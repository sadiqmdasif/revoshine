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
use _PhpScoper01187d35592a\Monolog\Utils;
use _PhpScoper01187d35592a\Monolog\Formatter\FormatterInterface;
use _PhpScoper01187d35592a\Monolog\Formatter\LineFormatter;
use _PhpScoper01187d35592a\Symfony\Component\Mailer\MailerInterface;
use _PhpScoper01187d35592a\Symfony\Component\Mailer\Transport\TransportInterface;
use _PhpScoper01187d35592a\Symfony\Component\Mime\Email;
/**
 * SymfonyMailerHandler uses Symfony's Mailer component to send the emails
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 *
 * @phpstan-import-type Record from \Monolog\Logger
 */
class SymfonyMailerHandler extends MailHandler
{
    /** @var MailerInterface|TransportInterface */
    protected $mailer;
    /** @var Email|callable(string, Record[]): Email */
    private $emailTemplate;
    /**
     * @psalm-param Email|callable(string, Record[]): Email $email
     *
     * @param MailerInterface|TransportInterface $mailer The mailer to use
     * @param callable|Email                     $email  An email template, the subject/body will be replaced
     */
    public function __construct($mailer, $email, $level = Logger::ERROR, bool $bubble = \true)
    {
        parent::__construct($level, $bubble);
        $this->mailer = $mailer;
        $this->emailTemplate = $email;
    }
    /**
     * {@inheritDoc}
     */
    protected function send(string $content, array $records) : void
    {
        $this->mailer->send($this->buildMessage($content, $records));
    }
    /**
     * Gets the formatter for the Swift_Message subject.
     *
     * @param string|null $format The format of the subject
     */
    protected function getSubjectFormatter(?string $format) : FormatterInterface
    {
        return new LineFormatter($format);
    }
    /**
     * Creates instance of Email to be sent
     *
     * @param  string        $content formatted email body to be sent
     * @param  array         $records Log records that formed the content
     *
     * @phpstan-param Record[] $records
     */
    protected function buildMessage(string $content, array $records) : Email
    {
        $message = null;
        if ($this->emailTemplate instanceof Email) {
            $message = clone $this->emailTemplate;
        } elseif (\is_callable($this->emailTemplate)) {
            $message = ($this->emailTemplate)($content, $records);
        }
        if (!$message instanceof Email) {
            $record = \reset($records);
            throw new \InvalidArgumentException('Could not resolve message as instance of Email or a callable returning it' . ($record ? Utils::getRecordMessageForException($record) : ''));
        }
        if ($records) {
            $subjectFormatter = $this->getSubjectFormatter($message->getSubject());
            $message->subject($subjectFormatter->format($this->getHighestRecord($records)));
        }
        if ($this->isHtmlBody($content)) {
            if (null !== ($charset = $message->getHtmlCharset())) {
                $message->html($content, $charset);
            } else {
                $message->html($content);
            }
        } else {
            if (null !== ($charset = $message->getTextCharset())) {
                $message->text($content, $charset);
            } else {
                $message->text($content);
            }
        }
        return $message->date(new \DateTimeImmutable());
    }
}
