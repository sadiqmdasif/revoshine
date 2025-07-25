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
 * Used for testing purposes.
 *
 * It records all records and gives you access to them for verification.
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 *
 * @method bool hasEmergency($record)
 * @method bool hasAlert($record)
 * @method bool hasCritical($record)
 * @method bool hasError($record)
 * @method bool hasWarning($record)
 * @method bool hasNotice($record)
 * @method bool hasInfo($record)
 * @method bool hasDebug($record)
 *
 * @method bool hasEmergencyRecords()
 * @method bool hasAlertRecords()
 * @method bool hasCriticalRecords()
 * @method bool hasErrorRecords()
 * @method bool hasWarningRecords()
 * @method bool hasNoticeRecords()
 * @method bool hasInfoRecords()
 * @method bool hasDebugRecords()
 *
 * @method bool hasEmergencyThatContains($message)
 * @method bool hasAlertThatContains($message)
 * @method bool hasCriticalThatContains($message)
 * @method bool hasErrorThatContains($message)
 * @method bool hasWarningThatContains($message)
 * @method bool hasNoticeThatContains($message)
 * @method bool hasInfoThatContains($message)
 * @method bool hasDebugThatContains($message)
 *
 * @method bool hasEmergencyThatMatches($message)
 * @method bool hasAlertThatMatches($message)
 * @method bool hasCriticalThatMatches($message)
 * @method bool hasErrorThatMatches($message)
 * @method bool hasWarningThatMatches($message)
 * @method bool hasNoticeThatMatches($message)
 * @method bool hasInfoThatMatches($message)
 * @method bool hasDebugThatMatches($message)
 *
 * @method bool hasEmergencyThatPasses($message)
 * @method bool hasAlertThatPasses($message)
 * @method bool hasCriticalThatPasses($message)
 * @method bool hasErrorThatPasses($message)
 * @method bool hasWarningThatPasses($message)
 * @method bool hasNoticeThatPasses($message)
 * @method bool hasInfoThatPasses($message)
 * @method bool hasDebugThatPasses($message)
 *
 * @phpstan-import-type Record from \Monolog\Logger
 * @phpstan-import-type Level from \Monolog\Logger
 * @phpstan-import-type LevelName from \Monolog\Logger
 */
class TestHandler extends AbstractProcessingHandler
{
    /** @var Record[] */
    protected $records = [];
    /** @var array<Level, Record[]> */
    protected $recordsByLevel = [];
    /** @var bool */
    private $skipReset = \false;
    /**
     * @return array
     *
     * @phpstan-return Record[]
     */
    public function getRecords()
    {
        return $this->records;
    }
    /**
     * @return void
     */
    public function clear()
    {
        $this->records = [];
        $this->recordsByLevel = [];
    }
    /**
     * @return void
     */
    public function reset()
    {
        if (!$this->skipReset) {
            $this->clear();
        }
    }
    /**
     * @return void
     */
    public function setSkipReset(bool $skipReset)
    {
        $this->skipReset = $skipReset;
    }
    /**
     * @param string|int $level Logging level value or name
     *
     * @phpstan-param Level|LevelName|LogLevel::* $level
     */
    public function hasRecords($level) : bool
    {
        return isset($this->recordsByLevel[Logger::toMonologLevel($level)]);
    }
    /**
     * @param string|array $record Either a message string or an array containing message and optionally context keys that will be checked against all records
     * @param string|int   $level  Logging level value or name
     *
     * @phpstan-param array{message: string, context?: mixed[]}|string $record
     * @phpstan-param Level|LevelName|LogLevel::*                      $level
     */
    public function hasRecord($record, $level) : bool
    {
        if (\is_string($record)) {
            $record = array('message' => $record);
        }
        return $this->hasRecordThatPasses(function ($rec) use($record) {
            if ($rec['message'] !== $record['message']) {
                return \false;
            }
            if (isset($record['context']) && $rec['context'] !== $record['context']) {
                return \false;
            }
            return \true;
        }, $level);
    }
    /**
     * @param string|int $level Logging level value or name
     *
     * @phpstan-param Level|LevelName|LogLevel::* $level
     */
    public function hasRecordThatContains(string $message, $level) : bool
    {
        return $this->hasRecordThatPasses(function ($rec) use($message) {
            return \strpos($rec['message'], $message) !== \false;
        }, $level);
    }
    /**
     * @param string|int $level Logging level value or name
     *
     * @phpstan-param Level|LevelName|LogLevel::* $level
     */
    public function hasRecordThatMatches(string $regex, $level) : bool
    {
        return $this->hasRecordThatPasses(function (array $rec) use($regex) : bool {
            return \preg_match($regex, $rec['message']) > 0;
        }, $level);
    }
    /**
     * @param  string|int $level Logging level value or name
     * @return bool
     *
     * @psalm-param callable(Record, int): mixed $predicate
     * @phpstan-param Level|LevelName|LogLevel::* $level
     */
    public function hasRecordThatPasses(callable $predicate, $level)
    {
        $level = Logger::toMonologLevel($level);
        if (!isset($this->recordsByLevel[$level])) {
            return \false;
        }
        foreach ($this->recordsByLevel[$level] as $i => $rec) {
            if ($predicate($rec, $i)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * {@inheritDoc}
     */
    protected function write(array $record) : void
    {
        $this->recordsByLevel[$record['level']][] = $record;
        $this->records[] = $record;
    }
    /**
     * @param  string  $method
     * @param  mixed[] $args
     * @return bool
     */
    public function __call($method, $args)
    {
        if (\preg_match('/(.*)(Debug|Info|Notice|Warning|Error|Critical|Alert|Emergency)(.*)/', $method, $matches) > 0) {
            $genericMethod = $matches[1] . ('Records' !== $matches[3] ? 'Record' : '') . $matches[3];
            $level = \constant('Monolog\\Logger::' . \strtoupper($matches[2]));
            $callback = [$this, $genericMethod];
            if (\is_callable($callback)) {
                $args[] = $level;
                return \call_user_func_array($callback, $args);
            }
        }
        throw new \BadMethodCallException('Call to undefined method ' . \get_class($this) . '::' . $method . '()');
    }
}
