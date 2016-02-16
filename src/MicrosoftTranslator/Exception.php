<?php namespace MicrosoftTranslator;

class Exception extends \Exception
{
	/**
	 * MicrosoftTranslator Exception can handle message arrays
	 *
	 * @param mixed           $message
	 * @param int             $code
	 * @param \Exception|null $previous
	 */
	public function __construct( $message , $code = 0 , \Exception $previous = null )
	{
		if ( is_array( $message ) )
		{
			$this->accumulator_message = $message;

			if ( isset( $message[ 'http_code' ] ) )
			{
				$code    = $message[ 'http_code' ];
				$message = json_encode( @$message[ 'http_body' ] );
			}
			else if ( isset( $message[ 'error_num' ] ) )
			{
				$code    = $message[ 'error_num' ];
				$message = @$message[ 'error_msg' ];
			}
			else
			{
				$message = json_encode( $message );
			}
		}
		else if ( ! is_string( $message ) )
		{
			$message = strval( $message );
		}

		parent::__construct( $message , $code , $previous );
	}

	/**
	 * Return an API error number
	 * (valid API response but with HTTP code 400, etc...)
	 *
	 * @return string
	 */
	public function getError()
	{
		return @$this->accumulator_message[ 'http_body' ][ 'error' ];
	}

	/**
	 * Return an API error description according to the api_lang configuration parameter
	 * (valid API response but with HTTP code 400, etc...)
	 *
	 * @return string
	 */
	public function getErrorDescription()
	{
		return @$this->accumulator_message[ 'http_body' ][ 'error_description' ];
	}

}
