<?php

class FakeMail
{
    /** @var bool */
    public $sendResult = true;

    /** @var string */
    public $errorInfo = 'SMTP down';

    /** @var stdClass */
    public $mailer;

    public function __construct()
    {
        $this->mailer = new stdClass();
        $this->mailer->ErrorInfo = $this->errorInfo;
    }

    /**
     * @param mixed $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
    }

    /**
     * @param string $type
     * @param string $address
     */
    public function address($type, $address)
    {
    }

    /**
     * @param bool $html
     */
    public function setHTML($html)
    {
    }

    /**
     * @return bool
     */
    public function send()
    {
        return $this->sendResult;
    }

    public function reset()
    {
    }
}
