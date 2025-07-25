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
use _PhpScoper01187d35592a\Psr\Log\LogLevel;
/**
 * Simple handler wrapper that deduplicates log records across multiple requests
 *
 * It also includes the BufferHandler functionality and will buffer
 * all messages until the end of the request or flush() is called.
 *
 * This works by storing all log records' messages above $deduplicationLevel
 * to the file specified by $deduplicationStore. When further logs come in at the end of the
 * request (or when flush() is called), all those above $deduplicationLevel are checked
 * against the existing stored logs. If they match and the timestamps in the stored log is
 * not older than $time seconds, the new log record is discarded. If no log record is new, the
 * whole data set is discarded.
 *
 * This is mainly useful in combination with Mail handlers or things like Slack or HipChat handlers
 * that send messages to people, to avoid spamming with the same message over and over in case of
 * a major component failure like a database server being down which makes all requests fail in the
 * same way.
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 *
 * @phpstan-import-type Record from \Monolog\Logger
 * @phpstan-import-type LevelName from \Monolog\Logger
 * @phpstan-import-type Level from \Monolog\Logger
 */
class DeduplicationHandler extends BufferHandler
{
    /**
     * @var string
     */
    protected $deduplicationStore;
    /**
     * @var Level
     */
    protected $deduplicationLevel;
    /**
     * @var int
     */
    protected $time;
    /**
     * @var bool
     */
    private $gc = \false;
    /**
     * @param HandlerInterface $handler            Handler.
     * @param string           $deduplicationStore The file/path where the deduplication log should be kept
     * @param string|int       $deduplicationLevel The minimum logging level for log records to be looked at for deduplication purposes
     * @param int              $time               The period (in seconds) during which duplicate entries should be suppressed after a given log is sent through
     * @param bool             $bubble             Whether the messages that are handled can bubble up the stack or not
     *
     * @phpstan-param Level|LevelName|LogLevel::* $deduplicationLevel
     */
    public function __construct(HandlerInterface $handler, ?string $deduplicationStore = null, $deduplicationLevel = Logger::ERROR, int $time = 60, bool $bubble = \true)
    {
        parent::__construct($handler, 0, Logger::DEBUG, $bubble, \false);
        $this->deduplicationStore = $deduplicationStore === null ? \sys_get_temp_dir() . '/monolog-dedup-' . \substr(\md5(__FILE__), 0, 20) . '.log' : $deduplicationStore;
        $this->deduplicationLevel = Logger::toMonologLevel($deduplicationLevel);
        $this->time = $time;
    }
    public function flush() : void
    {
        if ($this->bufferSize === 0) {
            return;
        }
        $passthru = null;
        foreach ($this->buffer as $record) {
            if ($record['level'] >= $this->deduplicationLevel) {
                $passthru = $passthru || !$this->isDuplicate($record);
                if ($passthru) {
                    $this->appendRecord($record);
                }
            }
        }
        // default of null is valid as well as if no record matches duplicationLevel we just pass through
        if ($passthru === \true || $passthru === null) {
            $this->handler->handleBatch($this->buffer);
        }
        $this->clear();
        if ($this->gc) {
            $this->collectLogs();
        }
    }
    /**
     * @phpstan-param Record $record
     */
    private function isDuplicate(array $record) : bool
    {
        if (!\file_exists($this->deduplicationStore)) {
            return \false;
        }
        $store = \file($this->deduplicationStore, \FILE_IGNORE_NEW_LINES | \FILE_SKIP_EMPTY_LINES);
        if (!\is_array($store)) {
            return \false;
        }
        $yesterday = \time() - 86400;
        $timestampValidity = $record['datetime']->getTimestamp() - $this->time;
        $expectedMessage = \preg_replace('{[\\r\\n].*}', '', $record['message']);
        for ($i = \count($store) - 1; $i >= 0; $i--) {
            list($timestamp, $level, $message) = \explode(':', $store[$i], 3);
            if ($level === $record['level_name'] && $message === $expectedMessage && $timestamp > $timestampValidity) {
                return \true;
            }
            if ($timestamp < $yesterday) {
                $this->gc = \true;
            }
        }
        return \false;
    }
    private function collectLogs() : void
    {
        if (!\file_exists($this->deduplicationStore)) {
            return;
        }
        $handle = \fopen($this->deduplicationStore, 'rw+');
        if (!$handle) {
            throw new \RuntimeException('Failed to open file for reading and writing: ' . $this->deduplicationStore);
        }
        \flock($handle, \LOCK_EX);
        $validLogs = [];
        $timestampValidity = \time() - $this->time;
        while (!\feof($handle)) {
            $log = \fgets($handle);
            if ($log && \substr($log, 0, 10) >= $timestampValidity) {
                $validLogs[] = $log;
            }
        }
        \ftruncate($handle, 0);
        \rewind($handle);
        foreach ($validLogs as $log) {
            \fwrite($handle, $log);
        }
        \flock($handle, \LOCK_UN);
        \fclose($handle);
        $this->gc = \false;
    }
    /**
     * @phpstan-param Record $record
     */
    private function appendRecord(array $record) : void
    {
        \file_put_contents($this->deduplicationStore, $record['datetime']->getTimestamp() . ':' . $record['level_name'] . ':' . \preg_replace('{[\\r\\n].*}', '', $record['message']) . "\n", \FILE_APPEND);
    }
}
