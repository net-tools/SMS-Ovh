<?php


namespace Nettools\SMS\Ovh;


use \Nettools\Core\Misc\AbstractConfig;
use \Nettools\SMS\SMSException;




/**
 * Classe to send SMS through OVH email request
 */
class EmailGateway extends OldGateway {

	protected $config;
	protected $mailer;
	
	
	/**
	 * @var string Ovh email gateway address
	 */
	const EMAIL = 'email2sms@ovh.net';
	
	
	/**
	 * @var int Number of messages per batch for bulkSend
	 */
	const BATCH_SIZE = 10;
	
	
	
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

		if ( $ret = $this->mailer->sendmail($email, $this->config->emailSender, self::EMAIL, $subject) )
			throw new SMSException('Error when sending SMS through OVH email gateway : ' . $ret);
		else
			return count($to);
	}
}	

?>