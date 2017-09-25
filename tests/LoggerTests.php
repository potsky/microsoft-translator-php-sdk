<?php

class LoggerTests extends TestCase
{
    public function testNoLogErrorLog()
    {
        $configuration = [
            'api_client_key' => 'CLIENT_ID',
        ];

        $logger = new \MicrosoftTranslator\Logger($configuration);

        $this->assertFalse($logger->debug('object', 'category', 'message'));
        $this->assertFalse($logger->info('object', 'category', 'message'));
        $this->assertFalse($logger->warning('object', 'category', 'message'));
        $this->assertFalse($logger->error('object', 'category', 'message'));

        $this->expectException('\\MicrosoftTranslator\\Exception');
        $logger->fatal('object', 'category', 'message');
    }

    public function testDebugErrorLog()
    {
        $configuration = [
            'api_client_key' => 'CLIENT_ID',
            'log_level'      => MicrosoftTranslator\Logger::LEVEL_DEBUG,
        ];

        $logger = new \MicrosoftTranslator\Logger($configuration);

        $this->assertTrue($logger->debug('object', 'category', 'message'));
        $this->assertTrue($logger->info('object', 'category', 'message'));
        $this->assertTrue($logger->warning('object', 'category', 'message'));
        $this->assertTrue($logger->error('object', 'category', 'message'));
    }

    public function testInfoErrorLog()
    {
        $configuration = [
            'api_client_key' => 'CLIENT_ID',
            'log_level'      => MicrosoftTranslator\Logger::LEVEL_INFO,
        ];

        $logger = new \MicrosoftTranslator\Logger($configuration);

        $this->assertFalse($logger->debug('object', 'category', 'message'));
        $this->assertTrue($logger->info('object', 'category', 'message'));
        $this->assertTrue($logger->warning('object', 'category', 'message'));
        $this->assertTrue($logger->error('object', 'category', 'message'));
    }

    public function testWarningErrorLog()
    {
        $configuration = [
            'api_client_key' => 'CLIENT_ID',
            'log_level'      => MicrosoftTranslator\Logger::LEVEL_WARNING,
        ];

        $logger = new \MicrosoftTranslator\Logger($configuration);

        $this->assertFalse($logger->debug('object', 'category', 'message'));
        $this->assertFalse($logger->info('object', 'category', 'message'));
        $this->assertTrue($logger->warning('object', 'category', 'message'));
        $this->assertTrue($logger->error('object', 'category', 'message'));
    }

    public function testErrorErrorLog()
    {
        $configuration = [
            'api_client_key' => 'CLIENT_ID',
            'log_level'      => MicrosoftTranslator\Logger::LEVEL_ERROR,
        ];

        $logger = new \MicrosoftTranslator\Logger($configuration);

        $this->assertFalse($logger->debug('object', 'category', 'message'));
        $this->assertFalse($logger->info('object', 'category', 'message'));
        $this->assertFalse($logger->warning('object', 'category', 'message'));
        $this->assertTrue($logger->error('object', 'category', 'message'));
    }

    public function testDebugFileNoAccess()
    {
        $configuration = [
            'api_client_key' => 'CLIENT_ID',
            'log_level'      => MicrosoftTranslator\Logger::LEVEL_DEBUG,
            'log_file_path'  => '/tmp/this/file/does/not/exists.log',
        ];

        $this->expectException('\\MicrosoftTranslator\\Exception', 'Unable to write to log file /tmp/this/file/does/not/exists.log');

        new \MicrosoftTranslator\Logger($configuration);
    }
}
