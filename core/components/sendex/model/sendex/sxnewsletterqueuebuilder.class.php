<?php

require_once dirname(__FILE__) . '/sxqueuelink.class.php';

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
     * @return bool|mixed|string
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
            $from = !empty($newsletter->email_from)
                ? $newsletter->email_from
                : $xpdo->getOption('emailsender');
            $fromName = !empty($newsletter->email_from_name)
                ? $newsletter->email_from_name
                : $xpdo->getOption('site_name');
            $emailReply = !empty($newsletter->email_reply) ? $newsletter->email_reply : $from;

            $template->_cacheable = false;
            $template->_processed = false;
            $template->_output = '';
            $body = $template->process($scriptProperties);

            if ($parser && $parser instanceof modParser) {
                $maxIterations = (int) $xpdo->getOption('parser_max_iterations', null, 10);
                $parser->processElementTags('', $body, true, true, '[[', ']]', array(), $maxIterations);
            }

            /** @var sxQueue $queue */
            $queue = $xpdo->newObject('sxQueue');
            $queue->fromArray(array(
                'subscriber_id'   => sxQueueLink::subscriberIdFromSubscriber($subscriber),
                'newsletter_id'   => $newsletter->id,
                'email_to'        => $email,
                'email_subject'   => $subject,
                'email_body'      => $body,
                'email_from'      => $from,
                'email_from_name' => $fromName,
                'email_reply'     => $emailReply,
            ));
            $queue->save();
        }

        return true;
    }
}
