<?php

class FakeMail
{
    /** @var bool */
    public $sendResult = true;

    /** @var string */
    public $errorInfo = 'SMTP down';

    /** @var stdClass */
    public $mailer;

    /** @var array<string,mixed> */
    public $sets = array();

    /** @var array<string,string> */
    public $addresses = array();

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
        $this->sets[$key] = $value;
    }

    /**
     * @param string $type
     * @param string $address
     */
    public function address($type, $address)
    {
        $this->addresses[$type] = $address;
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
