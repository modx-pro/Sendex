<?php

class modProcessor
{
    /** @var FakeModX */
    public $modx;

    /** @var array */
    public $properties = array();

    /** @var array */
    public $errors = array();

    /**
     * @param FakeModX $modx
     * @param array $properties
     */
    public function __construct(FakeModX &$modx, array $properties = array())
    {
        $this->modx = &$modx;
        $this->properties = $properties;
    }

    /**
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function getProperty($key, $default = null)
    {
        return array_key_exists($key, $this->properties) ? $this->properties[$key] : $default;
    }

    /**
     * @param string $field
     * @param string $message
     */
    public function addFieldError($field, $message)
    {
        $this->errors[$field] = $message;
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    /**
     * @param string $message
     * @param mixed $object
     *
     * @return array
     */
    public function failure($message = '', $object = null)
    {
        return array(
            'success' => false,
            'message' => $message,
            'object'  => $object,
        );
    }

    /**
     * @param string $message
     * @param mixed $object
     *
     * @return array
     */
    public function success($message = '', $object = null)
    {
        return array(
            'success' => true,
            'message' => $message,
            'object'  => $object,
        );
    }
}
