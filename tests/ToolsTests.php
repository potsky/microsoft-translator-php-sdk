<?php

use MicrosoftTranslator\Tools;

class ToolsTests extends TestCase
{
	public function testHttpBuildUrl()
	{
		$this->assertEquals( 'http://accumulator.psk.io' , Tools::httpBuildUrl( 'http://accumulator.psk.io' , array( 'port' => 443 ) , Tools::HTTP_URL_STRIP_ALL ) );
		$this->assertEquals( 'http://accumulator.psk.io' , Tools::httpBuildUrl( 'http://accumulator.psk.io' , array( 'user' => 'potsky' ) , Tools::HTTP_URL_STRIP_AUTH ) );
		$this->assertEquals( 'http://potsky@accumulator.psk.io' , Tools::httpBuildUrl( 'http://accumulator.psk.io' , array( 'user' => 'potsky' ) , Tools::HTTP_URL_REPLACE ) );
		$this->assertEquals( 'http://accumulator.psk.io/b' , Tools::httpBuildUrl( 'http://accumulator.psk.io/a' , array( 'path' => '/b' ) , Tools::HTTP_URL_JOIN_PATH ) );
		$this->assertEquals( 'http://accumulator.psk.io/b' , Tools::httpBuildUrl( 'http://accumulator.psk.io' , array( 'path' => '/b' ) , Tools::HTTP_URL_JOIN_PATH ) );
		$this->assertEquals( 'https://accumulator.psk.io' , Tools::httpBuildUrl( 'http://accumulator.psk.io' , array( 'scheme' => 'https' ) ) );
		$this->assertEquals( 'http://psk.io' , Tools::httpBuildUrl( 'http://accumulator.psk.io' , array( 'host' => 'psk.io' ) ) );
		$this->assertEquals( 'http://psk.io?a=1&b=2' , Tools::httpBuildUrl( 'http://psk.io?a=1' , array( 'query' => 'b=2' ) , Tools::HTTP_URL_JOIN_QUERY ) );
		$this->assertEquals( 'http://psk.io?b=2' , Tools::httpBuildUrl( 'http://psk.io?a=1' , array( 'query' => 'b=2' ) ) );
	}

	public function testGetArrayDotValue()
	{
		$haystack = array(
			'foo'  => 'bar' ,
			'dumb' => array(
				'dumber' => 'zoo',
			),
		);

		$this->assertNull( Tools::getArrayDotValue( $haystack , 'dumber' ) );
		$this->assertEquals( 'bar' , Tools::getArrayDotValue( $haystack , 'foo' ) );
		$this->assertEquals( 'zoo' , Tools::getArrayDotValue( $haystack , 'dumb.dumber' ) );
	}

	public function testIsAssociativeArray()
	{
		$this->assertTrue( Tools::isAssociativeArray( array( 'a' => 'b' ) ) );
		$this->assertTrue( Tools::isAssociativeArray( array( 0 => 'a' , 2 => 'b' ) ) );
		$this->assertFalse( Tools::isAssociativeArray( array( 'a' , 'b' ) ) );
	}

	public function testStartsWith()
	{
		$this->assertTrue( Tools::startsWith( 'This is life' , 'This' ) );
		$this->assertFalse( Tools::startsWith( 'This is life' , 'this' ) );
		$this->assertTrue( Tools::startsWith( 'This is life' , '' ) );
		$this->assertFalse( Tools::startsWith( '' , 'this' ) );
		$this->assertTrue( Tools::startsWith( '' , '' ) );
	}

	public function testEndsWith()
	{
		$this->assertTrue( Tools::endsWith( 'This is life' , 'life' ) );
		$this->assertFalse( Tools::endsWith( 'This is life' , 'Life' ) );
		$this->assertTrue( Tools::endsWith( 'This is life' , '' ) );
		$this->assertFalse( Tools::endsWith( '' , 'this' ) );
		$this->assertTrue( Tools::endsWith( '' , '' ) );
	}

}
