<?php

require_once dirname(__FILE__) . '/sxqueueclaim.class.php';
require_once dirname(__FILE__) . '/sxsendexevent.class.php';

/**
 * Deliver one queue email with claim/retry (#55, #105) and send events (#104 A1).
 *
 * sendMail return values:
 * - true: sent
 * - false: skip (row already claimed or plugin skip)
 * - string: mail error (release claim + sxOnQueueSendFailed)
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
        $claimStrategy = sxQueueClaim::lastStrategy();
        $snapshot = self::queueSnapshot($queue);

        $result = call_user_func($sendMail, $queue);
        if ($result === true) {
            if ($claimStrategy === 'lease') {
                self::dropQueueRow($queue);
            }
            return true;
        }

        // Plugin skip or soft abort: consume queue row, no retry.
        if ($result === false) {
            if ($claimStrategy === 'lease') {
                self::dropQueueRow($queue);
            }
            return false;
        }

        if ($claimStrategy === 'lease') {
            self::releaseClaimForRetry($queue);
        } else {
            self::requeueFromSnapshot($xpdo, $snapshot);
        }

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

    /**
     * @param object $queue
     * @return void
     */
    private static function dropQueueRow($queue)
    {
        if (!method_exists($queue, 'remove')) {
            return;
        }

        if ($queue->remove() !== false) {
            return;
        }

        if (self::forceDeleteRowViaSql($queue)) {
            return;
        }

        self::pinClaimAsConsumed($queue);
    }

    /**
     * @param object $queue
     * @return void
     */
    private static function releaseClaimForRetry($queue)
    {
        if (!method_exists($queue, 'toArray') || !method_exists($queue, 'set') || !method_exists($queue, 'save')) {
            return;
        }

        $data = $queue->toArray();
        if (array_key_exists('claimed_at', $data)) {
            $queue->set('claimed_at', null);
        }
        if (array_key_exists('expires_at', $data)) {
            $queue->set('expires_at', null);
        }
        if ($queue->save() !== false) {
            return;
        }

        self::forceReleaseClaimViaSql($queue);
    }

    /**
     * @param object $queue
     * @return void
     */
    private static function forceReleaseClaimViaSql($queue)
    {
        $xpdo = isset($queue->xpdo) ? $queue->xpdo : null;
        if (
            !is_object($xpdo)
            || !method_exists($xpdo, 'getConnection')
            || !method_exists($xpdo, 'getTableName')
        ) {
            return;
        }

        $connection = $xpdo->getConnection();
        if (!is_object($connection) || !method_exists($connection, 'prepare')) {
            return;
        }

        $table = (string) $xpdo->getTableName('sxQueue');
        if ($table === '') {
            return;
        }

        $stmt = $connection->prepare(
            'UPDATE ' . $table . ' SET claimed_at = NULL, expires_at = NULL WHERE id = ?'
        );
        if ($stmt) {
            $stmt->execute(array((int) $queue->get('id')));
        }
    }

    /**
     * @param object $queue
     * @return void
     */
    private static function forceDeleteRowViaSql($queue)
    {
        $xpdo = isset($queue->xpdo) ? $queue->xpdo : null;
        if (
            !is_object($xpdo)
            || !method_exists($xpdo, 'getConnection')
            || !method_exists($xpdo, 'getTableName')
        ) {
            return;
        }

        $connection = $xpdo->getConnection();
        if (!is_object($connection) || !method_exists($connection, 'prepare')) {
            return;
        }

        $table = (string) $xpdo->getTableName('sxQueue');
        if ($table === '') {
            return;
        }

        $stmt = $connection->prepare('DELETE FROM ' . $table . ' WHERE id = ?');
        if (!$stmt || !$stmt->execute(array((int) $queue->get('id')))) {
            return false;
        }

        return !method_exists($stmt, 'rowCount') || (int) $stmt->rowCount() > 0;
    }

    /**
     * @param object $queue
     * @return void
     */
    private static function pinClaimAsConsumed($queue)
    {
        if (method_exists($queue, 'toArray') && method_exists($queue, 'set') && method_exists($queue, 'save')) {
            $data = $queue->toArray();
            if (array_key_exists('expires_at', $data)) {
                $queue->set('expires_at', null);
            }
            $queue->save();
        }
    }

    /**
     * @param object $queue
     * @return array
     */
    private static function queueSnapshot($queue)
    {
        if (!method_exists($queue, 'toArray')) {
            return array();
        }

        return $queue->toArray();
    }

    /**
     * @param object $xpdo
     * @param array $snapshot
     * @return void
     */
    private static function requeueFromSnapshot($xpdo, array $snapshot)
    {
        if (empty($snapshot) || !method_exists($xpdo, 'newObject')) {
            return;
        }

        unset($snapshot['id']);
        if (array_key_exists('claimed_at', $snapshot)) {
            $snapshot['claimed_at'] = null;
        }
        if (array_key_exists('expires_at', $snapshot)) {
            $snapshot['expires_at'] = null;
        }

        $again = $xpdo->newObject('sxQueue');
        $again->fromArray($snapshot, '', true, true);
        $again->save();
    }
}
