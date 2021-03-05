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
	 * @param string $nostop 
	 * @return int Returns the number of messages sent, usually the number of values of $to parameter (a multi-sms message count as 1 message)
	 * @throws \Nettools\SMS\SMSException
	 */
	function send($msg, $sender, array $to, $nostop = true)
	{
		$url = self::URL . '?&account=' . $this->config->service . '&login=' . $this->config->login . '&password=' . $this->config->password . '&contentType=text/json';
		$rq = $url . '&from=' . urlencode($sender) . '&noStop=' . ($nostop ? '1':'0') . '&to=' . implode(',', $to) . '&message=' . urlencode($msg);
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