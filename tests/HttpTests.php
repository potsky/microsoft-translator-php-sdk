<?php

class HttpTests extends TestCase
{

	protected $configuration = array(
		'http_timeout'    => 15 ,
		'http_proxy_host' => 'foo' ,
		'http_proxy_type' => 'foo' ,
		'http_proxy_auth' => 'foo' ,
		'http_proxy_port' => 'foo' ,
		'http_proxy_user' => 'foo' ,
		'http_proxy_pass' => 'foo' ,
	);

	public function testCreate()
	{
		$http = new MicrosoftTranslator\Http( $this->configuration , new MicrosoftTranslator\Logger );

		$this->assertInstanceOf( 'MicrosoftTranslator\\Http' , $http );

	}

	public function testIsOk()
	{
		$http = new MicrosoftTranslator\Http( $this->configuration , new MicrosoftTranslator\Logger );

		$this->assertTrue( $http->isRequestOk( array( 'http_code' => 200 ) ) );
		$this->assertTrue( $http->isRequestOk( array( 'http_code' => 201 ) ) );
		$this->assertFalse( $http->isRequestOk( array( 'http_code' => 202 ) ) );
		$this->assertFalse( $http->isRequestOk( array( 'http_code' => 300 ) ) );
		$this->assertFalse( $http->isRequestOk( array( 'http_code' => 400 ) ) );
		$this->assertFalse( $http->isRequestOk( array( 'http_code' => 500 ) ) );
	}

	public function testGet()
	{
		$result_body = 'get';
		$result_code = 200;

		/** @var \Mockery\MockInterface|\MicrosoftTranslator\Http $mockHttp */
		$mockHttp = \Mockery::mock( '\\MicrosoftTranslator\\Http' , array( $this->configuration , new MicrosoftTranslator\Logger ) )->makePartial();

		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$mockHttp->shouldReceive( 'execCurl' )->andReturn( array( $result_body , $result_code , '' , 0 ) );

		$response = $mockHttp->get( 'url' , 'access_token' , array( 'foo' => 'bar' ) );

		$this->assertEquals( $result_body , $response[ 'http_body' ] );
		$this->assertEquals( $result_code , $response[ 'http_code' ] );
	}

	public function testHttp300Get()
	{
		$result_body = 'get';
		$result_code = 300;

		/** @var \Mockery\MockInterface|\MicrosoftTranslator\Http $mockHttp */
		$mockHttp = \Mockery::mock( '\\MicrosoftTranslator\\Http' , array( $this->configuration , new MicrosoftTranslator\Logger ) )->makePartial();

		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$mockHttp->shouldReceive( 'execCurl' )->andReturn( array( $result_body , $result_code , '' , 0 ) );

		$response = $mockHttp->get( 'url' , 'access_token' , array( 'foo' => 'bar' ) );

		$this->assertEquals( $result_body , $response[ 'http_body' ] );
		$this->assertEquals( $result_code , $response[ 'http_code' ] );
	}

	public function testHttp400Get()
	{
		$result_body = 'get';
		$result_code = 400;

		/** @var \Mockery\MockInterface|\MicrosoftTranslator\Http $mockHttp */
		$mockHttp = \Mockery::mock( '\\MicrosoftTranslator\\Http' , array( $this->configuration , new MicrosoftTranslator\Logger ) )->makePartial();

		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$mockHttp->shouldReceive( 'execCurl' )->andReturn( array( $result_body , $result_code , '' , 0 ) );

		$response = $mockHttp->get( 'url' , 'access_token' , array( 'foo' => 'bar' ) );

		$this->assertEquals( $result_body , $response[ 'http_body' ] );
		$this->assertEquals( $result_code , $response[ 'http_code' ] );
	}

	public function testHttpFailGet()
	{
		$curl_errmsg = 'foobar';
		$curl_errnum = 27;

		/** @var \Mockery\MockInterface|\MicrosoftTranslator\Http $mockHttp */
		$mockHttp = \Mockery::mock( '\\MicrosoftTranslator\\Http' , array( $this->configuration , new MicrosoftTranslator\Logger ) )->makePartial();

		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$mockHttp->shouldReceive( 'execCurl' )->andReturn( array( 'foo' , null , $curl_errmsg , $curl_errnum ) );

		$response = $mockHttp->get( 'url' , 'access_token' , array( 'foo' => 'bar' ) );

		$this->assertEquals( $curl_errmsg , $response[ 'error_msg' ] );
		$this->assertEquals( $curl_errnum , $response[ 'error_num' ] );
	}

	public function testPost()
	{
		$result_body = 'post';
		$result_code = 200;

		/** @var \Mockery\MockInterface|\MicrosoftTranslator\Http $mockHttp */
		$mockHttp = \Mockery::mock( '\\MicrosoftTranslator\\Http' , array( $this->configuration , new MicrosoftTranslator\Logger ) )->makePartial();

		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$mockHttp->shouldReceive( 'execCurl' )->andReturn( array( $result_body , $result_code , '' , 0 ) );

		$response = $mockHttp->post( 'url' , 'access_token' , array( 'foo' => 'bar' ) );

		$this->assertEquals( $result_body , $response[ 'http_body' ] );
		$this->assertEquals( $result_code , $response[ 'http_code' ] );
	}

	public function testPostWithStringPost()
	{
		$result_body = 'post';
		$result_code = 200;

		/** @var \Mockery\MockInterface|\MicrosoftTranslator\Http $mockHttp */
		$mockHttp = \Mockery::mock( '\\MicrosoftTranslator\\Http' , array( $this->configuration , new MicrosoftTranslator\Logger ) )->makePartial();

		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$mockHttp->shouldReceive( 'execCurl' )->andReturn( array( $result_body , $result_code , '' , 0 ) );

		$response = $mockHttp->post( 'url' , 'access_token' , 'this is a string' );

		$this->assertEquals( $result_body , $response[ 'http_body' ] );
		$this->assertEquals( $result_code , $response[ 'http_code' ] );
	}

	public function testPut()
	{
		$result_body = 'put';
		$result_code = 200;

		/** @var \Mockery\MockInterface|\MicrosoftTranslator\Http $mockHttp */
		$mockHttp = \Mockery::mock( '\\MicrosoftTranslator\\Http' , array( $this->configuration , new MicrosoftTranslator\Logger ) )->makePartial();

		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$mockHttp->shouldReceive( 'execCurl' )->andReturn( array( $result_body , $result_code , '' , 0 ) );

		$response = $mockHttp->put( 'url' );

		$this->assertEquals( $result_body , $response[ 'http_body' ] );
		$this->assertEquals( $result_code , $response[ 'http_code' ] );
	}

	public function testDelete()
	{
		$result_body = 'delete';
		$result_code = 200;

		/** @var \Mockery\MockInterface|\MicrosoftTranslator\Http $mockHttp */
		$mockHttp = \Mockery::mock( '\\MicrosoftTranslator\\Http' , array( $this->configuration , new MicrosoftTranslator\Logger ) )->makePartial();

		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$mockHttp->shouldReceive( 'execCurl' )->andReturn( array( $result_body , $result_code , '' , 0 ) );

		$response = $mockHttp->delete( 'url' );

		$this->assertEquals( $result_body , $response[ 'http_body' ] );
		$this->assertEquals( $result_code , $response[ 'http_code' ] );
	}

	public function testExecCurl()
	{
		$http     = new MicrosoftTranslator\Http( $this->configuration , new MicrosoftTranslator\Logger );
		$response = $http->execCurl( array( CURLOPT_URL => 'http://www.microsoft.com' ) );

		$this->assertInternalType( 'array' , $response );
		$this->assertContains( '</html>' , @$response[ 0 ] );
		$this->assertEquals( 200 , @$response[ 1 ] );
		$this->assertEquals( '' , @$response[ 2 ] );
		$this->assertEquals( 0 , @$response[ 3 ] );
	}
}
