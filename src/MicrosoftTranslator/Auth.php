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
    private $config_keys = [
        'auth_base_url',
        'api_client_key',
        'guard_type',
    ];

    /**
     * @var string
     */
    private $auth_base_url = Client::AUTH_BASE_URL;

    /**
     * @var string
     */
    private $auth_expire = Client::AUTH_EXPIRE;

    /**
     * @var string
     */
    private $api_client_key;

    /**
     * @var string
     */
    private $guard_type = Client::GUARD_DEFAULT;

    /**
     * @param array $config
     * @param \MicrosoftTranslator\LoggerInterface $logger
     * @param \MicrosoftTranslator\HttpInterface $http
     * @param \MicrosoftTranslator\GuardInterface $guard
     *
     * @throws \MicrosoftTranslator\Exception
     */
    public function __construct($config = [], LoggerInterface $logger, HttpInterface $http, GuardInterface $guard = null)
    {
        $this->logger = $logger;
        $this->http   = $http;

        foreach ($this->config_keys as $key) {
            if (isset($config[$key])) {
                $this->$key = $config[$key];
                $this->logger->debug(__CLASS__, 'config', sprintf('%s = %s', $key, $this->$key));
            }
        }

        // Init Auth Manager
        if (is_null($guard)) {
            switch ($this->guard_type) {
                case 'file':
                    $guard = new GuardFile($config, $logger);
                    break;
                default:
                    $class = $this->guard_type;
                    $guard = new $class($config, $logger);
                    break;
            }
        }

        if (! $guard instanceof GuardInterface) {
            throw new Exception('Guard Manager is not an instance of MicrosoftTranslator\\GuardInterface');
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
    public function getAccessToken($force_new = false)
    {
        if ($this->guard->hasAccessToken()) {
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
        $url          = trim($this->auth_base_url, "/ \t\n\r\0\x0B");
        $access_token = null;
        $auth         = ['Ocp-Apim-Subscription-Key' => $this->api_client_key,];
        $result       = $this->http->post($url, null, $auth, "application/json;charset=utf-8");

        if (Http::isRequestOk($result)) {
            if (! is_string($result['http_body'])) {
                throw new Exception('Access token found in response but it is not a string');
            }

            $access_token = strval(@$result['http_body']);

            $this->logger->debug(__CLASS__, 'oauth', sprintf('New access_token generated %s...', substr($access_token, 0, 10)));

            $this->guard->storeAccessTokenForSeconds($access_token , $this->auth_expire);
        } else {
            $this->logger->fatal(__CLASS__, 'oauth', 'Unable to generate a new access token : '.json_encode($result));
        }

        return $access_token;
    }
}
