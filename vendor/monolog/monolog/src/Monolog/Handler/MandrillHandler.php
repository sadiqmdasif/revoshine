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
use _PhpScoper01187d35592a\Swift;
use _PhpScoper01187d35592a\Swift_Message;
/**
 * MandrillHandler uses cURL to send the emails to the Mandrill API
 *
 * @author Adam Nicholson <adamnicholson10@gmail.com>
 */
class MandrillHandler extends MailHandler
{
    /** @var Swift_Message */
    protected $message;
    /** @var string */
    protected $apiKey;
    /**
     * @psalm-param Swift_Message|callable(): Swift_Message $message
     *
     * @param string                 $apiKey  A valid Mandrill API key
     * @param callable|Swift_Message $message An example message for real messages, only the body will be replaced
     */
    public function __construct(string $apiKey, $message, $level = Logger::ERROR, bool $bubble = \true)
    {
        parent::__construct($level, $bubble);
        if (!$message instanceof Swift_Message && \is_callable($message)) {
            $message = $message();
        }
        if (!$message instanceof Swift_Message) {
            throw new \InvalidArgumentException('You must provide either a Swift_Message instance or a callable returning it');
        }
        $this->message = $message;
        $this->apiKey = $apiKey;
    }
    /**
     * {@inheritDoc}
     */
    protected function send(string $content, array $records) : void
    {
        $mime = 'text/plain';
        if ($this->isHtmlBody($content)) {
            $mime = 'text/html';
        }
        $message = clone $this->message;
        $message->setBody($content, $mime);
        /** @phpstan-ignore-next-line */
        if (\version_compare(Swift::VERSION, '6.0.0', '>=')) {
            $message->setDate(new \DateTimeImmutable());
        } else {
            /** @phpstan-ignore-next-line */
            $message->setDate(\time());
        }
        $ch = \curl_init();
        \curl_setopt($ch, \CURLOPT_URL, 'https://mandrillapp.com/api/1.0/messages/send-raw.json');
        \curl_setopt($ch, \CURLOPT_POST, 1);
        \curl_setopt($ch, \CURLOPT_RETURNTRANSFER, 1);
        \curl_setopt($ch, \CURLOPT_POSTFIELDS, \http_build_query(['key' => $this->apiKey, 'raw_message' => (string) $message, 'async' => \false]));
        Curl\Util::execute($ch);
    }
}
