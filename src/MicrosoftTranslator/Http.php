<?php namespace MicrosoftTranslator;

class Http implements HttpInterface
{
    /**
     * @var \MicrosoftTranslator\LoggerInterface
     */
    private $logger;

    /**
     * Array of configuration parameters managed by this class
     *
     * @var array
     */
    private $config_keys = [
        'http_timeout',
        'http_proxy_host',
        'http_proxy_type',
        'http_proxy_auth',
        'http_proxy_port',
        'http_proxy_user',
        'http_proxy_pass',
        'http_user_agent',
    ];

    /**
     * Timeout for API requests
     *
     * @var int
     */
    private $http_timeout = 10;

    /**
     * An IP or hostname to use for the proxy
     * Let null for direct connexion
     *
     * @var string|null
     */
    private $http_proxy_host = null;

    /**
     * One of these constants :
     * - CURLPROXY_HTTP (default)
     * - CURLPROXY_SOCKS4
     * - CURLPROXY_SOCKS5
     *
     * @var int|null
     */
    private $http_proxy_type = null;

    /**
     * One of these constants:
     * - CURLAUTH_BASIC (default)
     * - CURLAUTH_NTLM
     *
     * @var int|null
     */
    private $http_proxy_auth = null;

    /**
     * The proxy port (default is 3128)
     *
     * @var int|null
     */
    private $http_proxy_port = 3128;

    /**
     * The username to connect to proxy
     *
     * @var string|null
     */
    private $http_proxy_user = null;

    /**
     * The password to connect to proxy
     *
     * @var string|null
     */
    private $http_proxy_pass = null;

    /**
     * The user agent used to make requests
     *
     * @var string
     */
    private $http_user_agent = 'MicrosoftTranslator PHP SDK v%VERSION%';

    /**
     * @param array $config
     * @param \MicrosoftTranslator\LoggerInterface $logger
     */
    public function __construct($config, LoggerInterface $logger)
    {
        $this->logger = $logger;

        foreach ($this->config_keys as $key) {
            if (isset($config[$key])) {
                $this->$key = $config[$key];
                $this->logger->debug(__CLASS__, 'config', $key.' = '.$this->$key);
            }
        }
    }

    /**
     * Check the http_code in the response array and tell whether if is 200 or 201
     *
     * @param array $result
     *
     * @return bool
     */
    public static function isRequestOk($result)
    {
        return in_array(@$result['http_code'], [200, 201]);
    }

    /**
     * GET API endpoint
     *
     * @param string $url
     * @param string|null $access_token
     * @param array $parameters
     * @param string $contentType
     *
     * @return array
     */
    public function get($url, $access_token = null, $parameters = [], $contentType = 'text/xml')
    {
        return $this->doApiCall($url, 'GET', $access_token, $parameters, $contentType);
    }

    /**
     * POST API endpoint
     *
     * @param string $url
     * @param string|null $access_token
     * @param string|array $parameters
     * @param string $contentType
     *
     * @return array
     */
    public function post($url, $access_token = null, $parameters = [], $contentType = 'text/xml')
    {
        return $this->doApiCall($url, 'POST', $access_token, $parameters, $contentType);
    }

    /**
     * PUT API endpoint
     *
     * @param string $url
     * @param string|null $access_token
     * @param string|array $parameters
     * @param string $contentType
     *
     * @return array
     */
    public function put($url, $access_token = null, $parameters = [], $contentType = 'text/xml')
    {
        return $this->doApiCall($url, 'PUT', $access_token, $parameters, $contentType);
    }

    /**
     * DELETE API endpoint
     *
     * @param string $url
     * @param string|null $access_token
     * @param string|array $parameters
     * @param string $contentType
     *
     * @return array
     */
    public function delete($url, $access_token = null, $parameters = [], $contentType = 'text/xml')
    {
        return $this->doApiCall($url, 'DELETE', $access_token, $parameters, $contentType);
    }

    /**
     * Execute the request with cURL
     *
     * Made public for unit tests, you can publicly call it but this method is not really interesting!
     *
     * @param array $config
     *
     * @return array
     */
    public function execCurl(array $config)
    {
        $config[CURLOPT_VERBOSE]        = false;
        $config[CURLOPT_SSL_VERIFYPEER] = false;
        $config[CURLOPT_RETURNTRANSFER] = true;

        if (defined('CURLOPT_IPRESOLVE')) // PHP5.3
        {
            $config[CURLOPT_IPRESOLVE] = CURL_IPRESOLVE_V4;
        }

        $ch = curl_init();

        foreach ($config as $key => $value) {
            curl_setopt($ch, $key, $value);
        }

        $result      = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error       = curl_error($ch);
        $error_code  = curl_errno($ch);

        curl_close($ch);

        return [$result, $status_code, $error, $error_code];
    }

    /**
     * @param string $url
     * @param string $method
     * @param string|null $access_token
     * @param string|array $parameters
     * @param string $contentType
     *
     * @return array
     */
    private function doApiCall($url, $method, $access_token = null, $parameters = [], $contentType = 'text/xml')
    {
        $request = [];
        $headers = [];

        $request[CURLOPT_TIMEOUT]       = (int) $this->http_timeout;
        $request[CURLOPT_USERAGENT]     = str_replace('%VERSION%', Client::VERSION, $this->http_user_agent);
        $request[CURLOPT_CUSTOMREQUEST] = $method;

        if (isset($parameters['Ocp-Apim-Subscription-Key'])) {
            $headers[] = "Ocp-Apim-Subscription-Key: ".$parameters['Ocp-Apim-Subscription-Key'];
            $headers[] = "Content-Length: 0";
            unset($parameters['Ocp-Apim-Subscription-Key']);
        }

        if (! empty($parameters)) {
            if ($method === 'GET') {
                $url = Tools::httpBuildUrl($url, ["query" => http_build_query($parameters)], Tools::HTTP_URL_JOIN_QUERY);
            } else {
                if (is_array($parameters)) {
                    $request[CURLOPT_POSTFIELDS] = http_build_query($parameters);
                } else {
                    if (is_string($parameters)) {
                        $request[CURLOPT_POSTFIELDS] = $parameters;
                    }
                }
            }
        }

        $request[CURLOPT_URL] = $url;

        if (! is_null($contentType)) {
            $headers[] = "Content-Type: $contentType";
        }

        if (! is_null($access_token)) {
            $headers[] = 'Authorization: Bearer '.$access_token;
        }

        if (! empty($this->http_proxy_host)) {
            $request[CURLOPT_PROXY] = $this->http_proxy_host;

            if (! empty($this->http_proxy_port)) {
                $request[CURLOPT_PROXYPORT] = $this->http_proxy_port;
            }

            if (! empty($this->http_proxy_type)) {
                $request[CURLOPT_PROXYTYPE] = $this->http_proxy_type;
            }

            if (! empty($this->http_proxy_auth)) {
                $request[CURLOPT_PROXYAUTH] = $this->http_proxy_auth;
            }

            if (! empty($this->http_proxy_user)) {
                $request[CURLOPT_PROXYUSERPWD] = $this->http_proxy_user.':'.$this->http_proxy_pass;
            }
        }

        $request[CURLOPT_HTTPHEADER] = $headers;

        $this->logger->info(__CLASS__, 'api', sprintf('%s %s', $method, $url));

        $start = microtime(true);
        @list($result, $status_code, $error, $errno) = $this->execCurl($request);
        $end = microtime(true);

        $duration = (int) round(($end - $start) * 1000);

        if ($errno === 0) {
            $return = [
                'http_code' => $status_code,
                'http_body' => $result,
                'duration'  => $duration,
            ];

            if ($status_code >= 400) {
                $this->logger->error(__CLASS__, 'api', sprintf('Response HTTP code %s, body length %s bytes, duration %sms on endpoint %s %s', $status_code, strlen($result), $duration, $method, $url));
            } else {
                if ($status_code >= 300) {
                    $this->logger->warning(__CLASS__, 'api', sprintf('Response HTTP code %s, body length %s bytes, duration %sms on endpoint %s %s', $status_code, strlen($result), $duration, $method, $url));
                } else {
                    $this->logger->info(__CLASS__, 'api', sprintf('Response HTTP code %s, body length %s bytes, duration %sms', $status_code, strlen($result), $duration));
                }
            }
        } else {
            $return = [
                'error_msg' => $error,
                'error_num' => $errno,
                'duration'  => $duration,
            ];

            $this->logger->error(__CLASS__, 'api', sprintf('cURL error #%s : %s', $errno, $error));
        }

        return $return;
    }
}
