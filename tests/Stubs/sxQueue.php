<?php

class sxQueue extends xPDOSimpleObject
{
    /** @var bool */
    public $saveResult = true;

    /** @var bool */
    public $removeResult = true;

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
            if (!$this->get('id')) {
                $max = 0;
                foreach ($this->xpdo->queues as $queue) {
                    $max = max($max, (int) $queue->get('id'));
                }
                $this->set('id', $max + 1);
            }
            $found = false;
            foreach ($this->xpdo->queues as $index => $queue) {
                if ((int) $queue->get('id') === (int) $this->get('id')) {
                    $this->xpdo->queues[$index] = $this;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $this->xpdo->queues[] = $this;
            }
        }

        return true;
    }

    /**
     * @param array $ancestors
     *
     * @return bool
     */
    public function remove(array $ancestors = array())
    {
        if (!$this->removeResult) {
            return false;
        }

        if ($this->xpdo instanceof FakeModX) {
            foreach ($this->xpdo->queues as $index => $queue) {
                if ($queue === $this || (int) $queue->get('id') === (int) $this->get('id')) {
                    unset($this->xpdo->queues[$index]);
                    $this->xpdo->queues = array_values($this->xpdo->queues);
                    break;
                }
            }
        }

        return true;
    }
}
