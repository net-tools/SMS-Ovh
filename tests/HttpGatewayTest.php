<?php

namespace Nettools\SMS\Ovh\Tests;



class HttpGatewayTest extends \PHPUnit\Framework\TestCase
{
    public function testGatewaySend()
    {
		$config = new \Nettools\Core\Misc\ObjectConfig((object)[
				'service' 		=> 'my_service',
				'login'			=> 'my_login',
				'password'		=> 'my_pwd'
			]);
		
		
		/*{"status":100,"smsIds":["290079169"],"creditLeft":"397.40"}*/
		
		$stub_guzzle_response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
		$stub_guzzle_response->method('getStatusCode')->willReturn(200);
		$stub_guzzle_response->method('getBody')->willReturn('{"status":100,"smsIds":["290079169","290079170"],"creditLeft":"397.40"}');
				
		// creating stub for guzzle client ; any of the request (GET, POST, PUT, DELETE) will return the guzzle response
		$stub_guzzle = $this->createMock(\GuzzleHttp\Client::class);
		
		// asserting that method Request is called with the right parameters, in particular, the options array being merged with default timeout options
		$stub_guzzle->expects($this->once())->method('request')->with(
						$this->equalTo('get'), 
						$this->equalTo(\Nettools\SMS\Ovh\HttpGateway::URL), 
						$this->equalTo(
								array(
									'query' => [
										'account'		=> 'my_service',
										'login'			=> 'my_login',
										'password'		=> 'my_pwd',
										'contentType'	=> 'text/json',
										'from'			=> 'TESTSENDER',
										'noStop'		=> '1',
										'to'			=> '+33601020304,+33605060708',
										'message'		=> 'my sms'
									]
								)
							)
					)
					->willReturn($stub_guzzle_response);
		
		$client = new \Nettools\SMS\Ovh\HttpGateway($stub_guzzle, $config);
		$r = $client->send('my sms', 'TESTSENDER', ['+33601020304', '+33605060708'], true);
		$this->assertEquals(2, $r);
	}

}


?>
