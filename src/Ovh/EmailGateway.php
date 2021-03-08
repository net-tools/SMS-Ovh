<?php


namespace Nettools\SMS\Ovh;


use \Nettools\Core\Misc\AbstractConfig;
use \Nettools\SMS\SMSException;




/**
 * Classe to send SMS through OVH Http request
 */
class EmailGateway implements \Nettools\SMS\SMSGateway {

	protected $config;
	protected $mailer;
	
	
	/**
	 * @var string Ovh email gateway address
	 */
	const EMAIL = 'email2sms@ovh.net';
	
	
	/**
	 * Constructor
	 *
	 * @param \Nettools\Mailing\Mailer $mailer
	 * @param \Nettools\Misc\AbstractConfig $config Config object
	 *
	 * $config must have values for :
	 * - service : ovh account
	 * - login : ovh sms user
	 * - password : ovh sms password
	 * - emailSender : email address the email to Ovh gateway is sent from
	 */
	public function __construct(\Nettools\Mailing\Mailer $mailer, AbstractConfig $config)
	{
		$this->mailer = $mailer;
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
	 * @throws \Nettools\SMS\SMSException
	 */
	function send($msg, $sender, array $to, $transactional = true)
	{
		// prepare text/plain part
		$email = \Nettools\Mailing\Mailer::createText($msg);
		
		$subject = 'Account=' . $this->config->service . ':Login=' . $this->config->login . ':Password=' . $this->config->password;
		$subject .= ':From=' . $sender . ':NoStop=' . ($transactional ? '1':'0') . ':To=' . implode(',', $to);

		if ( $ret = $this->mailer->sendmail($email, $this->config->emailSender, self::EMAIL, $subject, true) )
			throw new SMSException('Error when sending SMS through OVH email gateway : ' . $ret);
		else
			return count($to);
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