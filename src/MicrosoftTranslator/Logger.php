<?php namespace MicrosoftTranslator;

class Logger implements LoggerInterface
{
	const LEVEL_DEBUG   = 8;
	const LEVEL_INFO    = 6;
	const LEVEL_WARNING = 4;
	const LEVEL_ERROR   = 2;
	const LEVEL_FATAL   = 0;

	/**
	 * Array of configuration parameters managed by this class
	 *
	 * @var array
	 */
	private $config_keys = array(
		'log_level' ,
		'log_file_path' ,
	);

	/**
	 * Default Log Level : nothing is logged
	 *
	 * @var int
	 */
	private $log_level = -1;

	/**
	 * File path of the file to send log
	 * Leave empty to use error_log function instead
	 *
	 * @var int
	 */
	private $log_file_path = null;

	/**
	 * @param array $config
	 */
	public function __construct( $config = array() )
	{
		// We need to manage this line manually to send the logs in the file if needed in the next loop
		if ( isset( $config[ 'log_file_path' ] ) )
		{
			$this->log_file_path = $config[ 'log_file_path' ];
		}

		foreach ( $this->config_keys as $key )
		{
			if ( isset( $config[ $key ] ) )
			{
				$this->$key = $config[ $key ];
				$this->debug( __CLASS__ , 'config' , sprintf( '%s = %s' , $key , $this->$key ) );
			}
		}
	}

	/**
	 * @param string $object
	 *
	 * @return string
	 */
	private static function getClassNameWithoutNameSpace( $object )
	{
		$names = explode( '\\' , $object );

		return end( $names );
	}

	/**
	 * @param string $object
	 * @param string $category
	 * @param string $severity
	 *
	 * @return string
	 */
	private static function getMessagePrefix( $object , $category , $severity )
	{
		return '[' . date( 'Y/m/d H:i:s' ) . '][' . $severity. '][' . self::getClassNameWithoutNameSpace( $object ) . '][' . $category . '] ';
	}


	/**
	 * Info message
	 *
	 * @param string $object
	 * @param string $category
	 * @param string $message
	 *
	 * @return bool
	 */
	public function info( $object , $category , $message )
	{
		return $this->message( self::getMessagePrefix( $object , $category , 'info' ) . $message , self::LEVEL_INFO );
	}

	/**
	 * Warning message
	 *
	 * @param string $object
	 * @param string $category
	 * @param string $message
	 *
	 * @return bool
	 */
	public function warning( $object , $category , $message )
	{
		return $this->message( self::getMessagePrefix( $object , $category , 'warn' ) . $message , self::LEVEL_WARNING );
	}

	/**
	 * Debug message
	 *
	 * @param string $object
	 * @param string $category
	 * @param string $message
	 *
	 * @return bool
	 */
	public function debug( $object , $category , $message )
	{
		return $this->message( self::getMessagePrefix( $object , $category , 'debug' ) . $message , self::LEVEL_DEBUG );
	}

	/**
	 * Error message
	 *
	 * @param string $object
	 * @param string $category
	 * @param string $message
	 *
	 * @return bool
	 */
	public function error( $object , $category , $message )
	{
		return $this->message( self::getMessagePrefix( $object , $category , 'error' ) . $message , self::LEVEL_ERROR );
	}

	/**
	 * fatal message and throw an MicrosoftTranslator\Exception
	 *
	 * @param string $object
	 * @param string $category
	 * @param string $message
	 *
	 * @throws \MicrosoftTranslator\Exception
	 */
	public function fatal( $object , $category , $message )
	{
		$this->message( self::getMessagePrefix( $object , $category , 'fatal' ) . $message , self::LEVEL_FATAL );

		throw new Exception( $message );
	}

	/**
	 * Log a message using error_log if log level is compliant
	 *
	 * @param string  $message
	 * @param integer $message_level
	 *
	 * @return bool true if message has been logged
	 * @throws \MicrosoftTranslator\Exception
	 */
	private function message( $message , $message_level )
	{
		if ( $message_level <= (int)$this->log_level )
		{
			if ( empty( $this->log_file_path ) )
			{
				error_log( $message );
			}
			else
			{
				if ( @file_put_contents( $this->log_file_path , $message . "\n" , FILE_APPEND ) === false )
				{
					$message = sprintf( "Unable to write to log file %s" , $this->log_file_path );

					error_log( self::getMessagePrefix( __CLASS__ , 'message' , 'fatal' ) . $message );

					throw new Exception( $message );
				}
			}

			return true;
		}

		return false;
	}
}
