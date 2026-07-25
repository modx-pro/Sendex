<?php

require_once dirname(__FILE__) . '/sxqueueclaim.class.php';
require_once dirname(__FILE__) . '/sxsendexevent.class.php';

/**
 * Deliver one queue email with claim / requeue (#55) and send events (#104 A1).
 *
 * sendMail return values:
 * - true: sent
 * - false: skip (row already claimed; do not requeue)
 * - string: mail error (requeue + sxOnQueueSendFailed)
 */
class sxQueueDeliver
{
    /**
     * @param object $queue sxQueue-like with get/set/toArray/save
     * @param callable $sendMail function (object $queue): true|false|string
     * @return true|false|string true on sent, false if not claimed or skipped, string on mail error
     */
    public static function send($queue, $sendMail)
    {
        $xpdo = $queue->xpdo;
        if (!sxQueueClaim::tryClaim($xpdo, (int) $queue->get('id'))) {
            return false;
        }

        $result = call_user_func($sendMail, $queue);
        if ($result === true) {
            return true;
        }

        // Plugin skip or soft abort: row already removed; do not put it back.
        if ($result === false) {
            return false;
        }

        // Claim already removed the row; put it back for retry.
        $payload = $queue->toArray();
        unset($payload['id']);
        $again = $xpdo->newObject('sxQueue');
        $again->fromArray($payload, '', true, true);
        $again->save();

        $error = is_string($result) ? $result : 'unknown error';
        $failedParams = array(
            'queue' => $queue,
            'error' => $error,
        );
        sxSendexEvent::invoke($xpdo, 'sxOnQueueSendFailed', $failedParams);

        if (method_exists($xpdo, 'log')) {
            $xpdo->log(
                defined('xPDO::LOG_LEVEL_ERROR') ? constant('xPDO::LOG_LEVEL_ERROR') : 3,
                'Sendex queue send failed after claim: ' . $error
            );
        }

        return $result;
    }
}
