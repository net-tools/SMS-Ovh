<?php

namespace Nettools\SMS\Ovh\Tests;



class EmailGatewayTest extends \PHPUnit\Framework\TestCase
{
    public function testGatewaySend()
    {
		$config = new \Nettools\Core\Misc\ObjectConfig((object)[
				'service' 		=> 'my_service',
				'login'			=> 'my_login',
				'password'		=> 'my_pwd',
				'emailSender'	=> 'me@at.com'
			]);
		
		
		$mailer = new \Nettools\Mailing\Mailer(\Nettools\Mailing\MailSenders\Virtual_MailSender::class);
		
		$g = new \Nettools\SMS\Ovh\EmailGateway($mailer, $config);
		$r = $g->send('my sms', 'TESTSENDER', ['+33601020304', '+33605060708'], true);
		$this->assertEquals(2, $r);
		
		$m = $mailer->getMailSender()->getSent();
		$this->assertEquals(1, count($m));

		/*
		$subject = 'Account=' . $this->config->service . ':Login=' . $this->config->login . ':Password=' . $this->config->password;
		$subject .= ':From=' . $sender . ':NoStop=' . ($transactional ? '1':'0') . ':To=' . implode(',', $to);*/
		
		$subject = 'Subject: Account=my_service:Login=my_login:Password=my_pwd:From=TESTSENDER:NoStop=1:To=+33601020304,+33605060708';
		$this->assertEquals(false, strpos($m[0], $subject) === false);
		$this->assertEquals(false, strpos($m[0], 'my sms') === false);
	}
}




?>
