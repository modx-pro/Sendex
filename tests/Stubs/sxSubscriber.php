<?php

require_once dirname(__DIR__, 2) . '/core/components/sendex/model/sendex/sxsubscribercode.class.php';

class sxSubscriber extends xPDOSimpleObject
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

        if (sxSubscriberCode::needsNewCode($this->get('code'))) {
            $this->set(
                'code',
                sxSubscriberCode::generate($this->user_id, $this->newsletter_id, $this->email)
            );
        }

        if ($this->get('id') === null && $this->xpdo instanceof FakeModX) {
            $this->set('id', count($this->xpdo->subscribers) + 1);
            $this->xpdo->subscribers[] = $this;
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
            foreach ($this->xpdo->subscribers as $index => $subscriber) {
                if ($subscriber === $this) {
                    unset($this->xpdo->subscribers[$index]);
                    $this->xpdo->subscribers = array_values($this->xpdo->subscribers);
                    break;
                }
            }
        }

        return true;
    }
}
