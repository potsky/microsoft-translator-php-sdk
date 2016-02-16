<?php

class ResponseTests extends TestCase
{
	public function testResponse()
	{
		$data = 'this is a string';

		$response = array(
			'http_code' => 200 ,
			'http_body' => $data ,
		);

		$object = new MicrosoftTranslator\Response( '' , array() , array() , true , '' , $response );

		$this->assertEquals( 'raw' , $object->getType() );
		$this->assertEquals( 200 , $object->getCode() );
		$this->assertEquals( $data , $object->getBody() );
		$this->assertEquals( $response , $object->getResponse() );
		$this->assertEquals( json_encode( $response ) , strval( $object ) );
	}
}
