<?php

namespace Nettools\SMS\Ovh\Tests;


class ApiGatewayTest extends \PHPUnit\Framework\TestCase
{
    public function testGatewaySend()
    {
		$config = new \Nettools\Core\Misc\ObjectConfig((object)['service' => 'my_service']);
		
		$params = [
			'message'		=> 'my sms',
			'noStopClause'	=> true,
			'sender'		=> 'TESTSENDER',
			'receivers'		=> ['+33601020304', '+33605060708']
		];
		
        $client = $this->createMock(\Ovh\Api::class);
		$client->method('post')->with($this->equalTo('/sms/my_service/jobs'), $this->equalTo($params))->willReturn(['ids' => [1000,2000]]);
		
		
		$g = new \Nettools\SMS\Ovh\ApiGateway($client, $config);
		$r = $g->send('my sms', 'TESTSENDER', ['+33601020304', '+33605060708'], true);
		$this->assertEquals(2, $r);
	}
	
	
	
    public function testGatewayBulkSendFromHttp()
    {
		$config = new \Nettools\Core\Misc\ObjectConfig((object)['service' => 'my_service']);
		
		$params = [
			'message'		=> 'my sms',
			'noStopClause'	=> true,
			'sender'		=> 'TESTSENDER',
			'receiversDocumentUrl'		=> 'my.url'
		];
		
        $client = $this->createMock(\Ovh\Api::class);
		$client->method('post')->with($this->equalTo('/sms/my_service/jobs'), $this->equalTo($params))->willReturn(['totalCreditsRemoved'=>1]);
		
		
		$g = new \Nettools\SMS\Ovh\ApiGateway($client, $config);
		$r = $g->bulkSendFromHttp('my sms', 'TESTSENDER', 'my.url', true);
		$this->assertEquals(1, $r);
	}
}




?>
