<?php

declare (strict_types=1);
namespace _PhpScoper01187d35592a\GuzzleHttp\Promise;

/**
 * Interface used with classes that return a promise.
 */
interface PromisorInterface
{
    /**
     * Returns a promise.
     */
    public function promise() : PromiseInterface;
}
