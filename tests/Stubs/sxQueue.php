<?php

class sxQueue extends xPDOSimpleObject
{
    /** @var bool */
    public $saveResult = true;

    /**
     * @param null $cacheFlag
     *
     * @return bool
     */
    public function save($cacheFlag = null)
    {
        if (!$this->saveResult) {
            return false;
        }

        if ($this->xpdo instanceof FakeModX) {
            $this->xpdo->queues[] = $this;
        }

        return true;
    }
}
