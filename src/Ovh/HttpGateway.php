<?php


namespace Nettools\SMS\Ovh;


use \Nettools\Core\Misc\AbstractConfig;
use \Nettools\SMS\SMSException;




/**
 * Class to send SMS through OVH Http request
 */
class HttpGateway extends OldGateway {

	protected $config;
	
	
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
	 * @param \Nettools\Misc\AbstractConfig $config Config object
	 *
	 * $config must have values for :
	 * - service : ovh account
	 * - login : ovh sms user
	 * - password : ovh sms password
	 */
	public function __construct(AbstractConfig $config)
	{
		$this->config = $config;
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
		$url = self::URL . '?&account=' . $this->config->service . '&login=' . $this->config->login . '&password=' . $this->config->password . '&contentType=text/json';
		$rq = $url . '&from=' . urlencode($sender) . '&noStop=' . ($transactional ? '1':'0') . '&to=' . implode(',', $to) . '&message=' . urlencode($msg);
		$ret = file_get_contents($rq);
		
		
		// decode http response
		if ( $json = json_decode($ret) )
			if ( ($json->status >= 100) && ($json->status < 200) )
				return count($json->smsIds);
			else
				throw new SMSException("Error {$json->status} when sending SMS : " . $json->message);
		else
			return "Unknown error when sending SMS : $ret";
	}
}

?>