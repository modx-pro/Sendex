<?php

require_once dirname(__FILE__) . '/sxqueueclaim.class.php';

/**
 * Deliver one queue email with claim / requeue (#55).
 */
class sxQueueDeliver
{
    /**
     * @param object $queue sxQueue-like with get/set/toArray/save
     * @param callable $sendMail function (object $queue): true|string
     * @return true|false|string true on sent, false if not claimed, string on mail error
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

        // Claim already removed the row; put it back for retry.
        $payload = $queue->toArray();
        unset($payload['id']);
        $again = $xpdo->newObject('sxQueue');
        $again->fromArray($payload, '', true, true);
        $again->save();

        if (method_exists($xpdo, 'log')) {
            $xpdo->log(
                defined('xPDO::LOG_LEVEL_ERROR') ? constant('xPDO::LOG_LEVEL_ERROR') : 3,
                'Sendex queue send failed after claim: '
                    . (is_string($result) ? $result : 'unknown error')
            );
        }

        return $result;
    }
}
