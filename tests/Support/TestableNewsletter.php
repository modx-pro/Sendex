<?php

class TestableNewsletter extends sxNewsletter
{
    /**
     * @param string $name
     * @param array $params
     *
     * @return true|string
     */
    public function callInvokeSendexEvent($name, array $params = array())
    {
        return $this->invokeSendexEvent($name, $params);
    }
}
