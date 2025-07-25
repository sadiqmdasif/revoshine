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

use _PhpScoper01187d35592a\Monolog\Processor\ProcessorInterface;
/**
 * Interface to describe loggers that have processors
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 *
 * @phpstan-import-type Record from \Monolog\Logger
 */
interface ProcessableHandlerInterface
{
    /**
     * Adds a processor in the stack.
     *
     * @psalm-param ProcessorInterface|callable(Record): Record $callback
     *
     * @param  ProcessorInterface|callable $callback
     * @return HandlerInterface            self
     */
    public function pushProcessor(callable $callback) : HandlerInterface;
    /**
     * Removes the processor on top of the stack and returns it.
     *
     * @psalm-return ProcessorInterface|callable(Record): Record $callback
     *
     * @throws \LogicException             In case the processor stack is empty
     * @return callable|ProcessorInterface
     */
    public function popProcessor() : callable;
}
