<?php

class modTemplate
{
    public $_cacheable;
    public $_processed;
    public $_output = '';

    /**
     * @param array $properties
     *
     * @return string
     */
    public function process(array $properties = array())
    {
        $email = isset($properties['subscriber']['email'])
            ? $properties['subscriber']['email']
            : '';

        return 'Body for ' . $email;
    }
}
