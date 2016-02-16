<?php namespace MicrosoftTranslator;

class Auth implements AuthInterface
{
	/**
	 * @var \MicrosoftTranslator\LoggerInterface
	 */
	private $logger;

	/**
	 * @var \MicrosoftTranslator\HttpInterface
	 */
	private $http;

	/**
	 * @var \MicrosoftTranslator\GuardInterface
	 *
	 * This holds the Guard Manager to store, delete and get Access Token
	 *
	 * You can use your own Guard Manager by injecting it in the constructor
	 * Your Guard Manager must implement the \MicrosoftTranslator\GuardInterface interface
	 */
	private $guard;

	/**
	 * Array of configuration parameters managed by this class
	 *
	 * @var array
	 */
	private $config_keys = array(
		'auth_base_url' ,
		'api_client_id' ,
		'api_client_secret' ,
		'api_client_scope' ,
		'guard_type' ,
	);

	/**
	 * @var string
	 */
	private $auth_base_url = Client::AUTH_BASE_URL;

	/**
	 * @var string
	 */
	private $api_client_id;

	/**
	 * @var string
	 */
	private $api_client_secret;

	/**
	 * @var string
	 */
	private $api_client_scope = Client::API_CLIENT_SCOPE;

	/**
	 * @var string
	 */
	private $guard_type = Client::GUARD_DEFAULT;

	/**
	 * @param array                                $config
	 * @param \MicrosoftTranslator\LoggerInterface $logger
	 * @param \MicrosoftTranslator\HttpInterface   $http
	 * @param \MicrosoftTranslator\GuardInterface  $guard
	 *
	 * @throws \MicrosoftTranslator\Exception
	 */
	public function __construct( $config = array() , LoggerInterface $logger , HttpInterface $http , GuardInterface $guard = null )
	{
		$this->logger = $logger;
		$this->http   = $http;

		foreach ( $this->config_keys as $key )
		{
			if ( isset( $config[ $key ] ) )
			{
				$this->$key = $config[ $key ];
				$this->logger->debug( __CLASS__ , 'config' , sprintf( '%s = %s' , $key , $this->$key ) );
			}
		}

		// Init Auth Manager
		if ( is_null( $guard ) )
		{
			switch ( $this->guard_type )
			{
				case 'file':
					$guard = new GuardFile( $config , $logger );
					break;
				default:
					$class = $this->guard_type;
					$guard = new $class( $config , $logger );
					break;
			}
		}

		if ( ! $guard instanceof GuardInterface )
		{
			throw new Exception( 'Guard Manager is not an instance of MicrosoftTranslator\\GuardInterface' );
		}

		$this->guard = $guard;
	}


	/**
	 * Get an access token
	 *
	 * If available, the stored access token will be used
	 * If not available or if $force_new is true, a new one will be generated
	 *
	 * @param bool|false $force_new
	 *
	 * @return array|string
	 * @throws \MicrosoftTranslator\Exception
	 */
	public function getAccessToken( $force_new = false )
	{
		if ( $this->guard->hasAccessToken() )
		{
			$access_token = $this->guard->getAccessToken();

			return $access_token;
		}

		return $this->generateAndStoreNewAccessToken();
	}

	/**
	 * @return \MicrosoftTranslator\GuardInterface
	 */
	public function getGuard()
	{
		return $this->guard;
	}

	/**
	 * @return array|string
	 * @throws \MicrosoftTranslator\Exception
	 */
	private function generateAndStoreNewAccessToken()
	{
		$url          = trim( $this->auth_base_url , "/ \t\n\r\0\x0B" );
		$access_token = null;
		$auth         = array(
			'grant_type'    => 'client_credentials' ,
			'client_id'     => $this->api_client_id ,
			'client_secret' => $this->api_client_secret ,
			'scope'         => $this->api_client_scope ,
		);
		$result       = $this->http->post( $url , null , $auth , null );

		if ( Http::isRequestOk( $result ) )
		{
			$result[ 'http_body' ] = json_decode( $result[ 'http_body' ] , true );

			if ( ! isset( $result[ 'http_body' ][ 'access_token' ] ) )
			{
				throw new Exception( 'Access token not found in response' );
			}

			if ( ! is_string( $result[ 'http_body' ][ 'access_token' ] ) )
			{
				throw new Exception( 'Access token found in response but it is not a string' );
			}

			$access_token = strval( @$result[ 'http_body' ][ 'access_token' ] );
			$expires_in   = @(int)$result[ 'http_body' ][ 'expires_in' ];

			$this->logger->debug( __CLASS__ , 'oauth' , sprintf( 'New access_token generated %s...' , substr( $access_token , 0 , 10 ) ) );

			$this->guard->storeAccessTokenForSeconds( $access_token , $expires_in );
		}
		else
		{
			$this->logger->fatal( __CLASS__ , 'oauth' , 'Unable to generate a new access token : ' . json_encode( $result ) );
		}

		return $access_token;
	}
}
