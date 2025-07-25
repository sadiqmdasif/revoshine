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
use _PhpScoper01187d35592a\Monolog\Formatter\LineFormatter;
/**
 * NativeMailerHandler uses the mail() function to send the emails
 *
 * @author Christophe Coevoet <stof@notk.org>
 * @author Mark Garrett <mark@moderndeveloperllc.com>
 */
class NativeMailerHandler extends MailHandler
{
    /**
     * The email addresses to which the message will be sent
     * @var string[]
     */
    protected $to;
    /**
     * The subject of the email
     * @var string
     */
    protected $subject;
    /**
     * Optional headers for the message
     * @var string[]
     */
    protected $headers = [];
    /**
     * Optional parameters for the message
     * @var string[]
     */
    protected $parameters = [];
    /**
     * The wordwrap length for the message
     * @var int
     */
    protected $maxColumnWidth;
    /**
     * The Content-type for the message
     * @var string|null
     */
    protected $contentType;
    /**
     * The encoding for the message
     * @var string
     */
    protected $encoding = 'utf-8';
    /**
     * @param string|string[] $to             The receiver of the mail
     * @param string          $subject        The subject of the mail
     * @param string          $from           The sender of the mail
     * @param int             $maxColumnWidth The maximum column width that the message lines will have
     */
    public function __construct($to, string $subject, string $from, $level = Logger::ERROR, bool $bubble = \true, int $maxColumnWidth = 70)
    {
        parent::__construct($level, $bubble);
        $this->to = (array) $to;
        $this->subject = $subject;
        $this->addHeader(\sprintf('From: %s', $from));
        $this->maxColumnWidth = $maxColumnWidth;
    }
    /**
     * Add headers to the message
     *
     * @param string|string[] $headers Custom added headers
     */
    public function addHeader($headers) : self
    {
        foreach ((array) $headers as $header) {
            if (\strpos($header, "\n") !== \false || \strpos($header, "\r") !== \false) {
                throw new \InvalidArgumentException('Headers can not contain newline characters for security reasons');
            }
            $this->headers[] = $header;
        }
        return $this;
    }
    /**
     * Add parameters to the message
     *
     * @param string|string[] $parameters Custom added parameters
     */
    public function addParameter($parameters) : self
    {
        $this->parameters = \array_merge($this->parameters, (array) $parameters);
        return $this;
    }
    /**
     * {@inheritDoc}
     */
    protected function send(string $content, array $records) : void
    {
        $contentType = $this->getContentType() ?: ($this->isHtmlBody($content) ? 'text/html' : 'text/plain');
        if ($contentType !== 'text/html') {
            $content = \wordwrap($content, $this->maxColumnWidth);
        }
        $headers = \ltrim(\implode("\r\n", $this->headers) . "\r\n", "\r\n");
        $headers .= 'Content-type: ' . $contentType . '; charset=' . $this->getEncoding() . "\r\n";
        if ($contentType === 'text/html' && \false === \strpos($headers, 'MIME-Version:')) {
            $headers .= 'MIME-Version: 1.0' . "\r\n";
        }
        $subject = $this->subject;
        if ($records) {
            $subjectFormatter = new LineFormatter($this->subject);
            $subject = $subjectFormatter->format($this->getHighestRecord($records));
        }
        $parameters = \implode(' ', $this->parameters);
        foreach ($this->to as $to) {
            \mail($to, $subject, $content, $headers, $parameters);
        }
    }
    public function getContentType() : ?string
    {
        return $this->contentType;
    }
    public function getEncoding() : string
    {
        return $this->encoding;
    }
    /**
     * @param string $contentType The content type of the email - Defaults to text/plain. Use text/html for HTML messages.
     */
    public function setContentType(string $contentType) : self
    {
        if (\strpos($contentType, "\n") !== \false || \strpos($contentType, "\r") !== \false) {
            throw new \InvalidArgumentException('The content type can not contain newline characters to prevent email header injection');
        }
        $this->contentType = $contentType;
        return $this;
    }
    public function setEncoding(string $encoding) : self
    {
        if (\strpos($encoding, "\n") !== \false || \strpos($encoding, "\r") !== \false) {
            throw new \InvalidArgumentException('The encoding can not contain newline characters to prevent email header injection');
        }
        $this->encoding = $encoding;
        return $this;
    }
}
