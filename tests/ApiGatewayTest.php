<?php

namespace Nettools\SMS\Ovh\Tests;

use \org\bovigo\vfs\vfsStream;




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
	
	
	
    public function testGatewayBulkSend()
    {
		$vfs = vfsStream::setup('root');
		

		
		$config = new \Nettools\Core\Misc\ObjectConfig((object)[
				'service' 			=> 'my_service',
				'downloadCsvUrl'	=> '/my.url/path',
				'localCsvPath'		=> 'path',
				'documentRoot'		=> $vfs->url()
			]);
		
		$params = [
			'message'		=> 'my sms',
			'noStopClause'	=> true,
			'sender'		=> 'TESTSENDER',
			'receiversDocumentUrl'		=> '/my.url/path/' . \Nettools\SMS\Ovh\ApiGateway::CSV_FILE
		];
		
        $client = $this->createMock(\Ovh\Api::class);
		$client->method('post')->with($this->equalTo('/sms/my_service/jobs'), $this->equalTo($params))->willReturn(['totalCreditsRemoved'=>2]);
		
		
		$g = new \Nettools\SMS\Ovh\ApiGateway($client, $config);
		$r = $g->bulkSend('my sms', 'TESTSENDER', ['+33601020304', '+33605060708'], true);
		$this->assertEquals(2, $r);
		
		$this->assertEquals(true, file_exists($vfs->url() . '/path/' . \Nettools\SMS\Ovh\ApiGateway::CSV_FILE));
		$this->assertEquals("Number\n+33601020304\n+33605060708", file_get_contents($vfs->url() . '/path/' . \Nettools\SMS\Ovh\ApiGateway::CSV_FILE));
		
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
