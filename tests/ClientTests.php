<?php

class ClientTests extends TestCase
{
	protected $configuration = array(
		'api_base_url'      => 'whatever we are unit testing' ,
		'api_client_key'     => 'dumb' ,
	);

	public function testCreate()
	{
		$client = new MicrosoftTranslator\Client( $this->configuration );
		$this->assertInstanceOf( 'MicrosoftTranslator\\Client' , $client );
	}

	public function testCreateWithIncorrectHttpManager()
	{
		$this->expectException( '\\MicrosoftTranslator\\Exception' , 'HTTP Manager is not an instance of MicrosoftTranslator\HttpInterface' );
		new MicrosoftTranslator\Client( $this->configuration , new stdClass() );
	}

	public function testCreateWithIncorrectAuthManager()
	{
		$this->expectException( '\\MicrosoftTranslator\\Exception' , 'Auth Manager is not an instance of MicrosoftTranslator\AuthInterface' );
		new MicrosoftTranslator\Client( $this->configuration , null , new stdClass() );
	}

	public function testCreateWithIncorrectLogger()
	{
		$this->expectException( '\\MicrosoftTranslator\\Exception' , 'Logger Manager is not an instance of MicrosoftTranslator\LoggerInterface' );
		new MicrosoftTranslator\Client( $this->configuration , null , null , new stdClass() );
	}

	public function testGetAuth()
	{
		$client = new MicrosoftTranslator\Client( $this->configuration );
		$this->assertInstanceOf( 'MicrosoftTranslator\\Auth' , $client->getAuth() );
	}

	public function testTranslate()
	{
		$result   = '<string xmlns="http://schemas.microsoft.com/2003/10/Serialization/">Fleisch</string>';
		$response = $this->getClient( $result )->translate( 'dog' , 'fr' );

		$this->assertInstanceOf( '\\MicrosoftTranslator\\Response' , $response );
		$this->assertInternalType( 'string' , $response->getBody() );
	}

	public function testTranslateFail()
	{
		$this->expectException( '\\MicrosoftTranslator\\Exception' );
		$result   = '<string xmlns="http://schemas.microsoft.com/2003/10/Serialization/">Fleisch</string>';
		$this->getClient( $result , 400 )->translate( 'dog' , 'fr' );
	}

	public function testTranslateWithFrom()
	{
		$result   = '<string xmlns="http://schemas.microsoft.com/2003/10/Serialization/">Fleisch</string>';
		$response = $this->getClient( $result )->translate( 'dog' , 'fr' , 'en' );

		$this->assertInstanceOf( '\\MicrosoftTranslator\\Response' , $response );
		$this->assertInternalType( 'string' , $response->getBody() );
	}

	public function testTranslateArray()
	{
		/** @noinspection XmlUnusedNamespaceDeclaration */
		$result   = '<ArrayOfTranslateArrayResponse xmlns="http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2" xmlns:i="http://www.w3.org/2001/XMLSchema-instance"><TranslateArrayResponse><From>en</From><OriginalTextSentenceLengths xmlns:a="http://schemas.microsoft.com/2003/10/Serialization/Arrays"><a:int>3</a:int></OriginalTextSentenceLengths><State/><TranslatedText>chien</TranslatedText><TranslatedTextSentenceLengths xmlns:a="http://schemas.microsoft.com/2003/10/Serialization/Arrays"><a:int>5</a:int></TranslatedTextSentenceLengths></TranslateArrayResponse><TranslateArrayResponse><From>en</From><OriginalTextSentenceLengths xmlns:a="http://schemas.microsoft.com/2003/10/Serialization/Arrays"><a:int>3</a:int></OriginalTextSentenceLengths><State/><TranslatedText>chat</TranslatedText><TranslatedTextSentenceLengths xmlns:a="http://schemas.microsoft.com/2003/10/Serialization/Arrays"><a:int>4</a:int></TranslatedTextSentenceLengths></TranslateArrayResponse></ArrayOfTranslateArrayResponse>';
		$response = $this->getClient( $result )->translateArray( array( 'dog' , 'cat' ) , 'fr' );

		$this->assertInstanceOf( '\\MicrosoftTranslator\\Response' , $response );
		$this->assertInternalType( 'array' , $response->getBody() );
	}

	public function testGetLanguageNames()
	{
		/** @noinspection XmlUnusedNamespaceDeclaration */
		$result   = '<ArrayOfstring xmlns="http://schemas.microsoft.com/2003/10/Serialization/Arrays" xmlns:i="http://www.w3.org/2001/XMLSchema-instance"><string>Anglais</string><string/><string>Français</string></ArrayOfstring>';
		$response = $this->getClient( $result )->getLanguageNames( 'fr' , array( 'en' , 'jp' , 'fr' ) );

		$this->assertInstanceOf( '\\MicrosoftTranslator\\Response' , $response );
		$this->assertInternalType( 'array' , $response->getBody() );
	}

	public function testGetLanguageNamesEmpty()
	{
		$this->expectException( '\\MicrosoftTranslator\\Exception' , '$languageCodes array is empty.' );
		/** @noinspection XmlUnusedNamespaceDeclaration */
		$result = '<ArrayOfstring xmlns="http://schemas.microsoft.com/2003/10/Serialization/Arrays" xmlns:i="http://www.w3.org/2001/XMLSchema-instance"><string>Anglais</string><string/><string>Français</string></ArrayOfstring>';
		$this->getClient( $result )->getLanguageNames( 'fr' , array() );
	}

	public function testGetLanguageNamesWithAString()
	{
		/** @noinspection XmlUnusedNamespaceDeclaration */
		$result   = '<ArrayOfstring xmlns="http://schemas.microsoft.com/2003/10/Serialization/Arrays" xmlns:i="http://www.w3.org/2001/XMLSchema-instance"><string>Anglais</string><string/><string>Français</string></ArrayOfstring>';
		$response = $this->getClient( $result )->getLanguageNames( 'fr' , 'en' );

		$this->assertInstanceOf( '\\MicrosoftTranslator\\Response' , $response );
		$this->assertInternalType( 'array' , $response->getBody() );
	}

	public function testGetLanguagesForTranslate()
	{
		/** @noinspection XmlUnusedNamespaceDeclaration */
		$result   = '<ArrayOfstring xmlns="http://schemas.microsoft.com/2003/10/Serialization/Arrays" xmlns:i="http://www.w3.org/2001/XMLSchema-instance"><string>ar</string><string>bs-Latn</string><string>bg</string><string>ca</string><string>zh-CHS</string><string>zh-CHT</string><string>hr</string><string>cs</string><string>da</string><string>nl</string><string>en</string><string>et</string><string>fi</string><string>fr</string><string>de</string><string>el</string><string>ht</string><string>he</string><string>hi</string><string>mww</string><string>hu</string><string>id</string><string>it</string><string>ja</string><string>sw</string><string>tlh</string><string>tlh-Qaak</string><string>ko</string><string>lv</string><string>lt</string><string>ms</string><string>mt</string><string>yua</string><string>no</string><string>otq</string><string>fa</string><string>pl</string><string>pt</string><string>ro</string><string>ru</string><string>sr-Cyrl</string><string>sr-Latn</string><string>sk</string><string>sl</string><string>es</string><string>sv</string><string>th</string><string>tr</string><string>uk</string><string>ur</string><string>vi</string><string>cy</string></ArrayOfstring>';
		$response = $this->getClient( $result )->getLanguagesForTranslate();

		$this->assertInstanceOf( '\\MicrosoftTranslator\\Response' , $response );
		$this->assertInternalType( 'array' , $response->getBody() );
	}

	public function testBreakSentences()
	{
		/** @noinspection XmlUnusedNamespaceDeclaration */
		$result   = '<ArrayOfint xmlns="http://schemas.microsoft.com/2003/10/Serialization/Arrays" xmlns:i="http://www.w3.org/2001/XMLSchema-instance"><int>16</int><int>17</int><int>29</int></ArrayOfint>';
		$response = $this->getClient( $result )->breakSentences( 'The dog is red. The cat is blue. The fish is yellow submarine.' , 'en' );

		$this->assertInstanceOf( '\\MicrosoftTranslator\\Response' , $response );
		$this->assertInternalType( 'array' , $response->getBody() );
	}

	public function testDetectArray()
	{
		/** @noinspection XmlUnusedNamespaceDeclaration */
		$result   = '<ArrayOfstring xmlns="http://schemas.microsoft.com/2003/10/Serialization/Arrays" xmlns:i="http://www.w3.org/2001/XMLSchema-instance"><string>en</string><string>fr</string></ArrayOfstring>';
		$response = $this->getClient( $result )->detectArray( array( 'The dog is red' , 'Le chien est rouge' ) );

		$this->assertInstanceOf( '\\MicrosoftTranslator\\Response' , $response );
		$this->assertInternalType( 'array' , $response->getBody() );
	}

	public function testDetectArrayEmpty()
	{
		$this->expectException( '\\MicrosoftTranslator\\Exception' , '$texts array is empty.' );
		/** @noinspection XmlUnusedNamespaceDeclaration */
		$result = '<ArrayOfstring xmlns="http://schemas.microsoft.com/2003/10/Serialization/Arrays" xmlns:i="http://www.w3.org/2001/XMLSchema-instance"><string>en</string><string>fr</string></ArrayOfstring>';
		$this->getClient( $result )->detectArray( array() );
	}

	public function testTransformText()
	{
		$result   = '{"ec":0,"em":"OK","sentence":"WHAT THE HECK I am not here!"}';
		$response = $this->getClient( $result )->TransformText( 'WTF I am not here!' , 'en' );

		$this->assertInstanceOf( '\\MicrosoftTranslator\\Response' , $response );
		$this->assertInternalType( 'array' , $response->getBody() );
	}

	public function testDetect()
	{
		$result   = '<string xmlns="http://schemas.microsoft.com/2003/10/Serialization/">en</string>';
		$response = $this->getClient( $result )->detect( array( 'The dog is red' ) );

		$this->assertInstanceOf( '\\MicrosoftTranslator\\Response' , $response );
		$this->assertInternalType( 'string' , $response->getBody() );
	}

	private function getClient( $result , $code = 200 )
	{
		/** @var \Mockery\MockInterface|\MicrosoftTranslator\Http $mockHttp */
		$mockHttp = \Mockery::mock( '\\MicrosoftTranslator\\Http' , array( $this->configuration , new \MicrosoftTranslator\Logger ) )->makePartial();
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$mockHttp->shouldReceive( 'execCurl' )->andReturn( array( $result , $code , '' , 0 ) );

		/** @var \Mockery\MockInterface|\MicrosoftTranslator\Auth $mockAuth */
		$mockAuth = \Mockery::mock( '\\MicrosoftTranslator\\Auth' , array( $this->configuration , new \MicrosoftTranslator\Logger , $mockHttp ) )->makePartial();
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$mockAuth->shouldReceive( 'getAccessToken' )->andReturn( 'foo_access_token' );

		return new MicrosoftTranslator\Client( $this->configuration , $mockHttp , $mockAuth );
	}
}
