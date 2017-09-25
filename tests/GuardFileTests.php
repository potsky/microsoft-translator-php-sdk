<?php

class GuardFileTests extends TestCase
{
    protected $configuration = [
        'api_client_key' => 'dumb',
    ];

    public function testCreate()
    {
        $client = new MicrosoftTranslator\Client($this->configuration);

        $this->assertInstanceOf('\\MicrosoftTranslator\\GuardInterface', $client->getAuth()
                                                                                ->getGuard());
    }

    public function testMandatoryConfiguration()
    {
        $this->expectException('\\MicrosoftTranslator\\Exception', 'api_client_key is mandatory');

        new MicrosoftTranslator\Client([]);
    }

    public function testGetAccessToken()
    {
        $guard = new MicrosoftTranslator\GuardFile($this->configuration, new MicrosoftTranslator\Logger($this->configuration));

        // remove all previous AT
        $guard->deleteAllAccessTokens();

        $this->assertNull($guard->getAccessToken());

        // store an already expired access token
        $this->assertTrue($guard->storeAccessTokenForSeconds('12345', 0));

        // at exists but is expired
        $this->assertNull($guard->getAccessToken());
    }

    public function testInvalidAccessTokenFoundInFile()
    {
        $guard = new MicrosoftTranslator\GuardFile($this->configuration, new MicrosoftTranslator\Logger($this->configuration));

        // remove all previous AT
        $guard->deleteAllAccessTokens();

        // store an already expired access token
        $this->assertTrue($guard->storeAccessTokenForSeconds('12345', 0));

        // given that we retrieve 1 second, it is invalid
        $this->assertFalse($guard->hasAccessToken());
    }

    public function testCleanAccessToken()
    {
        $guard = new MicrosoftTranslator\GuardFile($this->configuration, new MicrosoftTranslator\Logger($this->configuration));

        // remove all previous AT
        $guard->deleteAllAccessTokens();

        // store an access token for 1 second
        $this->assertTrue($guard->storeAccessTokenForSeconds('12345', 0));

        // given that we retrieve 1 second, it is invalid
        $this->assertEquals(1, $guard->cleanAccessTokens());
    }

    public function testDeleteOneAccessToken()
    {
        $guard = new MicrosoftTranslator\GuardFile($this->configuration, new MicrosoftTranslator\Logger($this->configuration));

        // remove all previous AT
        $guard->deleteAllAccessTokens();

        // store an access token for 1 second
        $this->assertTrue($guard->storeAccessTokenForSeconds('12345', 0));

        // given that we retrieve 1 second, it is invalid
        $this->assertTrue($guard->deleteAccessToken());
    }

    public function testIncorrectExpiration()
    {
        /** @var \Mockery\MockInterface|\MicrosoftTranslator\GuardFile $mockGuard */
        $mockGuard = \Mockery::mock('\\MicrosoftTranslator\\GuardFile', [$this->configuration, new \MicrosoftTranslator\Logger])
                             ->makePartial();
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $mockGuard->shouldReceive('loadTimestamp')
                  ->andReturn(["a" => "b"]);

        // remove all previous AT
        $mockGuard->deleteAllAccessTokens();

        // store an access token for 1 second
        $this->assertTrue($mockGuard->storeAccessTokenForSeconds('12345', 0));

        // at exists but is expired
        $this->assertNull($mockGuard->getAccessToken());
    }

    public function testFileError()
    {
        $configuration = array_merge($this->configuration, ['guard_file_dir_path' => '/tmp/this/dir/does/not/exist/']);

        $guard = new MicrosoftTranslator\GuardFile($configuration, new MicrosoftTranslator\Logger);

        // remove all previous AT
        $guard->deleteAllAccessTokens();

        // cannot store access token
        $this->expectException('\\MicrosoftTranslator\\Exception');
        $guard->storeAccessTokenForSeconds('12345', 100);
    }
}
