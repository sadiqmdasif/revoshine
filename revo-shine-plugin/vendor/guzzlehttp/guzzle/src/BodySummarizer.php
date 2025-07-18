<?php

namespace _PhpScoper01187d35592a\GuzzleHttp;

use _PhpScoper01187d35592a\Psr\Http\Message\MessageInterface;
final class BodySummarizer implements BodySummarizerInterface
{
    /**
     * @var int|null
     */
    private $truncateAt;
    public function __construct(int $truncateAt = null)
    {
        $this->truncateAt = $truncateAt;
    }
    /**
     * Returns a summarized message body.
     */
    public function summarize(MessageInterface $message) : ?string
    {
        return $this->truncateAt === null ? \_PhpScoper01187d35592a\GuzzleHttp\Psr7\Message::bodySummary($message) : \_PhpScoper01187d35592a\GuzzleHttp\Psr7\Message::bodySummary($message, $this->truncateAt);
    }
}
