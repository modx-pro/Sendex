<?php

/**
 * Frontend AJAX subscribe/unsubscribe JSON contract (#42).
 */
class sxSubscribeAjaxResponse
{
    /**
     * @param array $scriptProperties
     * @return bool
     */
    public static function isRequest(array $scriptProperties = array())
    {
        if (
            isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) {
            return true;
        }

        if (!empty($_REQUEST['ajax']) || !empty($_REQUEST['sendex_ajax'])) {
            return true;
        }

        return false;
    }

    /**
     * @param array $placeholders
     * @param string $html
     * @return array{success:bool,message:string,html:string}
     */
    public static function payload(array $placeholders, $html)
    {
        return array(
            'success' => empty($placeholders['error']),
            'message' => (string) (isset($placeholders['message']) ? $placeholders['message'] : ''),
            'html'    => (string) $html,
        );
    }

    /**
     * @param array $placeholders
     * @param string $html
     */
    public static function send(array $placeholders, $html)
    {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=UTF-8');
        }

        echo json_encode(
            self::payload($placeholders, $html),
            JSON_UNESCAPED_UNICODE
        );
        @session_write_close();
        exit;
    }

    /**
     * Whether the POST/AJAX action targets this snippet instance (multi-widget pages).
     *
     * @param int|string $snippetNewsletterId
     * @param array      $request
     * @return bool
     */
    public static function matchesNewsletter($snippetNewsletterId, array $request)
    {
        if (!isset($request['newsletter_id']) || $request['newsletter_id'] === '') {
            return true;
        }

        return (int) $request['newsletter_id'] === (int) $snippetNewsletterId;
    }

    /**
     * @param int|string $snippetNewsletterId
     * @param string     $widgetKey
     * @param array      $request
     * @return bool
     */
    public static function matchesRequest($snippetNewsletterId, $widgetKey, array $request)
    {
        if (!self::matchesNewsletter($snippetNewsletterId, $request)) {
            return false;
        }

        $requestWidgetKey = isset($request['sendex_widget_key'])
            ? trim((string) $request['sendex_widget_key'])
            : '';

        if ($requestWidgetKey !== '') {
            return $widgetKey !== '' && $requestWidgetKey === (string) $widgetKey;
        }

        // Email confirm/unsubscribe links and legacy forms omit sendex_widget_key.
        return $widgetKey === '';
    }

    /**
     * @param mixed $value
     * @param bool $default
     * @return bool
     */
    public static function parseEnabled($value, $default = true)
    {
        if ($value === null || $value === '') {
            return $default;
        }
        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtolower(trim((string) $value));
        if (in_array($normalized, array('0', 'false', 'no', 'off'), true)) {
            return false;
        }
        if (in_array($normalized, array('1', 'true', 'yes', 'on'), true)) {
            return true;
        }

        return $default;
    }
}
