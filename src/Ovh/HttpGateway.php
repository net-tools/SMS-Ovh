<?php


namespace Nettools\SMS\Ovh;


use \Nettools\Core\Misc\AbstractConfig;
use \Nettools\SMS\SMSException;




/**
 * Classe to send SMS through OVH Http request
 */
class HttpGateway implements \Nettools\SMS\SMSGateway {

	protected $config;
	
	
	/**
	 * @var string URL of OVH HTTP sms gateway
	 */
	const URL = 'https://www.ovh.com/cgi-bin/sms/http2sms.cgi';
	
	
	
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
	
	
	
	/**
	 * Send SMS to a lot of recipients (this is more optimized that calling `send` with a big array of recipients)
	 *
	 * @param string $msg 
	 * @param string $sender
	 * @param string[] $to Big array of recipients, numbers in international format +xxyyyyyyyyyyyyy (ex. +33612345678)
	 * @param bool $transactional True if message sent is transactional ; otherwise it's promotional)
	 * @return int Returns the number of SMS sent (a multi-sms message count as as many message)
	 */
	function bulkSend($msg, $sender, array $to, $transactional = true)
	{
		return $this->send($msg, $sender, $to, $transactional);
	}
	
	
	
	/**
	 * Send SMS to a lot of recipients by downloading a CSV file
	 *
	 * @param string $msg 
	 * @param string $sender
	 * @param string $url Url of CSV file with recipients, numbers in international format +xxyyyyyyyyyyyyy (ex. +33612345678), first row is column headers (1 column title 'Number')
	 * @param bool $transactional True if message sent is transactional ; otherwise it's promotional)
	 * @return int Returns the number of SMS sent (a multi-sms message count as as many message)
	 */
	function bulkSendFromHttp($msg, $sender, $url, $transactional = true)
	{
		throw new SMSException('bulkSendFromHttp not implemented');
	}
}

?>