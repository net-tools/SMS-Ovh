<?php


namespace Nettools\SMS\Ovh;


use \Nettools\Core\Misc\AbstractConfig;
use \Nettools\SMS\SMSException;




/**
 * Base class to send SMS through OVH Http or Email gateways
 */
abstract class OldGateway implements \Nettools\SMS\SMSGateway {

	/**
	 * Get batch size for bulkSend
	 *
	 * @return int
	 */
	abstract function getBatchSize();
	
	
	
	/**
	 * Send SMS to a lot of recipients (this is more optimized that calling `send` with a big array of recipients)
	 *
	 * @param string $msg 
	 * @param string $sender
	 * @param string[] $to Big array of recipients (numbers in +xxyyyyyyyyy format for EmailGateway or 00xxyyyyyyyyyy for HttpGateway)
	 * @param bool $transactional True if message sent is transactional ; otherwise it's promotional)
	 * @return int Returns the number of SMS sent (a multi-sms message count as as many message)
	 */
	function bulkSend($msg, $sender, array $to, $transactional = true)
	{
		// send by batch
		if ( count($to) <= $this->getBatchSize() )
			return $this->send($msg, $sender, $to, $transactional);
		else
			return $this->bulkSend($msg, $sender, array_slice($to, 0, $this->getBatchSize()), $transactional) + $this->bulkSend($msg, $sender, array_slice($to, $this->getBatchSize()), $transactional);
	}
	
	
	
	/**
	 * Send SMS to a lot of recipients by downloading a CSV file
	 *
	 * @param string $msg 
	 * @param string $sender
	 * @param string $url Url of CSV file with recipients (numbers in +xxyyyyyyyyy format for EmailGateway or 00xxyyyyyyyyyy for HttpGateway)
	 * @param bool $transactional True if message sent is transactional ; otherwise it's promotional)
	 * @return int Returns the number of SMS sent (a multi-sms message count as as many message)
	 */
	function bulkSendFromHttp($msg, $sender, $url, $transactional = true)
	{
		throw new SMSException('bulkSendFromHttp not implemented');
	}
}

?>