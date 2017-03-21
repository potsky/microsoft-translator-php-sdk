#### Existing Azure subscriptions will be retired and cancelled starting 3/31/2017. New microsoft Azure accounts need a credit card so this extension will not be maintained, sorry for this.

---

# PHP SDK for Microsoft Translator

[![Latest Stable Version](https://poser.pugx.org/potsky/microsoft-translator-php-sdk/v/stable.svg)](https://packagist.org/packages/potsky/microsoft-translator-php-sdk)
[![Total Downloads](https://poser.pugx.org/potsky/microsoft-translator-php-sdk/downloads.svg)](https://packagist.org/packages/potsky/microsoft-translator-php-sdk)
[![Build Status](https://travis-ci.org/potsky/microsoft-translator-php-sdk.svg)](https://travis-ci.org/potsky/microsoft-translator-php-sdk)
[![Coverage Status](https://coveralls.io/repos/potsky/microsoft-translator-php-sdk/badge.svg?branch=master&service=github)](https://coveralls.io/github/potsky/microsoft-translator-php-sdk?branch=master)

## Table of content

1. [Installation](#user-content-1-installation)
1. [Configuration](#user-content-2-configuration)
1. [Usage](#user-content-3-usage)
1. [Change Log](#user-content-4-change-log)
1. [Contribute](#user-content-5-contribute)

## 1. Installation

### Requirements

The SDK needs the [cURL PHP extension](http://php.net/manual/fr/book.curl.php). 

### With composer

Install the latest stable version using composer: `composer require potsky/microsoft-translator-php-sdk`

Or add `potsky/microsoft-translator-php-sdk` to your `composer.json` :

```json
{
    "require": {
        "potsky/microsoft-translator-php-sdk": "*"
    }
}
```

### Manually

Is it time to move to composer?

Not now? Ok, just include `src/MicrosoftTranslator.php` in your PHP script :

```php
include_once( 'src/MicrosoftTranslator.php' );
```

## 2. Configuration

### 2.1 Microsoft Account

You need to create an account on Microsoft Translation service : <https://datamarket.azure.com/dataset/bing/microsofttranslator>.

Then you need to create an application to get a `client_id` and a `client_secret` : <https://datamarket.azure.com/developer/applications>

### 2.2 SDK

When instantiating a new client, you pass an array of configuration parameters: 

```php
$msTranslator = new MicrosoftTranslator\Client( $configuration );
```

Here is the list of available parameters, some are mandatory:

|Name|Default|Description|
|----|----|----|
|`api_client_id`|**mandatory**|Your OAuth client ID|
|`api_client_secret`|**mandatory**|Your OAuth client secret|
|`api_client_scope`|`http://api.microsofttranslator.com`| |
|`api_access_token`|-|You can directly give an access token. In this case `api_client_id` and `api_client_secret` will not be used|
|`api_base_url`|`http://api.microsofttranslator.com/V2/Http.svc/`| |
|`auth_base_url`|`https://datamarket.accesscontrol.windows.net/v2/OAuth2-13/`| |
|`guard_type`|`file`|The only available type is `file` but you can specify your own Guard by setting the classname like this `YourNameSpace\\YourGuardWhichImplementsTheMicrosoftTranslatorGuardInterface`|
|`guard_file_dir_path`|The default PHP tmp directory|You can specify a custom directory. **IT MUST NOT BE EXPOSED TO INTERNET** given that clear access token will be stored in files...|
|`log_level`|No log|To enable log, choose the maximum severity you want to log in this list : `\MicrosoftTranslator\Logger::LEVEL_DEBUG`, `\MicrosoftTranslator\Logger::LEVEL_INFO`, `\MicrosoftTranslator\Logger::LEVEL_WARNING`, `\MicrosoftTranslator\Logger::LEVEL_ERROR` and `\MicrosoftTranslator\Logger::LEVEL_FATAL`|
|`log_file_path`|No file path, output with error_log() function|Set a file path to log in this file|
|`http_timeout`|`10`|The timeout in seconds for requests|
|`http_proxy_host`|-|The proxy host if you need a proxy for outgoing connections to the API|
|`http_proxy_type`|`URLPROXY_HTTP`|One of these values: `URLPROXY_HTTP`, `CURLPROXY_SOCKS4`, `CURLPROXY_SOCKS5`|
|`http_proxy_auth`|`CURLAUTH_BASIC`|One if these values: `CURLAUTH_BASIC`, `CURLAUTH_NTLM`|
|`http_proxy_port`|`3128`|The proxy port|
|`http_proxy_user`|-|The proxy user name if your proxy needs authentication|
|`http_proxy_pass`|-|The proxy user password if your proxy needs authentication|
|`http_user_agent`|`MicrosoftTranslator PHP SDK v%VERSION%`|You can use your own user agent and you can use the placeholder `%VERSION%` to inject the current SDK version|

## 3. Usage

### 3.1 Methods

Define a client with your credentials:

```php
<?php
include 'src/MicrosoftTranslator.php';

$msTranslator = new \MicrosoftTranslator\Client( array(
	'log_level'         => \MicrosoftTranslator\Logger::LEVEL_DEBUG ,
	'api_client_id'     => 'your-client-id' ,
	'api_client_secret' => 'your-client-secret' ,
) );
```

#### Translate a text

Translate word `chair` in german. The API will try to guess the input language:

```php
print_r( $msTranslator->translate( 'chair' , 'de' )->getBody() );
```

Result:

```php
Stuhl
```

Translate word `chair` in german by specifying the initial sentence is in french:

```php
print_r( $msTranslator->translate( 'chair' , 'de' , 'fr' )->getBody() );
```

Result:

```php
Fleisch
```

#### Translate an array of texts

```php
print_r( $msTranslator->translateArray( array( 'dog' , 'cat' ) , 'fr' )->getBody() );
```

Result:

```php
Array
(
    [dog] => chien
    [cat] => chat
)
```

You can specify an input language as usual.

#### Transform a text

Only english is supported.

```php
print_r( $msTranslator->TransformText( 'WTF I am not here!' , 'en' )->getBody() );
```

Result:

```php
Array
(
    [ec] => 0
    [em] => OK
    [sentence] => WHAT THE HECK I am not here!
)
```

#### Detect the language of a text

```php
print_r( $msTranslator->detect( 'The dog is red' )->getBody() );
```

Result:

```php
en
```

#### Detect the language of an array of texts

```php
print_r( $msTranslator->detectArray( array( 'The dog is red' , 'Le chien est rouge' ) )->getBody() );
```

Result:

```php
Array
(
    [The dog is red] => en
    [Le chien est rouge] => fr
)
```

#### Get language names

```php
print_r( $msTranslator->getLanguageNames( 'fr' , array( 'en' , 'jp' , 'fr' ) )->getBody() );
```

Result:

```php
Array
(
    [en] => Anglais
    [jp] =>
    [fr] => Français
)
```

#### Get available languages for translation

```php
print_r( $msTranslator->getLanguagesForTranslate()->getBody() );
```

Result:

```php
Array
(
    [0] => ar
    [1] => bs-Latn
    [2] => bg
    [3] => ca
    [4] => zh-CHS
    [5] => zh-CHT
    [6] => hr
    [7] => cs
    [8] => da
    [9] => nl
    ...
)
```

#### Break sentences

```php
print_r( $msTranslator->breakSentences( 'The dog is red. The cat is blue. The fish is yellow submarine.' , 'en' )->getBody() );
```

Result:

```php
Array
(
    [0] => The dog is red.
    [1] => The cat is blue.
    [2] => The fish is yellow submarine.
)
```

### 3.2 Handle errors

SDK can throw an `MicrosoftTranslator\Exception` in several cases :

- HTTP problems with cURL
- API problems...
- Wrong credentials
- ...

You should catch these errors like this:

```php
try
{
	...

}
catch ( \MicrosoftTranslator\Exception $e )
{
	$error = sprintf( "Error #%s with message : %s" , $e->getCode() , $e->getMessage() );
	$msg   = sprintf( "|    %s    |" , $error );
	$line  = str_repeat( '-' , strlen( $msg ) );
	echo sprintf( "%s\n%s\n%s\n" , $line , $msg , $line );
}
```

or like this by checking the returned code instead:

```php
try
{
	...
}
catch ( \MicrosoftTranslator\Exception $f )
{
	if ( $f->getCode() === 500 )
	{
		echo sprintf( "Oups, error #%s : %s." , $f->getCode() , $f->getMessage() );
	}
	else
	{
		echo sprintf( "Error #%s : %s\n" , $f->getCode() , $f->getMessage() );
	}
}
```

### 3.3 Customize the SDK

You can use your own classes to implement several parts of the SDK. Your classes need to implement interfaces and they will get the configuration array in the constructors. You can then customize your classes at runtime.

#### 3.3.1 Inject a new Logger

You can use your own `Logger` class. The `Logger` class is used to log SDK messages.

It must implement the `\MicrosoftTranslator\LoggerInterface` interface.

Then use it when instantiating a client:

```php
$msTranslator = new \MicrosoftTranslator\Client( $config , null , null , $my_logger );
```

#### 3.3.2 Inject a new HTTP manager

You can use your own `Http` class. The `Http` class manages the HTTP connexions to reach the accumulator API.

It must implement the `\MicrosoftTranslator\HttpInterface` interface.

Then use it when instantiating a client:

```php
$msTranslator = new \MicrosoftTranslator\Client( $config , $my_http_manager );
```

#### 3.3.3 Inject a new Auth manager

You can use your own `Auth` class. The `Auth` class responsibility is to get an access token and to give it to the `Guard` manager.

It must implement the `\MicrosoftTranslator\AuthInterface` interface.

Then use it when instantiating a client:

```php
$msTranslator = new \MicrosoftTranslator\Client( $config , null , $my_auth_manager );
```

#### 3.3.4 Inject a new Guard manager

You can use your own `Guard` manager. The `Guard` class responsibility is to store access tokens and to manage expiration durations.

The `Guard` injection is a little bit different of others injections because it is done in the configuration array. This is a *setter* dependency injection instead of an *instantiation* dependency injection.
   
It must implement the `\MicrosoftTranslator\GuardInterface` interface.

Inject a new `Guard` manager via the configuration array:

```php
$msTranslator = new \MicrosoftTranslator\Client( array(
	'guard_type' => 'MyNameSpace\\MyGuard'
) );
```

### 3.4 Manage saved access tokens

The SDK maintains access tokens via a Guard object. The default Guard is a GuardFile which stores access tokens in files.

If you need to delete all saved access token, you can do this:

```php
$msTranslator->getAuth()->getGuard()->deleteAllAccessTokens();
```

To delete only expired access tokens, run this:

```php
$msTranslator->getAuth()->getGuard()->cleanAccessTokens();
```

## 4. Change Log

- `v0.0.2`
    -  The SDK now returns a `MicrosoftTranslator\Exception` when access token cannot be retrieved
- `v0.0.1`
    -  here is the beginning

## 5. Contribute

1. Fork it
2. Create your feature branch (`git checkout -b my-new-feature`)
3. Commit your changes (`git commit -am 'Added some feature'`)
4. Push to the branch (`git push origin my-new-feature`)
5. Create new Pull Request

Tests are in `tests`. To run the tests: `vendor/bin/phpunit`.

Coverage cannot decrease next a merge. To track file coverage, run `vendor/bin/phpunit --coverage-html coverage` and open `coverage/index.html` to check uncovered lines of code.

