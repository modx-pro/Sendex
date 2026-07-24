<?php

class FakeRegistry
{
    /** @var FakeModX */
    public $modx;

    public function __construct(FakeModX $modx)
    {
        $this->modx = $modx;
    }

    /**
     * @param string $name
     * @param string $class
     *
     * @return FakeRegister
     */
    public function getRegister($name, $class)
    {
        return new FakeRegister($this->modx);
    }
}
