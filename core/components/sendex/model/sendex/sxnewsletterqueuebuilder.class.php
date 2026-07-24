<?php

require_once dirname(__FILE__) . '/sxqueuelink.class.php';
require_once dirname(__FILE__) . '/sxnewslettermailer.class.php';

/**
 * Build queue rows from newsletter subscribers.
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
        $params = $newsletter->toArray();
        /** @var modParser $parser */
        $parser = $xpdo->getService(
            'parser',
            $xpdo->getOption('parser_class', null, 'modParser'),
            $xpdo->getOption('parser_class_path', null, '')
        );

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

        /** @var sxSubscriber $subscriber */
        foreach ($subscribers as $subscriber) {
            $scriptProperties = array(
                'newsletter' => $params,
                'subscriber' => $subscriber->toArray(),
            );

            /** @var modUser $user */
            if ($subscriber->get('user_id') && $user = $xpdo->getObject('modUser', $subscriber->user_id)) {
                /** @var modUserProfile|null $profile */
                $profile = $user->getOne('Profile');

                if (!$profile || !$user->active || $profile->blocked) {
                    continue;
                }
                $scriptProperties['user'] = $user->toArray();
                $scriptProperties['profile'] = $profile->toArray();
            }

            $email = $subscriber->email;
            $subject = !empty($newsletter->email_subject) ? $newsletter->email_subject : 'No subject';

            $template->_cacheable = false;
            $template->_processed = false;
            $template->_output = '';
            $body = $template->process($scriptProperties);

            if ($parser && $parser instanceof modParser) {
                $maxIterations = (int) $xpdo->getOption('parser_max_iterations', null, 10);
                $parser->processElementTags('', $body, true, true, '[[', ']]', array(), $maxIterations);
            }

            $message = sxNewsletterMailer::buildMessage($newsletter, $subscriber, $subject, $body, $xpdo);

            /** @var sxQueue $queue */
            $queue = $xpdo->newObject('sxQueue');
            $queue->fromArray(array(
                'subscriber_id'   => sxQueueLink::subscriberIdFromSubscriber($subscriber),
                'newsletter_id'   => $newsletterId,
                'email_to'        => $message['email_to'],
                'email_subject'   => $message['email_subject'],
                'email_body'      => $message['email_body'],
                'email_from'      => $message['email_from'],
                'email_from_name' => $message['email_from_name'],
                'email_reply'     => $message['email_reply'],
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
