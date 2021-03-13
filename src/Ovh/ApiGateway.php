<?php


namespace Nettools\SMS\Ovh;


use \Nettools\Core\Misc\AbstractConfig;



/**
 * Classe to send SMS through OVH api
 */
class ApiGateway implements \Nettools\SMS\SMSGateway {

	protected $api;
	protected $config;
	
	
	const CSV_FILE = 'sms-csv.csv';
	
	
	
	/**
	 * Constructor
	 *
	 * @param \Ovh\Api $api API ovh
	 * @param \Nettools\Misc\AbstractConfig $config Config object
	 *
	 * $config must have values for :
	 * - service
	 * - downloadCsvUrl : url (path) to download csv file
	 * - localCsvPath : path (relative to documentRoot config value) to csv file to be created
	 * - documentRoot : server path to www document root
	 */
	public function __construct(\Ovh\Api $api, AbstractConfig $config)
	{
		$this->api = $api;			
		$this->config = $config;
	}
	
	
	
	/**
	 * Send SMS to several recipients
	 *
	 * @param string $msg 
	 * @param string $sender
	 * @param string[] $to Array of recipients, numbers in international format +xxyyyyyyyyyyyyy (ex. +33612345678)
	 * @param bool $transactional True if message sent is transactional ; otherwise it's promotional)
	 * @return int Returns the number of messages sent, usually the number of values of $to parameter (a multi-sms message count as 1 message)
	 */
	function send($msg, $sender, array $to, $transactional = true)
	{
		$service = $this->config->service;
		$ret = $this->api->post("/sms/$service/jobs", array(
				'message' 		=> $msg,
				'noStopClause'	=> $transactional,
				'sender'		=> $sender,
				'receivers'		=> $to
			));

		return count($ret['ids']);
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
		// creating csv file to be downloaded by sms gateway later
		$file = self::CSV_FILE;
		$f = fopen($this->config->documentRoot . '/' . $this->config->localCsvPath . '/' . $file, 'w');
		fwrite($f, "Number\n");
		fwrite($f, implode("\n", $to));
		fclose($f);	
		
		// calling method to send from http url
		return $this->bulkSendFromHttp($msg, $sender, $this->config->downloadCsvUrl . '/' . $file, $transactional);
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
		$service = $this->config->service;
		$ret = $this->api->post("/sms/$service/jobs", array(
				'message' 				=> $msg,
				'noStopClause'			=> $transactional,
				'sender'				=> $sender,
				'receiversDocumentUrl'	=> $url
			));

		return $ret['totalCreditsRemoved'];
	}
}


?>