<?php

namespace _PhpScoper01187d35592a\Firebase\JWT;

class BeforeValidException extends \UnexpectedValueException implements JWTExceptionWithPayloadInterface
{
    private object $payload;
    public function setPayload(object $payload) : void
    {
        $this->payload = $payload;
    }
    public function getPayload() : object
    {
        return $this->payload;
    }
}
