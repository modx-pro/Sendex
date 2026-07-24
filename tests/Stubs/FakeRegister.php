<?php

class FakeRegister
{
    /** @var FakeModX */
    public $modx;

    /** @var array<string,array> */
    public $entries = array();

    public function __construct(FakeModX $modx)
    {
        $this->modx = $modx;
    }

    public function connect()
    {
        return true;
    }

    /**
     * @param string $path
     */
    public function subscribe($path)
    {
        $this->modx->lastRegisterPath = $path;
    }

    /**
     * @param string $path
     * @param array $payload
     * @param array $options
     */
    public function send($path, array $payload, array $options = array())
    {
        $this->modx->lastRegisterTtl = isset($options['ttl']) ? (int) $options['ttl'] : null;
        foreach ($payload as $hash => $entry) {
            $this->entries[$hash] = $entry;
            $this->modx->registryEntries[$hash] = $entry;
        }
    }

    /**
     * @param array $options
     *
     * @return array
     */
    public function read(array $options = array())
    {
        $path = $this->modx->lastRegisterPath;
        $prefix = '/sendex/subscribe/';
        if (strpos($path, $prefix) === 0) {
            $hash = substr($path, strlen($prefix));
            if ($hash !== '' && isset($this->modx->registryEntries[$hash])) {
                $entry = $this->modx->registryEntries[$hash];
                unset($this->modx->registryEntries[$hash]);

                return array($entry);
            }
        }

        return array();
    }
}
