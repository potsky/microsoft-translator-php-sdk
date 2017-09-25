<?php

class AuthTests extends TestCase
{
    protected $configuration = [
        'api_lang'       => 'en',
        'api_client_key' => 'dumb',
    ];

    public function testAuthWithDefaultGuard()
    {
        $client = new MicrosoftTranslator\Client([
            'api_client_key' => 'dumb',
        ]);

        $auth = $client->getAuth();

        $this->assertInstanceOf('MicrosoftTranslator\\Auth', $auth);
    }

    public function testAuthWithIncorrectGuard()
    {
        $this->expectException('\\MicrosoftTranslator\\Exception', 'Guard Manager is not an instance of MicrosoftTranslator\\GuardInterface');

        new MicrosoftTranslator\Client([
            'api_client_key' => 'dumb',
            'guard_type'     => '\\MicrosoftTranslator\\Logger',
        ]);
    }

    public function testGetGuard()
    {
        $client = new MicrosoftTranslator\Client([
            'api_client_key' => 'dumb',
        ]);

        $this->assertInstanceOf('\\MicrosoftTranslator\\GuardInterface', $client->getAuth()
                                                                                ->getGuard());
    }

    public function testGetAccessToken()
    {
        $access_token = 'LpL7uAhIz2TyYu8ZWvU62k9v3bbetCs8dxwcluRB';
        $result       = $access_token;

        /** @var \Mockery\MockInterface|\MicrosoftTranslator\Http $mockHttp */
        $mockHttp = \Mockery::mock('\\MicrosoftTranslator\\Http', [$this->configuration, new \MicrosoftTranslator\Logger])
                            ->makePartial();
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $mockHttp->shouldReceive('execCurl')
                 ->andReturn([$result, 200, '', 0]);

        $auth = new \MicrosoftTranslator\Auth($this->configuration, new \MicrosoftTranslator\Logger, $mockHttp);

        // compute
        $auth->getGuard()
             ->deleteAllAccessTokens();

        $this->assertEquals($access_token, $auth->getAccessToken());

        // get from cache
        $this->assertEquals($access_token, $auth->getAccessToken());
    }

    public function testGetAccessTokenWithHttpError()
    {
        /** @var \Mockery\MockInterface|\MicrosoftTranslator\Http $mockHttp */
        $mockHttp = \Mockery::mock('\\MicrosoftTranslator\\Http', [$this->configuration, new \MicrosoftTranslator\Logger])
                            ->makePartial();
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $mockHttp->shouldReceive('execCurl')
                 ->andReturn(['', 500, '', 0]);

        $auth = new \MicrosoftTranslator\Auth($this->configuration, new \MicrosoftTranslator\Logger, $mockHttp);
        $auth->getGuard()
             ->deleteAllAccessTokens();

        $this->expectException('\\MicrosoftTranslator\\Exception');
        $auth->getAccessToken();
    }

    public function testGetInvalidAccessToken()
    {
        $result = false;

        /** @var \Mockery\MockInterface|\MicrosoftTranslator\Http $mockHttp */
        $mockHttp = \Mockery::mock('\\MicrosoftTranslator\\Http', [$this->configuration, new \MicrosoftTranslator\Logger])
                            ->makePartial();
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $mockHttp->shouldReceive('execCurl')
                 ->andReturn([$result, 200, '', 0]);

        $auth = new \MicrosoftTranslator\Auth($this->configuration, new \MicrosoftTranslator\Logger, $mockHttp);
        $auth->getGuard()
             ->deleteAllAccessTokens();

        $this->expectException('\\MicrosoftTranslator\\Exception');
        $auth->getAccessToken();
    }

    public function testGetUndefinedAccessToken()
    {
        $result = false;

        /** @var \Mockery\MockInterface|\MicrosoftTranslator\Http $mockHttp */
        $mockHttp = \Mockery::mock('\\MicrosoftTranslator\\Http', [$this->configuration, new \MicrosoftTranslator\Logger])
                            ->makePartial();
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $mockHttp->shouldReceive('execCurl')
                 ->andReturn([$result, 200, '', 0]);

        $auth = new \MicrosoftTranslator\Auth($this->configuration, new \MicrosoftTranslator\Logger, $mockHttp);
        $auth->getGuard()
             ->deleteAllAccessTokens();

        $this->expectException('\\MicrosoftTranslator\\Exception', 'Access token not found in response');
        $auth->getAccessToken();
    }
}
