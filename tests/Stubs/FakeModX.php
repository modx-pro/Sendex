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

    /** @var array<string,callable> name => function (array &$params): void */
    public $invokeMutators = array();

    /** @var array<string,int> */
    public $getObjectCalls = array();

    /** @var array<string,int> */
    public $getCollectionCalls = array();

    /** @var array<string,bool> */
    public $permissions = array(
        'edit_document' => true,
        'new_document'  => true,
        'view_document' => true,
    );

    /** @var array<int,array{0:mixed,1:string}> */
    public $logs = array();

    /** @var FakePdoConnection|null */
    public $fakeConnection;

    public function __construct()
    {
        $this->services['registry'] = new FakeRegistry($this);
        $this->services['parser'] = new modParser();
    }

    /**
     * @param mixed $level
     * @param string $message
     */
    public function log($level, $message)
    {
        $this->logs[] = array($level, (string) $message);
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
     * @param array $params
     *
     * @return string
     */
    public function lexicon($key, $params = array())
    {
        if ($key === 'access_denied') {
            return 'access_denied';
        }

        if (!is_array($params) || $params === array()) {
            return $key;
        }

        $out = $key;
        foreach ($params as $name => $value) {
            $out .= ':' . $name . '=' . $value;
        }

        return $out;
    }

    /**
     * @param string $permission
     *
     * @return bool
     */
    public function hasPermission($permission)
    {
        return !empty($this->permissions[$permission]);
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
        if ($name === 'mail') {
            if (!isset($this->services['mail'])) {
                $this->services['mail'] = new FakeMail();
            }

            return $this->services['mail'];
        }

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
        if (!isset($this->getObjectCalls[$class])) {
            $this->getObjectCalls[$class] = 0;
        }
        $this->getObjectCalls[$class]++;
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

        if ($class === 'sxQueue') {
            if (is_numeric($criteria)) {
                $id = (int) $criteria;
                foreach ($this->queues as $queue) {
                    if ((int) $queue->get('id') === $id) {
                        return $queue;
                    }
                }

                return null;
            }

            return null;
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
     * @param FakeQuery|array|null $criteria
     *
     * @return array
     */
    public function getCollection($class, $criteria = null)
    {
        if (!isset($this->getCollectionCalls[$class])) {
            $this->getCollectionCalls[$class] = 0;
        }
        $this->getCollectionCalls[$class]++;

        if ($class === 'modUser') {
            return $this->userCollection($criteria);
        }

        if ($class === 'modUserProfile') {
            return $this->userProfileCollection($criteria);
        }

        if ($class === 'sxQueue') {
            return $this->queueCollection($criteria);
        }

        if ($class !== 'sxSubscriber') {
            return array();
        }

        $where = array();
        if ($criteria instanceof FakeQuery) {
            $where = $criteria->where;
        } elseif (is_array($criteria)) {
            $where = $criteria;
        }

        $out = array();
        foreach ($this->subscribers as $subscriber) {
            if ($where === array() || sxSubscriberMatch::matchesWhere($where, $subscriber)) {
                $out[] = $subscriber;
            }
        }

        return $out;
    }

    /**
     * @param FakeQuery|array|null $criteria
     *
     * @return modUser[]
     */
    protected function userCollection($criteria)
    {
        $ids = $this->inCriteria($criteria, 'id');
        $out = array();
        foreach ($this->users as $id => $user) {
            if ($ids && !in_array((int) $id, $ids, true)) {
                continue;
            }
            $out[] = $user;
        }

        return $out;
    }

    /**
     * @param FakeQuery|array|null $criteria
     *
     * @return modUserProfile[]
     */
    protected function userProfileCollection($criteria)
    {
        $ids = $this->inCriteria($criteria, 'internalKey');
        $out = array();
        foreach ($this->userProfiles as $userId => $profile) {
            if ($ids && !in_array((int) $userId, $ids, true)) {
                continue;
            }
            $out[] = $profile;
        }

        return $out;
    }

    /**
     * @param FakeQuery|array|null $criteria
     * @param string $field
     *
     * @return int[]|null
     */
    protected function inCriteria($criteria, $field)
    {
        $key = $field . ':IN';
        if ($criteria instanceof FakeQuery && isset($criteria->where[$key])) {
            return array_map('intval', $criteria->where[$key]);
        }
        if (is_array($criteria) && isset($criteria[$key])) {
            return array_map('intval', $criteria[$key]);
        }

        return null;
    }

    /**
     * @param FakeQuery|array|null $criteria
     *
     * @return sxQueue[]
     */
    protected function queueCollection($criteria)
    {
        $where = array();
        $limit = null;
        if ($criteria instanceof FakeQuery) {
            $where = $criteria->where;
            $limit = $criteria->limit;
        } elseif (is_array($criteria)) {
            $where = $criteria;
        }

        $ids = array();
        if (isset($where['id:IN']) && is_array($where['id:IN'])) {
            $ids = array_map('intval', $where['id:IN']);
        }

        $out = array();
        foreach ($this->queues as $queue) {
            if ($ids && !in_array((int) $queue->get('id'), $ids, true)) {
                continue;
            }
            $out[] = $queue;
        }

        if ($limit !== null && $limit > 0) {
            $out = array_slice($out, 0, $limit);
        }

        return $out;
    }

    /**
     * @param string $class
     * @param array $criteria
     *
     * @return int
     */
    public function getCount($class, $criteria = array())
    {
        if ($class === 'sxQueue') {
            $count = 0;
            foreach ($this->queues as $queue) {
                $match = true;
                foreach ($criteria as $key => $value) {
                    if ((string) $queue->get($key) !== (string) $value) {
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
     * @param string $class
     * @param string $alias
     * @param string $prefix
     * @param array $fields
     * @return string
     */
    public function getSelectColumns($class, $alias, $prefix = '', array $fields = array())
    {
        if ($fields !== array()) {
            $out = array();
            foreach ($fields as $field) {
                $out[] = $alias . '.' . $field;
            }

            return implode(', ', $out);
        }

        return $alias . '.*';
    }

    /**
     * @param string $class
     * @return string
     */
    public function getTableName($class)
    {
        if ($class === 'sxSubscriber') {
            return 'sendex_subscribers';
        }

        return $class;
    }

    /**
     * @param int|string $id
     * @param string $context
     * @param array|string $args
     * @param mixed $scheme
     * @return string
     */
    public function makeUrl($id, $context = '', $args = array(), $scheme = -1)
    {
        if (is_array($args)) {
            $query = http_build_query($args);
        } else {
            $query = (string) $args;
        }

        $url = 'https://example.com/index.php?id=' . (int) $id;
        if ($query !== '') {
            $url .= '&' . $query;
        }

        return $url;
    }

    /**
     * @return FakePdoConnection
     */
    public function getConnection()
    {
        if ($this->fakeConnection === null) {
            $this->fakeConnection = new FakePdoConnection($this);
        }

        return $this->fakeConnection;
    }

    /**
     * @param string $name
     * @param array $params
     *
     * @return mixed
     */
    public function invokeEvent($name, array &$params = array())
    {
        if (isset($this->invokeMutators[$name]) && is_callable($this->invokeMutators[$name])) {
            call_user_func_array($this->invokeMutators[$name], array(&$params));
        }

        $this->invoked[] = array($name, $params);

        if (array_key_exists($name, $this->invokeResponses)) {
            return $this->invokeResponses[$name];
        }

        return array();
    }
}
