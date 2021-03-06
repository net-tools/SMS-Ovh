<?php


namespace Nettools\SMS\Ovh;


use \Nettools\Core\Misc\AbstractConfig;
use \Nettools\SMS\SMSException;




/**
 * Class to send SMS through OVH Http request
 */
class HttpGateway extends OldGateway {

	protected $config;
	protected $http;
	
	
	/**
	 * @var string URL of OVH HTTP sms gateway
	 */
	const URL = 'https://www.ovh.com/cgi-bin/sms/http2sms.cgi';
	
	
	
	/**
	 * @var int Number of messages per batch for bulkSend
	 * - url max length : 2048, rounded to 2000
	 * - sms max length : 918, rounded to 900 (https://support.textmagic.com/faq/maximum-length-of-a-text-message/)
	 * - url length for mandatory parameters : 180
	 * - characters available for numbers : 2000-180-918 = 902, rounded to 900
	 * - one number : 13 to 15 digits, rounded to 15 
	 * - a comma separates each number, so 1 number = 16 digits max
	 * - max numbers / url : 900/16 = 56 numbers, rounded to 50
	 */
	const BATCH_SIZE = 50;
	
	
	
	
	/**
	 * Constructor
	 *
	 * @param \GuzzleHttp\Client $http Guzzle HTTP interface to send request through
	 * @param \Nettools\Misc\AbstractConfig $config Config object
	 *
	 * $config must have values for :
	 * - service : ovh account
	 * - login : ovh sms user
	 * - password : ovh sms password
	 */
	public function __construct(\GuzzleHttp\Client $http, AbstractConfig $config)
	{
		$this->http = $http;
		$this->config = $config;
	}
	
	
	
	/**
	 * Create instance and instantiate a GuzzleHttp client
	 *
	 * @param \Nettools\Misc\AbstractConfig $config Config object, see constructor doc
	 * @return \Nettools\SMS\Ovh\HttpGateway
	 */
	static function create(AbstractConfig $config)
	{
		return new HttpGateway(new \GuzzleHttp\Client(), $config);
	}
	
	
	
	/**
	 * Get batch size for bulkSend
	 *
	 * @return int
	 */
	function getBatchSize()
	{
		return self::BATCH_SIZE;
	}
	
	
	
	/**
	 * Send SMS to several recipients
	 *
	 * @param string $msg 
	 * @param string $sender
	 * @param string[] $to Array of recipients, numbers in international format 00xxyyyyyyyyyyyyy (ex. 0033612345678)
	 * @param bool $transactional True if message sent is transactional ; otherwise it's promotional)
	 * @return int Returns the number of messages sent, usually the number of values of $to parameter (a multi-sms message count as 1 message)
	 * @throws \Nettools\SMS\SMSException
	 */
	function send($msg, $sender, array $to, $transactional = true)
	{
		// prepare url
		$response = $this->http->request('GET', self::URL, 
						 	[ 
								'query' => [
									'account'		=> $this->config->service,
									'login'			=> $this->config->login,
									'password'		=> $this->config->password,
									'contentType'	=> 'text/json',
									'from'			=> $sender,
									'noStop'		=> $transactional ? '1':'0',
									'to'			=> implode(',', $to),
									'message'		=> $msg
								]
							]);
		
		if ( $response->getStatusCode() != 200 )
			throw new SMSException("HTTP error " . $response->getStatusCode() . ' ' . $response->getReasonPhrase() . " when sending SMS");
		
		
		// decoding Ovh response
		$body = (string)($response->getBody());
		if ( $json = json_decode($body) )
			if ( ($json->status >= 100) && ($json->status < 200) )
				return count($json->smsIds);
			else
				throw new SMSException("Error {$json->status} when sending SMS : " . $json->message);
		else
			return "Unknown error when sending SMS ; response is '$body'";
	}
}

?>