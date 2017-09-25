<?php

/** @noinspection PhpIncludeInspection */
include_once( 'src/MicrosoftTranslator.php' );

class AutoLoaderTests extends TestCase
{
	protected $configuration = array(
		'api_client_key'     => 'dumb' ,
	);


	public function testCreate()
	{
		$client = new MicrosoftTranslator\Client( $this->configuration );
		$this->assertInstanceOf( 'MicrosoftTranslator\\Client' , $client );
	}
}
