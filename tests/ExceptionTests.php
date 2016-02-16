<?php

class ExceptionTests extends TestCase
{

	public function testTextException()
	{
		$exception = new \MicrosoftTranslator\Exception( 'message' , 27 );

		$this->assertEquals( '' , $exception->getError() );
		$this->assertEquals( '' , $exception->getErrorDescription() );
		$this->assertEquals( 'message' , $exception->getMessage() );
		$this->assertEquals( 27 , $exception->getCode() );
	}

	public function testArray1Exception()
	{
		$message = array(
			'error'             => 'error_message' ,
			'error_description' => 'error_description' ,
		);

		$exception = new \MicrosoftTranslator\Exception( array(
			'http_code' => 1311 ,
			'http_body' => $message ,
		) , 27 );

		$this->assertEquals( 'error_message' , $exception->getError() );
		$this->assertEquals( 'error_description' , $exception->getErrorDescription() );
		$this->assertEquals( json_encode( $message ) , $exception->getMessage() );
		$this->assertEquals( 1311 , $exception->getCode() );
	}

	public function testMessageNoArrayNoStringException()
	{
		$exception = new \MicrosoftTranslator\Exception( 1311 , 27 );

		$this->assertEquals( 27 , $exception->getCode() );
		$this->assertEquals( '1311' , $exception->getMessage() );
	}


	public function testMessageInvalidArrayException()
	{
		$exception = new \MicrosoftTranslator\Exception( array( "a" => "b" ) , 27 );

		$this->assertEquals( 27 , $exception->getCode() );
		$this->assertEquals( '{"a":"b"}' , $exception->getMessage() );
	}


	public function testArrayErrorNumException()
	{
		$exception = new \MicrosoftTranslator\Exception( array(
			'error_num' => 1311 ,
			'error_msg' => 'message' ,
		) , 27 );

		$this->assertNull( $exception->getError() );
		$this->assertNull( $exception->getErrorDescription() );
		$this->assertEquals( 'message' , $exception->getMessage() );
		$this->assertEquals( 1311 , $exception->getCode() );
	}

}
