<?php

namespace _PhpScoper01187d35592a\GuzzleHttp;

use _PhpScoper01187d35592a\Psr\Http\Message\MessageInterface;
interface BodySummarizerInterface
{
    /**
     * Returns a summarized message body.
     */
    public function summarize(MessageInterface $message) : ?string;
}
