<?php

/**
 * Cancelable Sendex system events via modX::invokeEvent.
 */
class sxSendexEvent
{
    /**
     * Before-events cancel when a plugin outputs a non-empty string.
     * Params are passed by reference so plugins can mutate values (e.g. message).
     *
     * @param object $xpdo
     * @param string $name
     * @param array $params
     * @return true|string true, or first plugin error message if cancelled
     */
    public static function invoke($xpdo, $name, array &$params)
    {
        if (!($xpdo instanceof modX) || !method_exists($xpdo, 'invokeEvent')) {
            return true;
        }

        $response = $xpdo->invokeEvent($name, $params);
        if (is_array($response)) {
            $messages = array();
            foreach ($response as $item) {
                if (is_string($item) && trim($item) !== '') {
                    $messages[] = trim($item);
                }
            }
            if (!empty($messages)) {
                return reset($messages);
            }
        }

        return true;
    }
}
