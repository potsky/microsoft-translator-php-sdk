<?php namespace MicrosoftTranslator;

class Client
{
    const VERSION = "0.0.2";

    const API_BASE_URL = 'http://api.microsofttranslator.com/V2/Http.svc';

    const AUTH_BASE_URL = 'https://api.cognitive.microsoft.com/sts/v1.0/issueToken';

    /*
     * The acces token is valid for 10 minutes. Obtain a new acces token every 10 minutes, and keep using the same access token for repeated requests within these 10 minutes.
     *
     * http://docs.microsofttranslator.com/text-translate.html
     */
    const AUTH_EXPIRE = 10*60;

    const GUARD_DEFAULT = 'file';

    /**
     * @var \MicrosoftTranslator\HttpInterface
     *
     * This holds the HTTP Manager
     *
     * You can use your own HTTP Manager by injecting it in the constructor
     * Your HTTP Manager must implement the \MicrosoftTranslator\HttpInterface interface
     */
    private $http;

    /**
     * @var \MicrosoftTranslator\AuthInterface
     *
     * This holds the Auth Manager
     *
     * You can use your own Auth Manager by injecting it in the constructor
     * Your Auth Manager must implement the \MicrosoftTranslator\AuthInterface interface
     */
    private $auth;

    /**
     * @var \MicrosoftTranslator\LoggerInterface
     *
     * This holds the Logger Manager
     *
     * You can use your own Logger Manager by injecting it in the constructor
     * Your Logger Manager must implement the \MicrosoftTranslator\LoggerInterface interface
     */
    private $logger;

    /**
     * Array of configuration parameters managed by this class
     *
     * @var array
     */
    private $config_keys = [
        'api_access_token',
        'api_base_url',
    ];

    /**
     * Configuration parameter
     *
     * If you pass an access token, all calls will be done with this access token
     * and the SDK will not handle it (no refresh for example)
     *
     * @var string
     */
    private $api_access_token;

    /**
     * Configuration parameter
     *
     * @var string
     */
    private $api_base_url = self::API_BASE_URL;

    /**
     * @param array $config                                     an array of configuration parameters
     * @param \MicrosoftTranslator\HttpInterface|null $http     if null, a new Http manager will be used
     * @param \MicrosoftTranslator\AuthInterface|null $auth     if null, a new Auth manager will be used
     * @param \MicrosoftTranslator\LoggerInterface|null $logger if null, a new Logger manager will be used
     *
     * @throws Exception
     */
    public function __construct($config = [], $http = null, $auth = null, $logger = null)
    {
        // Init logger at first
        if (is_null($logger)) {
            $logger = new Logger($config);
        }

        if (! $logger instanceof LoggerInterface) {
            throw new Exception('Logger Manager is not an instance of MicrosoftTranslator\\LoggerInterface');
        }

        $this->logger = $logger;

        // Load configuration for Client
        foreach ($this->config_keys as $key) {
            if (isset($config[$key])) {
                $this->$key = $config[$key];
                $this->logger->debug(__CLASS__, 'config', sprintf('%s = %s', $key, $this->$key));
            }
        }

        // Init HTTP Manager
        if (is_null($http)) {
            $http = new Http($config, $logger);
        }

        if (! $http instanceof HttpInterface) {
            throw new Exception('HTTP Manager is not an instance of MicrosoftTranslator\\HttpInterface');
        }

        $this->http = $http;

        // Init Auth Manager
        if (is_null($auth)) {
            $auth = new Auth($config, $logger, $http);
        }

        if (! $auth instanceof AuthInterface) {
            throw new Exception('Auth Manager is not an instance of MicrosoftTranslator\\AuthInterface');
        }

        $this->auth = $auth;
    }

    /**
     * Return the Auth Manager
     *
     * It can be useful to delete or clean up old access tokens :
     * - $msTranslator->getAuth()->getGuard()->cleanAccessTokens()
     * - $msTranslator->getAuth()->getGuard()->deleteAllAccessTokens()
     *
     * @return AuthInterface
     */
    public function getAuth()
    {
        return $this->auth;
    }

    /**
     * Translates a text string from one language to another.
     *
     * @param string $text             Required. A string representing the text to translate. The size of the text must
     *                                 not exceed 10000 characters.
     * @param string $to               Required. A string representing the language code to translate the text into.
     * @param string|null $from        Optional. A string representing the language code of the translation text.
     * @param string $contentType      Optional. The format of the text being translated. The supported formats are
     *                                 "text/plain" and "text/html". Any HTML needs to be well-formed.
     * @param string $category         Optional. A string containing the category (domain) of the translation. Defaults
     *                                 to "general".
     *
     * The language codes are available at https://msdn.microsoft.com/en-us/library/hh456380.aspx
     *
     * The API endpoint documentation is available at https://msdn.microsoft.com/en-us/library/ff512421.aspx
     *
     * @return \MicrosoftTranslator\Response
     * @throws \MicrosoftTranslator\Exception
     */
    public function translate($text, $to, $from = null, $contentType = 'text/plain', $category = 'general')
    {
        $query_parameters = [
            'text'        => $text,
            'to'          => $to,
            'contentType' => $contentType,
            'category'    => $category,
        ];

        if (! is_null($from)) {
            $query_parameters['from'] = $from;
        }

        return $this->get('/Translate', [], $query_parameters);
    }

    /**
     * Translates a text string from one language to another.
     *
     * @param string $text Required. A string representing the text to translate. The size of the text must
     *                     not exceed 10000 characters.
     * @param string $from Required. A string representing the language code of input text.
     *
     * The language codes are available at https://msdn.microsoft.com/en-us/library/hh456380.aspx
     *
     * The API endpoint documentation is available at https://msdn.microsoft.com/en-us/library/ff512410.aspx
     *
     * @return \MicrosoftTranslator\Response
     * @throws \MicrosoftTranslator\Exception
     */
    public function breakSentences($text, $from)
    {
        $query_parameters = [
            'text'     => $text,
            'language' => $from,
        ];

        return $this->get('/BreakSentences', [], $query_parameters);
    }

    /**
     * The TransformText method is a text normalization function for social media, which returns a normalized form of
     * the input. The method can be used as a preprocessing step in Machine Translation or other applications, which
     * expect clean input text than is typically found in social media or user-generated content. The function
     * currently works only with English input.
     *
     * @param string $text             Required. A string representing the text to translate. The size of the text must
     *                                 not exceed 10000 characters.
     * @param string $from             Required. A string representing the language code. This parameter supports only
     *                                 English with "en" as the language name.
     * @param string|null $category    Optional. A string containing the category or domain of the translation. This
     *                                 parameter supports only the default option general.
     *
     * The language codes are available at https://msdn.microsoft.com/en-us/library/hh456380.aspx
     *
     * The API endpoint documentation is available at https://msdn.microsoft.com/en-us/library/dn876735.aspx
     *
     * @return \MicrosoftTranslator\Response
     * @throws \MicrosoftTranslator\Exception
     */
    public function TransformText($text, $from, $category = 'general')
    {
        $query_parameters = [
            'sentence' => $text,
            'language' => $from,
            'category' => $category,
        ];

        return $this->get('/TransformText', [], $query_parameters, true, 'http://api.microsofttranslator.com/V3/json/');
    }

    /**
     * Use the TranslateArray method to retrieve translations for multiple source texts.
     *
     * @param array $texts             Required. An array containing the texts for translation. All strings must be of
     *                                 the same language. The total of all texts to be translated must not exceed 10000
     *                                 characters. The maximum number of array elements is 2000.
     * @param string $to               Required. A string representing the language code to translate the text into.
     * @param string|null $from        Optional. A string representing the language code of the translation text.
     * @param string $contentType      Optional. The format of the text being translated. The supported formats are
     *                                 "text/plain" and "text/html". Any HTML needs to be well-formed.
     * @param string $category         Optional. A string containing the category (domain) of the translation. Defaults
     *                                 to "general".
     *
     * The language codes are available at https://msdn.microsoft.com/en-us/library/hh456380.aspx
     *
     * The API endpoint documentation is available at https://msdn.microsoft.com/en-us/library/ff512422.aspx
     *
     * @return \MicrosoftTranslator\Response
     * @throws \MicrosoftTranslator\Exception
     */
    public function translateArray(array $texts, $to, $from = null, $contentType = 'text/plain', $category = 'general')
    {
        $requestXml = "<TranslateArrayRequest>"."<AppId/>"."<From>$from</From>"."<Options>"."<Category xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\">$category</Category>"."<ContentType xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\">$contentType</ContentType>"."<ReservedFlags xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />"."<State xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />"."<Uri xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />"."<User xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />"."</Options>"."<Texts>";
        foreach ($texts as $text) {
            $requestXml .= "<string xmlns=\"http://schemas.microsoft.com/2003/10/Serialization/Arrays\"><![CDATA[$text]]></string>";
        }
        $requestXml .= "</Texts>"."<To>$to</To>"."</TranslateArrayRequest>";

        return $this->post('/TranslateArray', [], $requestXml, true, $texts);
    }

    /**
     * Retrieves friendly names for the languages passed in as the parameter languageCodes, and localized using the
     * passed locale language.
     *
     * @param string $locale              Required. A string representing a combination of an ISO 639 two-letter
     *                                    lowercase culture code associated with a language and an ISO 3166 two-letter
     *                                    uppercase subculture code to localize the language names or a ISO 639
     *                                    lowercase culture code by itself.
     * @param string|array $languageCodes Required. A string or an array representing the ISO 639-1 language codes to
     *                                    retrieve the friendly name for.
     *
     * The language codes are available at https://msdn.microsoft.com/en-us/library/hh456380.aspx
     *
     * The API endpoint documentation is available at https://msdn.microsoft.com/en-us/library/ff512414.aspx
     *
     * @return \MicrosoftTranslator\Response
     * @throws \MicrosoftTranslator\Exception
     */
    public function getLanguageNames($locale, $languageCodes)
    {
        if (is_string($languageCodes)) {
            $languageCodes = [$languageCodes];
        }

        /** @noinspection XmlUnusedNamespaceDeclaration */
        $requestXml = '<ArrayOfstring xmlns="http://schemas.microsoft.com/2003/10/Serialization/Arrays" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">';
        if (sizeof($languageCodes) > 0) {
            foreach ($languageCodes as $codes) {
                $requestXml .= "<string>$codes</string>";
            }
        } else {
            throw new Exception('$languageCodes array is empty.');
        }
        $requestXml .= '</ArrayOfstring>';

        return $this->post('/GetLanguageNames?locale='.$locale, [], $requestXml, true, $languageCodes);
    }

    /**
     * Obtain a list of language codes representing languages that are supported by the Translation Service.
     * translate() and translateArray() can translate between any two of these languages.
     *
     * @return \MicrosoftTranslator\Response
     */
    public function getLanguagesForTranslate()
    {
        return $this->get('/GetLanguagesForTranslate');
    }

    /**
     * Use the Detect Method to identify the language of a selected piece of text.
     *
     * @param string $text Required. A string containing some text whose language is to be identified. The size of the
     *                     text must not exceed 10000 characters.
     *
     * The API endpoint documentation is available at https://msdn.microsoft.com/en-us/library/ff512411.aspx
     *
     * @return \MicrosoftTranslator\Response
     * @throws \MicrosoftTranslator\Exception
     */
    public function detect($text)
    {
        $query_parameters = [
            'text' => $text,
        ];

        return $this->get('/Detect', [], $query_parameters);
    }

    /**
     * Use the DetectArray Method to identify the language of an array of string at once. Performs independent
     * detection of each individual array element and returns a result for each row of the array.
     *
     * @param array $texts Required. A string array representing the text from an unknown language. The size of the
     *                     text must not exceed 10000 characters.
     *
     * The API endpoint documentation is available at https://msdn.microsoft.com/en-us/library/ff512412.aspx
     *
     * @return \MicrosoftTranslator\Response
     * @throws \MicrosoftTranslator\Exception
     */
    public function detectArray(array $texts)
    {
        /** @noinspection XmlUnusedNamespaceDeclaration */
        $requestXml = '<ArrayOfstring xmlns="http://schemas.microsoft.com/2003/10/Serialization/Arrays" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">';
        if (sizeof($texts) > 0) {
            foreach ($texts as $str) {
                $requestXml .= "<string>$str</string>";
            }
        } else {
            throw new Exception('$texts array is empty.');
        }
        $requestXml .= '</ArrayOfstring>';

        return $this->post('/DetectArray', [], $requestXml, true, $texts);
    }

    /**
     * Build the URL according to endpoint by replacing URL parameters
     *
     * @param string $endpoint
     * @param array $url_parameters
     * @param string|null $special_url
     *
     * @return string
     */
    private function buildUrl($endpoint, $url_parameters = [], $special_url = null)
    {
        foreach ($url_parameters as $key => $value) {
            //@codeCoverageIgnoreStart
            $endpoint = str_replace('{'.$key.'}', $value, $endpoint);
            //@codeCoverageIgnoreEnd
        }

        if (is_null($special_url)) {
            $url = trim($this->api_base_url, "/ \t\n\r\0\x0B");
        } else {
            $url = $special_url;
        }
        $url = $url.'/'.trim($endpoint, "/ \t\n\r\0\x0B");

        return $url;
    }

    /**
     * @param string $endpoint
     * @param array $url_parameters
     * @param array $query_parameters
     * @param bool $try_to_auth
     * @param string $url
     * @param string $result
     * @param mixed|null $originalData
     *
     * @return \MicrosoftTranslator\Response
     * @throws \MicrosoftTranslator\Exception
     */
    private function getResponseObject($endpoint, $url_parameters, $query_parameters, $try_to_auth, $url, $result, $originalData = null)
    {
        if ((isset($result['http_code'])) && (substr(strval($result['http_code']), 0, 1) !== '2')) {
            throw new Exception($result);
        }
        if (isset($result['http_body'])) {
            if (Tools::startsWith($result['http_body'], '<string ')) {
                $xmlObj              = simplexml_load_string($result['http_body']);
                $result['http_body'] = strval($xmlObj[0]);
            } else {
                if (Tools::startsWith($result['http_body'], '<ArrayOfTranslateArrayResponse ')) {
                    $xmlObj = simplexml_load_string($result['http_body']);
                    $array  = [];
                    $i      = 0;

                    /** @noinspection PhpUndefinedFieldInspection */
                    foreach ($xmlObj->TranslateArrayResponse as $translatedArrObj) {
                        if (@isset($originalData[$i])) {
                            /** @noinspection PhpUndefinedFieldInspection */
                            $array[$originalData[$i]] = strval($translatedArrObj->TranslatedText);
                        } //@codeCoverageIgnoreStart
                        else {
                            /** @noinspection PhpUndefinedFieldInspection */
                            $array[] = strval($translatedArrObj->TranslatedText);
                        }
                        //@codeCoverageIgnoreEnd
                        $i++;
                    }

                    $result['http_body'] = $array;
                } else {
                    if (Tools::startsWith($result['http_body'], '<ArrayOfstring ')) {
                        $xmlObj = simplexml_load_string($result['http_body']);
                        $array  = [];
                        $i      = 0;

                        /** @noinspection PhpUndefinedFieldInspection */
                        foreach ($xmlObj->string as $language) {
                            if (@isset($originalData[$i])) {
                                $array[$originalData[$i]] = strval($language);
                            } else {
                                $array[] = strval($language);
                            }
                            $i++;
                        }

                        $result['http_body'] = $array;
                    } else {
                        if (Tools::startsWith($result['http_body'], '<ArrayOfint ')) {
                            $xmlObj   = simplexml_load_string($result['http_body']);
                            $array    = [];
                            $i        = 1;
                            $startLen = 0;

                            /** @noinspection PhpUndefinedFieldInspection */
                            foreach ($xmlObj->int as $strLen) {
                                $endLen   = (int) $strLen;
                                $array[]  = substr($query_parameters['text'], $startLen, $endLen);
                                $startLen += $endLen;
                                $i++;
                            }

                            $result['http_body'] = $array;
                        } else {
                            if (! is_null($json = json_decode(Tools::removeUtf8Bom($result['http_body']), true))) {
                                $result['http_body'] = $json;
                            }
                        }
                    }
                }
            }
        }

        return new Response($endpoint, $url_parameters, $query_parameters, $try_to_auth, $url, $result);
    }

    /**
     * @param string $endpoint
     * @param array $url_parameters
     * @param array $query_parameters
     * @param bool $try_to_auth if set to false, no access token will be used
     * @param null $special_url new url instead of api url
     *
     * @return \MicrosoftTranslator\Response
     * @throws \MicrosoftTranslator\Exception
     */
    private function get($endpoint, $url_parameters = [], $query_parameters = [], $try_to_auth = true, $special_url = null)
    {
        $url              = $this->buildUrl($endpoint, $url_parameters, $special_url);
        $api_access_token = ($try_to_auth === true) ? (is_null($this->api_access_token)) ? $this->auth->getAccessToken() : $this->api_access_token : '';
        $query_parameters = $this->fixQueryParameters($query_parameters);

        return $this->getResponseObject($endpoint, $url_parameters, $query_parameters, $try_to_auth, $url, $this->http->get($url, $api_access_token, $query_parameters));
    }

    /**
     * @param string $endpoint
     * @param array $url_parameters
     * @param string|array $query_parameters
     * @param bool $try_to_auth if set to false, no access token will be used
     * @param mixed|null $originalData
     *
     * @return \MicrosoftTranslator\Response
     * @throws \MicrosoftTranslator\Exception
     */
    private function post($endpoint, $url_parameters = [], $query_parameters = [], $try_to_auth = true, $originalData = null)
    {
        $url              = $this->buildUrl($endpoint, $url_parameters);
        $api_access_token = ($try_to_auth === true) ? (is_null($this->api_access_token)) ? $this->auth->getAccessToken() : $this->api_access_token : '';
        $query_parameters = $this->fixQueryParameters($query_parameters);

        return $this->getResponseObject($endpoint, $url_parameters, $query_parameters, $try_to_auth, $url, $this->http->post($url, $api_access_token, $query_parameters), $originalData);
    }

    /** @noinspection PhpUnusedPrivateMethodInspection
     * @param string $endpoint
     * @param array $url_parameters
     * @param string|array $query_parameters
     * @param bool $try_to_auth if set to false, no access token will be used
     * @param mixed|null $originalData
     *
     * @return \MicrosoftTranslator\Response
     * @throws \MicrosoftTranslator\Exception
     * @codeCoverageIgnore
     */
    private function put($endpoint, $url_parameters = [], $query_parameters = [], $try_to_auth = true, $originalData = null)
    {
        $url              = $this->buildUrl($endpoint, $url_parameters);
        $api_access_token = ($try_to_auth === true) ? (is_null($this->api_access_token)) ? $this->auth->getAccessToken() : $this->api_access_token : '';
        $query_parameters = $this->fixQueryParameters($query_parameters);

        return $this->getResponseObject($endpoint, $url_parameters, $query_parameters, $try_to_auth, $url, $this->http->put($url, $api_access_token, $query_parameters), $originalData);
    }

    /** @noinspection PhpUnusedPrivateMethodInspection
     * @param string $endpoint
     * @param array $url_parameters
     * @param string|array $query_parameters
     * @param bool $try_to_auth if set to false, no access token will be used
     * @param mixed|null $originalData
     *
     * @return \MicrosoftTranslator\Response
     * @throws \MicrosoftTranslator\Exception
     * @codeCoverageIgnore
     */
    private function delete($endpoint, $url_parameters = [], $query_parameters = [], $try_to_auth = true, $originalData = null)
    {
        $url              = $this->buildUrl($endpoint, $url_parameters);
        $api_access_token = ($try_to_auth === true) ? (is_null($this->api_access_token)) ? $this->auth->getAccessToken() : $this->api_access_token : '';
        $query_parameters = $this->fixQueryParameters($query_parameters);

        return $this->getResponseObject($endpoint, $url_parameters, $query_parameters, $try_to_auth, $url, $this->http->delete($url, $api_access_token, $query_parameters), $originalData);
    }

    /**
     * @param string|array $query_parameters
     *
     * @return array
     */
    private function fixQueryParameters($query_parameters)
    {
        if (is_array($query_parameters)) {
            $query_parameters['appId'] = '';
        }

        return $query_parameters;
    }
}

