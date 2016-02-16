<?php namespace MicrosoftTranslator;

class GuardFile implements GuardInterface
{
	const FILE_PREFIX = 'microsoft-translator-at-';

	/**
	 * @var \MicrosoftTranslator\LoggerInterface
	 */
	private $logger;

	/**
	 * Array of configuration parameters managed by this class
	 *
	 * @var array
	 */
	private $config_keys = array(
		'guard_file_dir_path' ,
		'api_base_url' ,
		'api_client_id' ,
	);

	/**x
	 * @var string
	 */
	private $guard_file_dir_path;

	/**
	 * @var string
	 */
	private $api_base_url = Client::API_BASE_URL;

	/**
	 * @var string
	 */
	private $api_client_id;

	/**
	 * The file is saved here in runtime
	 *
	 * @var array
	 */
	private $runtime_file_content;

	/**
	 * This class stores Access Token in a file according to the client_id value
	 *
	 * So each application client will share the same Access Token to reach the API
	 * Use this Guard if you use the API in a single app like a batch for example
	 *
	 * @param array                                $config
	 * @param \MicrosoftTranslator\LoggerInterface $logger
	 */
	public function __construct( $config = array() , LoggerInterface $logger )
	{
		$this->logger = $logger;

		foreach ( $this->config_keys as $key )
		{
			if ( isset( $config[ $key ] ) )
			{
				$this->$key = $config[ $key ];
				$this->logger->debug( __CLASS__ , 'config' , sprintf( '%s = %s' , $key , $this->$key ) );
			}
		}

		if ( is_null( $this->api_client_id ) )
		{
			$this->logger->fatal( __CLASS__ , 'init' , 'api_client_id is mandatory' );
		}

		if ( is_null( $this->guard_file_dir_path ) )
		{
			$this->guard_file_dir_path = sys_get_temp_dir();
		}
	}

	/**
	 * Tell if there is a valid stored Access Token
	 * It must answer false if there is one but it is expired
	 *
	 * @param array $param
	 *
	 * @return bool
	 */
	public function hasAccessToken( array $param = array() )
	{
		if ( ! $this->hasStoredToken() )
		{
			$this->logger->debug( __CLASS__ , 'has' , sprintf( 'No access token found in %s' , $this->getFilePath() ) );

			return false;
		}

		if ( $this->isNotExpired() )
		{
			$this->logger->debug( __CLASS__ , 'has' , 'Valid access token found' );

			return true;
		}

		$this->logger->debug( __CLASS__ , 'has' , sprintf( 'No valid access token found in %s' , $this->getFilePath() ) );

		return false;
	}

	/**
	 * Store an access token for a amount of time
	 *
	 * @param string $access_token
	 * @param int    $seconds
	 * @param array  $param
	 *
	 * @return bool  Successfully store or not
	 */
	public function storeAccessTokenForSeconds( $access_token , $seconds , array $param = array() )
	{
		$time = time() + (int)$seconds - 1;

		if ( $this->save( $access_token , $time ) === true )
		{
			$this->logger->info( __CLASS__ , 'store' , sprintf( 'Access token stored for %ss' , $seconds ) );
		}

		// if cannot save, an exception will be raised
		return true;
	}

	/**
	 * Get a stored Access Token
	 * It must answer only if the Access Token is valid and not expired
	 *
	 * @param array $param
	 *
	 * @return string|null
	 */
	public function getAccessToken( array $param = array() )
	{
		if ( ! $this->hasStoredToken() )
		{
			return null;
		}

		if ( $this->isNotExpired() )
		{
			$this->logger->debug( __CLASS__ , 'get' , 'Access token retrieved from file' );

			return $this->loadAccessToken();
		}

		return null;
	}

	/**
	 * Delete an Access Token
	 *
	 * @param array $param
	 *
	 * @return bool
	 */
	public function deleteAccessToken( array $param = array() )
	{
		return unlink( $this->getFilePath() );
	}

	/**
	 * Remove all expired Access Tokens
	 *
	 * @param array $param
	 *
	 * @return int the count of deleted Access Tokens
	 */
	public function cleanAccessTokens( array $param = array() )
	{
		$count = 0;

		foreach ( $this->getAllStoredAccessTokenFiles() as $file )
		{
			$expires_at = $this->loadTimestamp( $file );

			if ( is_integer( $expires_at ) )
			{
				if ( $expires_at < time() )
				{
					unlink( $file );
					$count++;
				}
			}
		}

		return $count;
	}

	/**
	 * Try to delete all stored Access Token
	 *
	 * @param array $param
	 *
	 * @return int the count of deleted Access Tokens
	 */
	public function deleteAllAccessTokens( array $param = array() )
	{
		$count = 0;

		foreach ( $this->getAllStoredAccessTokenFiles() as $file )
		{
			unlink( $file );
			$count++;
		}

		return $count;
	}

	/**
	 * @param null $file_path if null, get the current file
	 *
	 * Made public for unit tests
	 *
	 * @return int
	 */
	public function loadTimestamp( $file_path = null )
	{
		if ( is_null( $file_path ) )
		{
			$d = $this->load();
		}
		else
		{
			$d = $this->loadFile( $file_path );
		}

		return @$d[ 'e' ];
	}

	/**
	 * @return array
	 */
	private function getAllStoredAccessTokenFiles()
	{
		return glob( $this->getDirectoryPath() . DIRECTORY_SEPARATOR . self::FILE_PREFIX . '*' );
	}

	/**
	 * @return string
	 */
	private function getDirectoryPath()
	{
		return realpath( $this->guard_file_dir_path );
	}

	/**
	 * @return string
	 */
	private function getFilePath()
	{
		return $this->getDirectoryPath() . DIRECTORY_SEPARATOR . self::FILE_PREFIX . sha1( $this->api_base_url . '/' . $this->api_client_id );
	}

	/**
	 * Load the saved file as array
	 *
	 * @return array|null
	 */
	private function load()
	{
		if ( ! isset( $this->runtime_file_content ) )
		{
			$this->runtime_file_content = json_decode( @file_get_contents( $this->getFilePath() ) , true );
		}

		return $this->runtime_file_content;
	}

	/**
	 * Load a saved file as array
	 *
	 * @param string $file_path the file path of the file to decode
	 *
	 * @return array|null
	 */
	private function loadFile( $file_path )
	{
		return json_decode( @file_get_contents( $file_path ) , true );
	}

	/**
	 * @return string
	 */
	private function loadAccessToken()
	{
		$d = $this->load();

		return @$d[ 'a' ];
	}

	/**
	 * @return bool
	 */
	private function hasStoredToken()
	{
		$at = $this->loadAccessToken();

		return ( ! empty( $at ) );
	}

	/**
	 * @return bool
	 */
	private function isNotExpired()
	{
		$expired_at = $this->loadTimestamp();

		if ( is_integer( $expired_at ) )
		{
			return ( time() < $expired_at );
		}

		return false;
	}

	/**
	 * Save AT and timestamp in file
	 *
	 * @param string $access_token
	 * @param int    $timestamp
	 *
	 * @return bool
	 */
	private function save( $access_token , $timestamp )
	{
		unset( $this->runtime_file_content );

		$bytes = @file_put_contents( $this->getFilePath() , json_encode( array( 'a' => $access_token , 'e' => $timestamp ) ) );

		if ( $bytes === false )
		{
			// fatal log will throw an exception
			$this->logger->fatal( __CLASS__ , 'store' , sprintf( 'Unable to store access token in %s' , $this->getFilePath() ) );
		}

		$this->logger->debug( __CLASS__ , 'store' , sprintf( 'Access token stored in %s' , $this->getFilePath() ) );

		return true;
	}

}
