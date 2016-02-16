<?php

/** @noinspection PhpIncludeInspection */
include_once( 'src/MicrosoftTranslator.php' );

class AutoLoaderTests extends TestCase
{
	protected $configuration = array(
		'api_client_id'     => 'dumb' ,
		'api_client_secret' => 'dumber' ,
	);


	public function testCreate()
	{
		$client = new MicrosoftTranslator\Client( $this->configuration );
		$this->assertInstanceOf( 'MicrosoftTranslator\\Client' , $client );
	}
}
