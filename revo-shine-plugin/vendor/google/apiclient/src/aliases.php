<?php

namespace _PhpScoper01187d35592a;

if (\class_exists('_PhpScoper01187d35592a\\Google_Client', \false)) {
    // Prevent error with preloading in PHP 7.4
    // @see https://github.com/googleapis/google-api-php-client/issues/1976
    return;
}
$classMap = ['_PhpScoper01187d35592a\\Google\\Client' => 'Google_Client', '_PhpScoper01187d35592a\\Google\\Service' => 'Google_Service', '_PhpScoper01187d35592a\\Google\\AccessToken\\Revoke' => 'Google_AccessToken_Revoke', '_PhpScoper01187d35592a\\Google\\AccessToken\\Verify' => 'Google_AccessToken_Verify', '_PhpScoper01187d35592a\\Google\\Model' => 'Google_Model', '_PhpScoper01187d35592a\\Google\\Utils\\UriTemplate' => 'Google_Utils_UriTemplate', '_PhpScoper01187d35592a\\Google\\AuthHandler\\Guzzle6AuthHandler' => 'Google_AuthHandler_Guzzle6AuthHandler', '_PhpScoper01187d35592a\\Google\\AuthHandler\\Guzzle7AuthHandler' => 'Google_AuthHandler_Guzzle7AuthHandler', '_PhpScoper01187d35592a\\Google\\AuthHandler\\AuthHandlerFactory' => 'Google_AuthHandler_AuthHandlerFactory', '_PhpScoper01187d35592a\\Google\\Http\\Batch' => 'Google_Http_Batch', '_PhpScoper01187d35592a\\Google\\Http\\MediaFileUpload' => 'Google_Http_MediaFileUpload', '_PhpScoper01187d35592a\\Google\\Http\\REST' => 'Google_Http_REST', '_PhpScoper01187d35592a\\Google\\Task\\Retryable' => 'Google_Task_Retryable', '_PhpScoper01187d35592a\\Google\\Task\\Exception' => 'Google_Task_Exception', '_PhpScoper01187d35592a\\Google\\Task\\Runner' => 'Google_Task_Runner', '_PhpScoper01187d35592a\\Google\\Collection' => 'Google_Collection', '_PhpScoper01187d35592a\\Google\\Service\\Exception' => 'Google_Service_Exception', '_PhpScoper01187d35592a\\Google\\Service\\Resource' => 'Google_Service_Resource', '_PhpScoper01187d35592a\\Google\\Exception' => 'Google_Exception'];
foreach ($classMap as $class => $alias) {
    \class_alias($class, $alias);
}
/**
 * This class needs to be defined explicitly as scripts must be recognized by
 * the autoloader.
 */
class Google_Task_Composer extends \_PhpScoper01187d35592a\Google\Task\Composer
{
}
/** @phpstan-ignore-next-line */
if (\false) {
    class Google_AccessToken_Revoke extends \_PhpScoper01187d35592a\Google\AccessToken\Revoke
    {
    }
    class Google_AccessToken_Verify extends \_PhpScoper01187d35592a\Google\AccessToken\Verify
    {
    }
    class Google_AuthHandler_AuthHandlerFactory extends \_PhpScoper01187d35592a\Google\AuthHandler\AuthHandlerFactory
    {
    }
    class Google_AuthHandler_Guzzle6AuthHandler extends \_PhpScoper01187d35592a\Google\AuthHandler\Guzzle6AuthHandler
    {
    }
    class Google_AuthHandler_Guzzle7AuthHandler extends \_PhpScoper01187d35592a\Google\AuthHandler\Guzzle7AuthHandler
    {
    }
    class Google_Client extends \_PhpScoper01187d35592a\Google\Client
    {
    }
    class Google_Collection extends \_PhpScoper01187d35592a\Google\Collection
    {
    }
    class Google_Exception extends \_PhpScoper01187d35592a\Google\Exception
    {
    }
    class Google_Http_Batch extends \_PhpScoper01187d35592a\Google\Http\Batch
    {
    }
    class Google_Http_MediaFileUpload extends \_PhpScoper01187d35592a\Google\Http\MediaFileUpload
    {
    }
    class Google_Http_REST extends \_PhpScoper01187d35592a\Google\Http\REST
    {
    }
    class Google_Model extends \_PhpScoper01187d35592a\Google\Model
    {
    }
    class Google_Service extends \_PhpScoper01187d35592a\Google\Service
    {
    }
    class Google_Service_Exception extends \_PhpScoper01187d35592a\Google\Service\Exception
    {
    }
    class Google_Service_Resource extends \_PhpScoper01187d35592a\Google\Service\Resource
    {
    }
    class Google_Task_Exception extends \_PhpScoper01187d35592a\Google\Task\Exception
    {
    }
    interface Google_Task_Retryable extends \_PhpScoper01187d35592a\Google\Task\Retryable
    {
    }
    class Google_Task_Runner extends \_PhpScoper01187d35592a\Google\Task\Runner
    {
    }
    class Google_Utils_UriTemplate extends \_PhpScoper01187d35592a\Google\Utils\UriTemplate
    {
    }
}
