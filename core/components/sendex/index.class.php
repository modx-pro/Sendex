<?php

/**
 * Class IndexManagerController
 */

require_once dirname(__FILE__) . '/sendexmaincontroller.class.php';

class IndexManagerController extends SendexMainController
{
    /**
     * @return string
     */
    public static function getDefaultController()
    {
        return 'home';
    }
}
