<?php

require_once dirname(__FILE__) . '/sxqueuesender.class.php';

/**
 * Queue + send workflow for a newsletter (#29).
 */
class sxNewsletterDispatch
{
    /**
     * Add queue rows for subscribers, then flush pending rows for this newsletter.
     *
     * When addQueues creates nothing but queue rows already exist (re-send), flush still runs.
     *
     * @param sxNewsletter $newsletter
     * @param array $options Optional sxQueueSender::flush options (e.g. sendFn for tests)
     * @return array{success:bool,message:string,queued:int,sent:int,skipped:int,failed:int}
     */
    public static function queueAndSend(sxNewsletter $newsletter, array $options = array())
    {
        $xpdo = $newsletter->xpdo;
        $newsletterId = (int) $newsletter->get('id');
        $queued = 0;

        $addResult = $newsletter->addQueues();
        if (is_int($addResult)) {
            $queued = $addResult;
        } elseif ((int) $xpdo->getCount('sxQueue', array('newsletter_id' => $newsletterId)) <= 0) {
            return self::failure($addResult, $queued);
        }

        $flushOptions = array(
            'criteria'    => array('newsletter_id' => $newsletterId),
            'stopOnError' => true,
        );
        if (isset($options['sendFn']) && is_callable($options['sendFn'])) {
            $flushOptions['sendFn'] = $options['sendFn'];
        }

        $stats = sxQueueSender::flush($xpdo, $flushOptions);
        if ($stats['firstError'] !== null) {
            return self::failure($stats['firstError'], $queued, $stats);
        }

        return array(
            'success'  => true,
            'message'  => $xpdo->lexicon('sendex_newsletter_send_success', array(
                'queued' => $queued,
                'sent'   => $stats['sent'],
            )),
            'queued'   => $queued,
            'sent'     => $stats['sent'],
            'skipped'  => $stats['skipped'],
            'failed'   => $stats['failed'],
        );
    }

    /**
     * @param string $message
     * @param int $queued
     * @param array|null $stats
     * @return array{success:bool,message:string,queued:int,sent:int,skipped:int,failed:int}
     */
    protected static function failure($message, $queued = 0, ?array $stats = null)
    {
        if ($stats === null) {
            $stats = array('sent' => 0, 'skipped' => 0, 'failed' => 0);
        }

        return array(
            'success'  => false,
            'message'  => $message,
            'queued'   => $queued,
            'sent'     => (int) $stats['sent'],
            'skipped'  => (int) $stats['skipped'],
            'failed'   => (int) $stats['failed'],
        );
    }
}
