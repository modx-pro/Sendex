<?php

/**
 * Sendex bootstrap: shared init for connector, mgr, and cron (#74).
 */

if (!function_exists('sendexCorePath')) {
    /**
     * @param modX $modx
     * @return string
     */
    function sendexCorePath($modx)
    {
        return $modx->getOption(
            'sendex_core_path',
            null,
            $modx->getOption('core_path') . 'components/sendex/'
        );
    }
}

if (!function_exists('sendexRegisterAutoload')) {
    /**
     * @param string $corePath
     * @return void
     */
    function sendexRegisterAutoload($corePath)
    {
        static $registered = false;
        if ($registered) {
            return;
        }
        $registered = true;

        $modelDir = rtrim($corePath, '/') . '/model/sendex/';
        $processorsDir = rtrim($corePath, '/') . '/processors/mgr/';

        spl_autoload_register(static function ($class) use ($modelDir, $processorsDir) {
            if ($class === 'sxSendexProcessor') {
                $path = $processorsDir . 'sendexprocessor.class.php';
                if (is_file($path)) {
                    require_once $path;
                }

                return;
            }

            if ($class === 'sxProcessorInput') {
                $path = $modelDir . 'sxprocessorinput.class.php';
                if (is_file($path)) {
                    require_once $path;
                }

                return;
            }

            if (strpos($class, 'sx') !== 0) {
                return;
            }

            $path = $modelDir . strtolower($class) . '.class.php';
            if (is_file($path)) {
                require_once $path;
            }
        });
    }
}

if (!function_exists('sendexEnsureProcessorBase')) {
    /**
     * Ensure legacy processor base classes resolve on MODX 3.
     *
     * @return void
     */
    function sendexEnsureProcessorBase()
    {
        if (class_exists('modProcessor', false)) {
            return;
        }

        $map = array(
            'modProcessor'              => 'MODX\\Revolution\\Processors\\Processor',
            'modObjectProcessor'        => 'MODX\\Revolution\\Processors\\Model\\ModelProcessor',
            'modObjectGetProcessor'     => 'MODX\\Revolution\\Processors\\Model\\GetProcessor',
            'modObjectGetListProcessor' => 'MODX\\Revolution\\Processors\\Model\\GetListProcessor',
            'modObjectCreateProcessor'  => 'MODX\\Revolution\\Processors\\Model\\CreateProcessor',
            'modObjectUpdateProcessor'  => 'MODX\\Revolution\\Processors\\Model\\UpdateProcessor',
        );

        foreach ($map as $legacy => $namespaced) {
            if (!class_exists($legacy, false) && class_exists($namespaced)) {
                class_alias($namespaced, $legacy);
            }
        }
    }
}

if (!function_exists('sendexBootstrap')) {
    /**
     * @param modX $modx
     * @param array $config
     * @return Sendex
     */
    function sendexBootstrap($modx, array $config = array())
    {
        $corePath = sendexCorePath($modx);
        sendexRegisterAutoload($corePath);
        sendexEnsureProcessorBase();

        require_once $corePath . 'model/sendex/sendex.class.php';

        if (!isset($modx->sendex) || !($modx->sendex instanceof Sendex)) {
            $modx->sendex = new Sendex($modx, $config);
        }

        $modx->lexicon->load('sendex:default');

        return $modx->sendex;
    }
}
