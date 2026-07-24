<?php

/**
 * Minimal xPDOSimpleObject stub for unit tests (no MODX install required).
 */
class xPDOSimpleObject
{
    /** @var FakeModX|null */
    public $xpdo;

    /** @var array */
    public $_fields = array();

    /**
     * @param FakeModX|null $xpdo
     */
    public function __construct(&$xpdo = null)
    {
        $this->xpdo = &$xpdo;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return array_key_exists($key, $this->_fields) ? $this->_fields[$key] : null;
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return bool
     */
    public function set($key, $value)
    {
        $this->_fields[$key] = $value;

        return true;
    }

    /**
     * @param array $array
     * @param string $keyPrefix
     * @param bool $setPrimaryKeys
     * @param bool $rawValues
     *
     * @return bool
     */
    public function fromArray($array, $keyPrefix = '', $setPrimaryKeys = false, $rawValues = false)
    {
        foreach ($array as $key => $value) {
            $this->_fields[$key] = $value;
        }

        return true;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->_fields;
    }

    /**
     * @param array $ancestors
     *
     * @return bool
     */
    public function remove(array $ancestors = array())
    {
        if ($this->xpdo instanceof FakeModX && $this instanceof sxNewsletter) {
            $id = (int) $this->get('id');
            if ($id && isset($this->xpdo->newsletters[$id]) && $this->xpdo->newsletters[$id] === $this) {
                unset($this->xpdo->newsletters[$id]);
            }
            foreach ($this->xpdo->subscribers as $index => $subscriber) {
                if ((int) $subscriber->get('newsletter_id') === $id) {
                    unset($this->xpdo->subscribers[$index]);
                }
            }
            $this->xpdo->subscribers = array_values($this->xpdo->subscribers);
        }

        return true;
    }

    /**
     * @param string $alias
     *
     * @return array
     */
    public function getMany($alias)
    {
        if ($alias === 'Subscribers' && $this->xpdo instanceof FakeModX) {
            $id = (int) $this->get('id');
            $out = array();
            foreach ($this->xpdo->subscribers as $subscriber) {
                if ((int) $subscriber->get('newsletter_id') === $id) {
                    $out[] = $subscriber;
                }
            }

            return $out;
        }

        return array();
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return array_key_exists($name, $this->_fields) && $this->_fields[$name] !== null;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }
}
