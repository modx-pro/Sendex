<?php

/**
 * MODX 2/3 compatibility at service boundaries (#74).
 */
class sxModxCompat
{
    /**
     * @param object $modx
     * @return object|null modPHPMailer-like
     */
    public static function getMail($modx)
    {
        if (self::hasServices($modx)) {
            $services = $modx->services;
            if (method_exists($services, 'has') && $services->has('mail')) {
                return $services->get('mail');
            }
        }

        return $modx->getService('mail', 'mail.modPHPMailer');
    }

    /**
     * @param object $modx
     * @return object|null
     */
    public static function getParser($modx)
    {
        if (self::hasServices($modx)) {
            $services = $modx->services;
            if (method_exists($services, 'has') && $services->has('parser')) {
                return $services->get('parser');
            }
        }

        return $modx->getService(
            'parser',
            $modx->getOption('parser_class', null, 'modParser'),
            $modx->getOption('parser_class_path', null, '')
        );
    }

    /**
     * @param object $modx
     * @return object|null registry.modRegistry
     */
    public static function getRegistry($modx)
    {
        if (self::hasServices($modx)) {
            $services = $modx->services;
            if (method_exists($services, 'has') && $services->has('registry')) {
                return $services->get('registry');
            }
        }

        return $modx->getService('registry', 'registry.modRegistry');
    }

    /**
     * @param string $name BODY|FROM|FROM_NAME|SUBJECT|...
     * @return string
     */
    public static function mailConst($name)
    {
        $key = 'MAIL_' . strtoupper($name);
        if (class_exists('modMail', false)) {
            return constant('modMail::' . $key);
        }

        $fqcn = 'MODX\\Revolution\\Mail\\modMail';
        if (class_exists($fqcn)) {
            return constant($fqcn . '::' . $key);
        }

        return strtolower($name);
    }

    /**
     * @param object $modx
     * @return bool
     */
    protected static function hasServices($modx)
    {
        return isset($modx->services)
            && is_object($modx->services)
            && method_exists($modx->services, 'get');
    }
}
