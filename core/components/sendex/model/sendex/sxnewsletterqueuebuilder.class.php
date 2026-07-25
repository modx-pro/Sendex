<?php

require_once dirname(__FILE__) . '/sxqueuelink.class.php';
require_once dirname(__FILE__) . '/sxnewslettermailer.class.php';
require_once dirname(__FILE__) . '/sxnewsletterqueueusers.class.php';

/**
 * Build queue rows from newsletter subscribers.
 *
 * Post-#62 home of `sxNewsletter::addQueues` (issue #64 also named this path).
 * Send path: `sxQueue::send()` → `sxQueueSender` → compact body via `sxQueueBodyRenderer`.
 *
 * Compact mode (#64): new rows store empty `email_body` (no extra DB column); headers
 * and recipient are snapshotted; HTML is rendered at send time.
 */
class sxNewsletterQueueBuilder
{
    /** @var sxNewsletter */
    protected $newsletter;

    /**
     * @param sxNewsletter $newsletter
     */
    public function __construct(sxNewsletter $newsletter)
    {
        $this->newsletter = $newsletter;
    }

    /**
     * @return int|string Number of queue rows created, or lexicon error key/message
     */
    public function addQueues()
    {
        $newsletter = $this->newsletter;
        $xpdo = $newsletter->xpdo;
        $template = null;

        if (!$subscribers = $newsletter->getMany('Subscribers')) {
            return $xpdo->lexicon('sendex_newsletter_err_no_subscribers');
        }

        if ($tmp = $newsletter->get('template')) {
            /** @var modTemplate $template */
            $template = $xpdo->getObject('modTemplate', $tmp);
        }

        if (!$template || !($template instanceof modTemplate)) {
            return $xpdo->lexicon('sendex_newsletter_err_no_template');
        }

        $newsletterId = (int) $newsletter->id;
        $before = (int) $xpdo->getCount('sxQueue', array('newsletter_id' => $newsletterId));
        $subject = !empty($newsletter->email_subject) ? $newsletter->email_subject : 'No subject';
        $headers = sxNewsletterMailer::resolveHeaders($newsletter, $xpdo);

        $userIds = array();
        foreach ($subscribers as $subscriber) {
            $userId = (int) $subscriber->get('user_id');
            if ($userId > 0) {
                $userIds[] = $userId;
            }
        }
        $userContexts = sxNewsletterQueueUsers::loadContexts($xpdo, $userIds);

        /** @var sxSubscriber $subscriber */
        foreach ($subscribers as $subscriber) {
            $userId = (int) $subscriber->get('user_id');
            if (
                $userId > 0
                && in_array($userId, $userContexts['loadedIds'], true)
                && !isset($userContexts['eligible'][$userId])
            ) {
                continue;
            }

            /** @var sxQueue $queue */
            $queue = $xpdo->newObject('sxQueue');
            $queue->fromArray(array(
                'subscriber_id'   => sxQueueLink::subscriberIdFromSubscriber($subscriber),
                'newsletter_id'   => $newsletterId,
                'email_to'        => $subscriber->get('email'),
                'email_subject'   => $subject,
                'email_body'      => '',
                'email_from'      => $headers['email_from'],
                'email_from_name' => $headers['email_from_name'],
                'email_reply'     => $headers['email_reply'],
            ));
            $queue->save();
        }

        $created = (int) $xpdo->getCount('sxQueue', array('newsletter_id' => $newsletterId)) - $before;
        if ($created <= 0) {
            return $xpdo->lexicon('sendex_newsletter_err_no_queues');
        }

        return $created;
    }
}
