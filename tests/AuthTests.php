<?php

class AuthTests extends TestCase
{
	protected $configuration = array(
		'api_lang'          => 'en' ,
		'api_client_id'     => 'dumb' ,
		'api_client_secret' => 'dumber' ,
	);

	public function testAuthWithDefaultGuard()
	{
		$client = new MicrosoftTranslator\Client( array(
			'api_client_id'     => 'dumb' ,
			'api_client_secret' => 'dumber' ,
		) );

		$auth = $client->getAuth();

		$this->assertInstanceOf( 'MicrosoftTranslator\\Auth' , $auth );
	}

	public function testAuthWithIncorrectGuard()
	{
		$this->setExpectedException( '\\MicrosoftTranslator\\Exception' , 'Guard Manager is not an instance of MicrosoftTranslator\\GuardInterface' );

		new MicrosoftTranslator\Client( array(
			'api_client_id'     => 'dumb' ,
			'api_client_secret' => 'dumber' ,
			'guard_type'        => '\\MicrosoftTranslator\\Logger' ,
		) );
	}

	public function testGetGuard()
	{
		$client = new MicrosoftTranslator\Client( array(
			'api_client_id'     => 'dumb' ,
			'api_client_secret' => 'dumber' ,
		) );

		$this->assertInstanceOf( '\\MicrosoftTranslator\\GuardInterface' , $client->getAuth()->getGuard() );
	}

	public function testGetAccessToken()
	{
		$access_token = 'LpL7uAhIz2TyYu8ZWvU62k9v3bbetCs8dxwcluRB';
		$result = '{
		  "access_token": "' . $access_token . '",
		  "token_type": "Bearer",
		  "expires_in": 3600
		}';

		/** @var \Mockery\MockInterface|\MicrosoftTranslator\Http $mockHttp */
		$mockHttp = \Mockery::mock( '\\MicrosoftTranslator\\Http' , array( $this->configuration , new \MicrosoftTranslator\Logger ) )->makePartial();
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$mockHttp->shouldReceive( 'execCurl' )->andReturn( array( $result , 200 , '' , 0 ) );

		$auth = new \MicrosoftTranslator\Auth( $this->configuration , new \MicrosoftTranslator\Logger , $mockHttp );

		// compute
		$auth->getGuard()->deleteAllAccessTokens();
		$this->assertEquals( $access_token , $auth->getAccessToken() );

		// get from cache
		$this->assertEquals( $access_token , $auth->getAccessToken() );
	}

	public function testGetAccessTokenWithHttpError()
	{
		/** @var \Mockery\MockInterface|\MicrosoftTranslator\Http $mockHttp */
		$mockHttp = \Mockery::mock( '\\MicrosoftTranslator\\Http' , array( $this->configuration , new \MicrosoftTranslator\Logger ) )->makePartial();
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$mockHttp->shouldReceive( 'execCurl' )->andReturn( array( '' , 500 , '' , 0 ) );

		$auth = new \MicrosoftTranslator\Auth( $this->configuration , new \MicrosoftTranslator\Logger , $mockHttp );
		$auth->getGuard()->deleteAllAccessTokens();

		$this->setExpectedException( '\\MicrosoftTranslator\\Exception' );
		$auth->getAccessToken();
	}

	public function testGetInvalidAccessToken()
	{
		$result = '{
		  "access_token": {},
		  "token_type": "Bearer",
		  "expires_in": 3600
		}';

		/** @var \Mockery\MockInterface|\MicrosoftTranslator\Http $mockHttp */
		$mockHttp = \Mockery::mock( '\\MicrosoftTranslator\\Http' , array( $this->configuration , new \MicrosoftTranslator\Logger ) )->makePartial();
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$mockHttp->shouldReceive( 'execCurl' )->andReturn( array( $result , 200 , '' , 0 ) );

		$auth = new \MicrosoftTranslator\Auth( $this->configuration , new \MicrosoftTranslator\Logger , $mockHttp );
		$auth->getGuard()->deleteAllAccessTokens();

		$this->setExpectedException( '\\MicrosoftTranslator\\Exception' , 'Access token found in response but it is not a string' );
		$auth->getAccessToken();
	}

	public function testGetUndefinedAccessToken()
	{
		$result = '{
		  "token_type": "Bearer",
		  "expires_in": 3600
		}';

		/** @var \Mockery\MockInterface|\MicrosoftTranslator\Http $mockHttp */
		$mockHttp = \Mockery::mock( '\\MicrosoftTranslator\\Http' , array( $this->configuration , new \MicrosoftTranslator\Logger ) )->makePartial();
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$mockHttp->shouldReceive( 'execCurl' )->andReturn( array( $result , 200 , '' , 0 ) );

		$auth = new \MicrosoftTranslator\Auth( $this->configuration , new \MicrosoftTranslator\Logger , $mockHttp );
		$auth->getGuard()->deleteAllAccessTokens();

		$this->setExpectedException( '\\MicrosoftTranslator\\Exception' , 'Access token not found in response' );
		$auth->getAccessToken();
	}

}
