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
namespace _PhpScoper01187d35592a\Monolog\Handler;

use _PhpScoper01187d35592a\Monolog\Logger;
use _PhpScoper01187d35592a\Monolog\Formatter\FormatterInterface;
use _PhpScoper01187d35592a\Monolog\Formatter\LogglyFormatter;
use function array_key_exists;
use CurlHandle;
/**
 * Sends errors to Loggly.
 *
 * @author Przemek Sobstel <przemek@sobstel.org>
 * @author Adam Pancutt <adam@pancutt.com>
 * @author Gregory Barchard <gregory@barchard.net>
 */
class LogglyHandler extends AbstractProcessingHandler
{
    protected const HOST = 'logs-01.loggly.com';
    protected const ENDPOINT_SINGLE = 'inputs';
    protected const ENDPOINT_BATCH = 'bulk';
    /**
     * Caches the curl handlers for every given endpoint.
     *
     * @var resource[]|CurlHandle[]
     */
    protected $curlHandlers = [];
    /** @var string */
    protected $token;
    /** @var string[] */
    protected $tag = [];
    /**
     * @param string $token API token supplied by Loggly
     *
     * @throws MissingExtensionException If the curl extension is missing
     */
    public function __construct(string $token, $level = Logger::DEBUG, bool $bubble = \true)
    {
        if (!\extension_loaded('curl')) {
            throw new MissingExtensionException('The curl extension is needed to use the LogglyHandler');
        }
        $this->token = $token;
        parent::__construct($level, $bubble);
    }
    /**
     * Loads and returns the shared curl handler for the given endpoint.
     *
     * @param string $endpoint
     *
     * @return resource|CurlHandle
     */
    protected function getCurlHandler(string $endpoint)
    {
        if (!array_key_exists($endpoint, $this->curlHandlers)) {
            $this->curlHandlers[$endpoint] = $this->loadCurlHandle($endpoint);
        }
        return $this->curlHandlers[$endpoint];
    }
    /**
     * Starts a fresh curl session for the given endpoint and returns its handler.
     *
     * @param string $endpoint
     *
     * @return resource|CurlHandle
     */
    private function loadCurlHandle(string $endpoint)
    {
        $url = \sprintf("https://%s/%s/%s/", static::HOST, $endpoint, $this->token);
        $ch = \curl_init();
        \curl_setopt($ch, \CURLOPT_URL, $url);
        \curl_setopt($ch, \CURLOPT_POST, \true);
        \curl_setopt($ch, \CURLOPT_RETURNTRANSFER, \true);
        return $ch;
    }
    /**
     * @param string[]|string $tag
     */
    public function setTag($tag) : self
    {
        $tag = !empty($tag) ? $tag : [];
        $this->tag = \is_array($tag) ? $tag : [$tag];
        return $this;
    }
    /**
     * @param string[]|string $tag
     */
    public function addTag($tag) : self
    {
        if (!empty($tag)) {
            $tag = \is_array($tag) ? $tag : [$tag];
            $this->tag = \array_unique(\array_merge($this->tag, $tag));
        }
        return $this;
    }
    protected function write(array $record) : void
    {
        $this->send($record["formatted"], static::ENDPOINT_SINGLE);
    }
    public function handleBatch(array $records) : void
    {
        $level = $this->level;
        $records = \array_filter($records, function ($record) use($level) {
            return $record['level'] >= $level;
        });
        if ($records) {
            $this->send($this->getFormatter()->formatBatch($records), static::ENDPOINT_BATCH);
        }
    }
    protected function send(string $data, string $endpoint) : void
    {
        $ch = $this->getCurlHandler($endpoint);
        $headers = ['Content-Type: application/json'];
        if (!empty($this->tag)) {
            $headers[] = 'X-LOGGLY-TAG: ' . \implode(',', $this->tag);
        }
        \curl_setopt($ch, \CURLOPT_POSTFIELDS, $data);
        \curl_setopt($ch, \CURLOPT_HTTPHEADER, $headers);
        Curl\Util::execute($ch, 5, \false);
    }
    protected function getDefaultFormatter() : FormatterInterface
    {
        return new LogglyFormatter();
    }
}
