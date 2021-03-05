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
	 * @param string $nostop 
	 * @return int Returns the number of messages sent, usually the number of values of $to parameter (a multi-sms message count as 1 message)
	 * @throws \Nettools\SMS\SMSException
	 */
	function send($msg, $sender, array $to, $nostop = true)
	{
		// prepare text/plain part
		$email = \Nettools\Mailing\Mailer::createText($msg);
		
		$subject = 'Account=' . $this->config->service . ':Login=' . $this->config->login . ':Password=' . $this->config->password;
		$subject .= ':From=' . $sender . ':NoStop=' . ($nostop ? '1':'0') . ':To=' . implode(',', $to);

		if ( $ret = $this->mailer->sendmail($email, $this->config->emailSender, self::EMAIL, $subject, true) )
			throw new SMSException('Error when sending SMS through OVH email gateway : ' . $ret);
		else
			return count($to);
	}
}

?>