<?php

class FakeModX extends modX
{
    /** @var sxSubscriber[] */
    public $subscribers = array();

    /** @var array<int,string> user_id => email */
    public $profiles = array();

    /** @var array<string,mixed> */
    public $invokeResponses = array();

    /** @var array<int,array{0:string,1:array}> */
    public $invoked = array();

    /**
     * @param string $class
     * @param array|null $criteria
     *
     * @return FakeQuery
     */
    public function newQuery($class, $criteria = null)
    {
        return new FakeQuery($class, $criteria);
    }

    /**
     * @param string $class
     *
     * @return xPDOSimpleObject
     */
    public function newObject($class)
    {
        if ($class === 'sxSubscriber') {
            return new sxSubscriber($this);
        }

        throw new InvalidArgumentException('Unknown class: ' . $class);
    }

    /**
     * @param string $class
     * @param FakeQuery|array|null $criteria
     *
     * @return xPDOSimpleObject|null
     */
    public function getObject($class, $criteria = null)
    {
        if ($class === 'modUserProfile') {
            $userId = is_array($criteria) && isset($criteria['internalKey'])
                ? (int) $criteria['internalKey']
                : 0;
            if ($userId && isset($this->profiles[$userId])) {
                $profile = new modUserProfile($this);
                $profile->set('email', $this->profiles[$userId]);
                $profile->set('internalKey', $userId);

                return $profile;
            }

            return null;
        }

        if ($class === 'sxSubscriber') {
            $where = array();
            if ($criteria instanceof FakeQuery) {
                $where = $criteria->where;
            } elseif (is_array($criteria)) {
                $where = $criteria;
            }

            foreach ($this->subscribers as $subscriber) {
                $match = true;
                foreach ($where as $key => $value) {
                    if ((string) $subscriber->get($key) !== (string) $value) {
                        $match = false;
                        break;
                    }
                }
                if ($match) {
                    return $subscriber;
                }
            }

            return null;
        }

        return null;
    }

    /**
     * @param string $name
     * @param array $params
     *
     * @return mixed
     */
    public function invokeEvent($name, array $params = array())
    {
        $this->invoked[] = array($name, $params);

        if (array_key_exists($name, $this->invokeResponses)) {
            return $this->invokeResponses[$name];
        }

        return array();
    }
}
