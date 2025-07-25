<?php

declare (strict_types=1);
/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper01187d35592a\Monolog\Handler\Curl;

use CurlHandle;
/**
 * This class is marked as internal and it is not under the BC promise of the package.
 *
 * @internal
 */
final class Util
{
    /** @var array<int> */
    private static $retriableErrorCodes = [\CURLE_COULDNT_RESOLVE_HOST, \CURLE_COULDNT_CONNECT, \CURLE_HTTP_NOT_FOUND, \CURLE_READ_ERROR, \CURLE_OPERATION_TIMEOUTED, \CURLE_HTTP_POST_ERROR, \CURLE_SSL_CONNECT_ERROR];
    /**
     * Executes a CURL request with optional retries and exception on failure
     *
     * @param  resource|CurlHandle $ch             curl handler
     * @param  int                 $retries
     * @param  bool                $closeAfterDone
     * @return bool|string         @see curl_exec
     */
    public static function execute($ch, int $retries = 5, bool $closeAfterDone = \true)
    {
        while ($retries--) {
            $curlResponse = \curl_exec($ch);
            if ($curlResponse === \false) {
                $curlErrno = \curl_errno($ch);
                if (\false === \in_array($curlErrno, self::$retriableErrorCodes, \true) || !$retries) {
                    $curlError = \curl_error($ch);
                    if ($closeAfterDone) {
                        \curl_close($ch);
                    }
                    throw new \RuntimeException(\sprintf('Curl error (code %d): %s', $curlErrno, $curlError));
                }
                continue;
            }
            if ($closeAfterDone) {
                \curl_close($ch);
            }
            return $curlResponse;
        }
        return \false;
    }
}
