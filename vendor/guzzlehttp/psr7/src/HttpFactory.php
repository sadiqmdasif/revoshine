<?php

declare (strict_types=1);
namespace _PhpScoper01187d35592a\GuzzleHttp\Psr7;

use _PhpScoper01187d35592a\Psr\Http\Message\RequestFactoryInterface;
use _PhpScoper01187d35592a\Psr\Http\Message\RequestInterface;
use _PhpScoper01187d35592a\Psr\Http\Message\ResponseFactoryInterface;
use _PhpScoper01187d35592a\Psr\Http\Message\ResponseInterface;
use _PhpScoper01187d35592a\Psr\Http\Message\ServerRequestFactoryInterface;
use _PhpScoper01187d35592a\Psr\Http\Message\ServerRequestInterface;
use _PhpScoper01187d35592a\Psr\Http\Message\StreamFactoryInterface;
use _PhpScoper01187d35592a\Psr\Http\Message\StreamInterface;
use _PhpScoper01187d35592a\Psr\Http\Message\UploadedFileFactoryInterface;
use _PhpScoper01187d35592a\Psr\Http\Message\UploadedFileInterface;
use _PhpScoper01187d35592a\Psr\Http\Message\UriFactoryInterface;
use _PhpScoper01187d35592a\Psr\Http\Message\UriInterface;
/**
 * Implements all of the PSR-17 interfaces.
 *
 * Note: in consuming code it is recommended to require the implemented interfaces
 * and inject the instance of this class multiple times.
 */
final class HttpFactory implements RequestFactoryInterface, ResponseFactoryInterface, ServerRequestFactoryInterface, StreamFactoryInterface, UploadedFileFactoryInterface, UriFactoryInterface
{
    public function createUploadedFile(StreamInterface $stream, int $size = null, int $error = \UPLOAD_ERR_OK, string $clientFilename = null, string $clientMediaType = null) : UploadedFileInterface
    {
        if ($size === null) {
            $size = $stream->getSize();
        }
        return new UploadedFile($stream, $size, $error, $clientFilename, $clientMediaType);
    }
    public function createStream(string $content = '') : StreamInterface
    {
        return Utils::streamFor($content);
    }
    public function createStreamFromFile(string $file, string $mode = 'r') : StreamInterface
    {
        try {
            $resource = Utils::tryFopen($file, $mode);
        } catch (\RuntimeException $e) {
            if ('' === $mode || \false === \in_array($mode[0], ['r', 'w', 'a', 'x', 'c'], \true)) {
                throw new \InvalidArgumentException(\sprintf('Invalid file opening mode "%s"', $mode), 0, $e);
            }
            throw $e;
        }
        return Utils::streamFor($resource);
    }
    public function createStreamFromResource($resource) : StreamInterface
    {
        return Utils::streamFor($resource);
    }
    public function createServerRequest(string $method, $uri, array $serverParams = []) : ServerRequestInterface
    {
        if (empty($method)) {
            if (!empty($serverParams['REQUEST_METHOD'])) {
                $method = $serverParams['REQUEST_METHOD'];
            } else {
                throw new \InvalidArgumentException('Cannot determine HTTP method');
            }
        }
        return new ServerRequest($method, $uri, [], null, '1.1', $serverParams);
    }
    public function createResponse(int $code = 200, string $reasonPhrase = '') : ResponseInterface
    {
        return new Response($code, [], null, '1.1', $reasonPhrase);
    }
    public function createRequest(string $method, $uri) : RequestInterface
    {
        return new Request($method, $uri);
    }
    public function createUri(string $uri = '') : UriInterface
    {
        return new Uri($uri);
    }
}
