<?php

require_once dirname(__FILE__) . '/sxqueuedeliver.class.php';
require_once dirname(__FILE__) . '/sxnewslettermailer.class.php';

/**
 * Single entry point for queue delivery (#65): cron and mgr processors call flush/sendOne.
 */
class sxQueueSender
{
    /**
     * Claim, send mail, requeue on failure (#55).
     *
     * @param object $queue sxQueue
     * @return true|false|string
     */
    public static function sendOne($queue)
    {
        return sxQueueDeliver::send($queue, array(__CLASS__, 'deliverMail'));
    }

    /**
     * Send a batch of queue rows.
     *
     * Options:
     * - criteria (array): xPDO where, e.g. array('id:IN' => array(1, 2))
     * - limit (int): max rows (cron)
     * - stopOnError (bool): mgr processors stop on first mail error
     * - logErrors (bool): cron-style error log for non-true results
     * - sendFn (callable): test hook; defaults to sendOne
     *
     * @param object $xpdo modX / xPDO
     * @param array $options
     * @return array{sent:int,skipped:int,failed:int,firstError:string|null}
     */
    public static function flush($xpdo, array $options = array())
    {
        $stopOnError = !empty($options['stopOnError']);
        $logErrors = !empty($options['logErrors']);
        $sendFn = isset($options['sendFn']) && is_callable($options['sendFn'])
            ? $options['sendFn']
            : array(__CLASS__, 'sendOne');

        $query = $xpdo->newQuery('sxQueue');
        if (!empty($options['criteria']) && is_array($options['criteria'])) {
            $query->where($options['criteria']);
        }
        if (isset($options['limit']) && (int) $options['limit'] > 0) {
            $query->limit((int) $options['limit']);
        }

        $queues = $xpdo->getCollection('sxQueue', $query);
        $stats = array(
            'sent'       => 0,
            'skipped'    => 0,
            'failed'     => 0,
            'firstError' => null,
        );

        foreach ($queues as $queue) {
            $result = call_user_func($sendFn, $queue);
            if ($result === true) {
                $stats['sent']++;
                continue;
            }

            if ($result === false) {
                $stats['skipped']++;
            } else {
                $stats['failed']++;
            }

            $message = is_string($result) ? $result : 'send skipped or failed';
            if ($stats['firstError'] === null) {
                $stats['firstError'] = $message;
            }

            if ($logErrors && method_exists($xpdo, 'log')) {
                $xpdo->log(
                    self::logLevelError(),
                    '[Sendex] queue id ' . $queue->get('id') . ': ' . $message
                );
            }

            if ($stopOnError) {
                break;
            }
        }

        return $stats;
    }

    /**
     * @param object $queue sxQueue
     * @return true|string
     */
    public static function deliverMail($queue)
    {
        $message = sxNewsletterMailer::messageFromQueue($queue);
        if ($message === false) {
            return $queue->xpdo->lexicon('sendex_newsletter_err_no_template');
        }

        /** @var modPHPMailer $mail */
        $mail = sxModxCompat::getMail($queue->xpdo);
        sxNewsletterMailer::configureMailer($mail, $message);
        if (!$mail->send()) {
            $error = $mail->mailer->ErrorInfo;
            $queue->xpdo->log(
                xPDO::LOG_LEVEL_ERROR,
                'An error occurred while trying to send the email: ' . $error
            );
            $mail->reset();

            return $error;
        }

        $mail->reset();

        return true;
    }

    /**
     * @return int
     */
    protected static function logLevelError()
    {
        if (class_exists('modX', false) && defined('modX::LOG_LEVEL_ERROR')) {
            return constant('modX::LOG_LEVEL_ERROR');
        }
        if (defined('xPDO::LOG_LEVEL_ERROR')) {
            return constant('xPDO::LOG_LEVEL_ERROR');
        }

        return 3;
    }
}
