<?php

class FakeModxServices
{
    /** @var array<string,mixed> */
    private $services;

    /**
     * @param array<string,mixed> $services
     */
    public function __construct(array $services)
    {
        $this->services = $services;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return array_key_exists($name, $this->services);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function get($name)
    {
        return $this->services[$name];
    }
}
