<?php

declare (strict_types=1);
namespace _PhpScoper01187d35592a\GuzzleHttp\Psr7;

use _PhpScoper01187d35592a\Psr\Http\Message\StreamInterface;
/**
 * Uses PHP's zlib.inflate filter to inflate zlib (HTTP deflate, RFC1950) or gzipped (RFC1952) content.
 *
 * This stream decorator converts the provided stream to a PHP stream resource,
 * then appends the zlib.inflate filter. The stream is then converted back
 * to a Guzzle stream resource to be used as a Guzzle stream.
 *
 * @see http://tools.ietf.org/html/rfc1950
 * @see http://tools.ietf.org/html/rfc1952
 * @see http://php.net/manual/en/filters.compression.php
 */
final class InflateStream implements StreamInterface
{
    use StreamDecoratorTrait;
    /** @var StreamInterface */
    private $stream;
    public function __construct(StreamInterface $stream)
    {
        $resource = StreamWrapper::getResource($stream);
        // Specify window=15+32, so zlib will use header detection to both gzip (with header) and zlib data
        // See http://www.zlib.net/manual.html#Advanced definition of inflateInit2
        // "Add 32 to windowBits to enable zlib and gzip decoding with automatic header detection"
        // Default window size is 15.
        \stream_filter_append($resource, 'zlib.inflate', \STREAM_FILTER_READ, ['window' => 15 + 32]);
        $this->stream = $stream->isSeekable() ? new Stream($resource) : new NoSeekStream(new Stream($resource));
    }
}
