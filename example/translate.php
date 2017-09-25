<?php
include __DIR__.'/../src/MicrosoftTranslator.php';

/*
 * Configure the translator
 */
$config = [
    'log_level'      => \MicrosoftTranslator\Logger::LEVEL_DEBUG,
    'api_client_key' => '',
];

/*
 * I set api token from my computer. No key on github please!
 */
if (empty($config["api_client_key"])) {
    $config["api_client_key"] = getenv('MICROSOFT_TRANSLATOR_CLIENT_KEY');
}

/*
 * Load the translator
 */
$msTranslator = new \MicrosoftTranslator\Client($config);

echo "\n" . $msTranslator->translate('hello', 'fr')->getBody() . "\n\n";

/*
 * Get a second loader to verify that the access token is correctly saved
 */
$msTranslator2 = new \MicrosoftTranslator\Client($config);

echo "\n" . $msTranslator2->translate('hello', 'de')->getBody() . "\n\n";
