<?php

class LoggerTests extends TestCase
{

	public function testNoLogErrorLog()
	{
		$configuration = array(
			'api_client_id'     => 'CLIENT_ID' ,
			'api_client_secret' => 'CLIENT_SECRET' ,
		);

		$logger = new \MicrosoftTranslator\Logger( $configuration );

		$this->assertFalse( $logger->debug( 'object' , 'category' , 'message' ) );
		$this->assertFalse( $logger->info( 'object' , 'category' , 'message' ) );
		$this->assertFalse( $logger->warning( 'object' , 'category' , 'message' ) );
		$this->assertFalse( $logger->error( 'object' , 'category' , 'message' ) );

		$this->setExpectedException( '\\MicrosoftTranslator\\Exception' );
		$logger->fatal( 'object' , 'category' , 'message' );
	}

	public function testDebugErrorLog()
	{
		$configuration = array(
			'api_client_id'     => 'CLIENT_ID' ,
			'api_client_secret' => 'CLIENT_SECRET' ,
			'api_client_scope'  => 'CLIENT_SCOPE' ,
			'log_level'         => MicrosoftTranslator\Logger::LEVEL_DEBUG ,
		);

		$logger = new \MicrosoftTranslator\Logger( $configuration );

		$this->assertTrue( $logger->debug( 'object' , 'category' , 'message' ) );
		$this->assertTrue( $logger->info( 'object' , 'category' , 'message' ) );
		$this->assertTrue( $logger->warning( 'object' , 'category' , 'message' ) );
		$this->assertTrue( $logger->error( 'object' , 'category' , 'message' ) );
	}

	public function testInfoErrorLog()
	{
		$configuration = array(
			'api_client_id'     => 'CLIENT_ID' ,
			'api_client_secret' => 'CLIENT_SECRET' ,
			'api_client_scope'  => 'CLIENT_SCOPE' ,
			'log_level'         => MicrosoftTranslator\Logger::LEVEL_INFO ,
		);

		$logger = new \MicrosoftTranslator\Logger( $configuration );

		$this->assertFalse( $logger->debug( 'object' , 'category' , 'message' ) );
		$this->assertTrue( $logger->info( 'object' , 'category' , 'message' ) );
		$this->assertTrue( $logger->warning( 'object' , 'category' , 'message' ) );
		$this->assertTrue( $logger->error( 'object' , 'category' , 'message' ) );
	}

	public function testWarningErrorLog()
	{
		$configuration = array(
			'api_client_id'     => 'CLIENT_ID' ,
			'api_client_secret' => 'CLIENT_SECRET' ,
			'api_client_scope'  => 'CLIENT_SCOPE' ,
			'log_level'         => MicrosoftTranslator\Logger::LEVEL_WARNING ,
		);

		$logger = new \MicrosoftTranslator\Logger( $configuration );

		$this->assertFalse( $logger->debug( 'object' , 'category' , 'message' ) );
		$this->assertFalse( $logger->info( 'object' , 'category' , 'message' ) );
		$this->assertTrue( $logger->warning( 'object' , 'category' , 'message' ) );
		$this->assertTrue( $logger->error( 'object' , 'category' , 'message' ) );
	}

	public function testErrorErrorLog()
	{
		$configuration = array(
			'api_client_id'     => 'CLIENT_ID' ,
			'api_client_secret' => 'CLIENT_SECRET' ,
			'api_client_scope'  => 'CLIENT_SCOPE' ,
			'log_level'         => MicrosoftTranslator\Logger::LEVEL_ERROR ,
		);

		$logger = new \MicrosoftTranslator\Logger( $configuration );

		$this->assertFalse( $logger->debug( 'object' , 'category' , 'message' ) );
		$this->assertFalse( $logger->info( 'object' , 'category' , 'message' ) );
		$this->assertFalse( $logger->warning( 'object' , 'category' , 'message' ) );
		$this->assertTrue( $logger->error( 'object' , 'category' , 'message' ) );
	}

	public function testDebugFileNoAccess()
	{
		$configuration = array(
			'api_client_id'     => 'CLIENT_ID' ,
			'api_client_secret' => 'CLIENT_SECRET' ,
			'api_client_scope'  => 'CLIENT_SCOPE' ,
			'log_level'         => MicrosoftTranslator\Logger::LEVEL_DEBUG ,
			'log_file_path'     => '/tmp/this/file/does/not/exists.log' ,
		);


		$this->setExpectedException( '\\MicrosoftTranslator\\Exception' , 'Unable to write to log file /tmp/this/file/does/not/exists.log' );

		new \MicrosoftTranslator\Logger( $configuration );

	}

}
