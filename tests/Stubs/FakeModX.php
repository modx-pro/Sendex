<?php

class FakeModX extends modX
{
    /** @var sxSubscriber[] */
    public $subscribers = array();

    /** @var sxQueue[] */
    public $queues = array();

    /** @var array<int,string> user_id => email */
    public $profiles = array();

    /** @var array<int,modUserProfile> */
    public $userProfiles = array();

    /** @var array<int,modUser> */
    public $users = array();

    /** @var array<int,modTemplate> */
    public $templates = array();

    /** @var array<int,sxNewsletter> */
    public $newsletters = array();

    /** @var array<string,array> */
    public $registryEntries = array();

    /** @var string */
    public $lastRegisterPath = '';

    /** @var int|null */
    public $lastRegisterTtl = null;

    /** @var array<string,mixed> */
    public $options = array(
        'emailsender'           => 'noreply@example.com',
        'site_name'             => 'Example',
        'parser_class'          => 'modParser',
        'parser_class_path'     => '',
        'parser_max_iterations' => 10,
    );

    /** @var array<string,mixed> */
    public $services = array();

    /** @var array<string,mixed> */
    public $invokeResponses = array();

    /** @var array<int,array{0:string,1:array}> */
    public $invoked = array();

    public function __construct()
    {
        $this->services['registry'] = new FakeRegistry($this);
        $this->services['parser'] = new modParser();
    }

    /**
     * @param string $key
     * @param array|null $options
     * @param mixed $default
     * @param bool $skipEmpty
     *
     * @return mixed
     */
    public function getOption($key, $options = null, $default = null, $skipEmpty = false)
    {
        if (is_array($options) && array_key_exists($key, $options)) {
            return $options[$key];
        }
        if (array_key_exists($key, $this->options)) {
            return $this->options[$key];
        }

        return $default;
    }

    /**
     * @param string $key
     *
     * @return string
     */
    public function lexicon($key)
    {
        return $key;
    }

    /**
     * @param string $name
     * @param string $class
     * @param string $path
     *
     * @return mixed
     */
    public function getService($name, $class = '', $path = '')
    {
        return isset($this->services[$name]) ? $this->services[$name] : null;
    }

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
        if ($class === 'sxQueue') {
            return new sxQueue($this);
        }

        throw new InvalidArgumentException('Unknown class: ' . $class);
    }

    /**
     * @param string $class
     * @param FakeQuery|array|int|null $criteria
     *
     * @return mixed
     */
    public function getObject($class, $criteria = null)
    {
        if ($class === 'modUserProfile') {
            if (is_array($criteria) && isset($criteria['email'])) {
                $want = strtolower((string) $criteria['email']);
                foreach ($this->userProfiles as $userId => $profile) {
                    if (strtolower((string) $profile->get('email')) === $want) {
                        return $profile;
                    }
                }
                foreach ($this->profiles as $userId => $email) {
                    if (strtolower((string) $email) === $want) {
                        $profile = new modUserProfile($this);
                        $profile->set('email', $email);
                        $profile->set('blocked', false);
                        $profile->set('internalKey', $userId);

                        return $profile;
                    }
                }

                return null;
            }

            $userId = is_array($criteria) && isset($criteria['internalKey'])
                ? (int) $criteria['internalKey']
                : 0;
            if ($userId && isset($this->userProfiles[$userId])) {
                return $this->userProfiles[$userId];
            }
            if ($userId && isset($this->profiles[$userId])) {
                $profile = new modUserProfile($this);
                $profile->set('email', $this->profiles[$userId]);
                $profile->set('blocked', false);
                $profile->set('internalKey', $userId);

                return $profile;
            }

            return null;
        }

        if ($class === 'modUser') {
            $id = is_numeric($criteria) ? (int) $criteria : 0;
            if (is_array($criteria) && isset($criteria['id'])) {
                $id = (int) $criteria['id'];
            }

            return isset($this->users[$id]) ? $this->users[$id] : null;
        }

        if ($class === 'modTemplate') {
            $id = is_numeric($criteria) ? (int) $criteria : 0;

            return isset($this->templates[$id]) ? $this->templates[$id] : null;
        }

        if ($class === 'sxNewsletter') {
            $id = is_numeric($criteria) ? (int) $criteria : 0;
            if (is_array($criteria) && isset($criteria['id'])) {
                $id = (int) $criteria['id'];
                if (isset($criteria['active']) && isset($this->newsletters[$id])) {
                    $newsletter = $this->newsletters[$id];
                    if ((int) $newsletter->get('active') !== (int) $criteria['active']) {
                        return null;
                    }
                }
            }

            return isset($this->newsletters[$id]) ? $this->newsletters[$id] : null;
        }

        if ($class === 'sxSubscriber') {
            if (is_numeric($criteria)) {
                $id = (int) $criteria;
                foreach ($this->subscribers as $subscriber) {
                    if ((int) $subscriber->get('id') === $id) {
                        return $subscriber;
                    }
                }

                return null;
            }

            $where = array();
            if ($criteria instanceof FakeQuery) {
                $where = $criteria->where;
            } elseif (is_array($criteria)) {
                $where = $criteria;
            }

            foreach ($this->subscribers as $subscriber) {
                if (sxSubscriberMatch::matchesWhere($where, $subscriber)) {
                    return $subscriber;
                }
            }

            return null;
        }

        return null;
    }

    /**
     * @param string $class
     * @param array $criteria
     *
     * @return int
     */
    public function getCount($class, $criteria = array())
    {
        if ($class !== 'sxSubscriber') {
            return 0;
        }

        $count = 0;
        foreach ($this->subscribers as $subscriber) {
            $match = true;
            foreach ($criteria as $key => $value) {
                if ((string) $subscriber->get($key) !== (string) $value) {
                    $match = false;
                    break;
                }
            }
            if ($match) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param string $class
     * @param array $criteria
     *
     * @return sxSubscriber[]
     */
    public function getIterator($class, $criteria = array())
    {
        if ($class !== 'sxSubscriber') {
            return array();
        }

        $ids = array();
        if (isset($criteria['id:IN']) && is_array($criteria['id:IN'])) {
            $ids = array_map('intval', $criteria['id:IN']);
        }

        $out = array();
        foreach ($this->subscribers as $subscriber) {
            if ($ids && !in_array((int) $subscriber->get('id'), $ids, true)) {
                continue;
            }
            $out[] = $subscriber;
        }

        return $out;
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
