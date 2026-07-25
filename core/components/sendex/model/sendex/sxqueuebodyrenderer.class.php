<?php

require_once dirname(__FILE__) . '/sxnewsletterqueueusers.class.php';

/**
 * Render queue email body at send time when row has no stored HTML (#64).
 *
 * Legacy rows with non-empty `email_body` are sent as-is via sxNewsletterMailer.
 */
class sxQueueBodyRenderer
{
    /**
     * @param object $queue sxQueue-like
     * @return bool
     */
    public static function usesStoredBody($queue)
    {
        return trim((string) self::field($queue, 'email_body')) !== '';
    }

    /**
     * @param object $xpdo
     * @param object $queue sxQueue-like
     * @return string|false
     */
    public static function renderForQueue($xpdo, $queue)
    {
        $newsletterId = (int) self::field($queue, 'newsletter_id');
        /** @var sxNewsletter|null $newsletter */
        $newsletter = $xpdo->getObject('sxNewsletter', $newsletterId);
        if (!$newsletter) {
            return false;
        }

        $subscriber = self::resolveSubscriber($xpdo, $queue, $newsletterId);
        if (!$subscriber) {
            return false;
        }

        return self::render($xpdo, $newsletter, $subscriber);
    }

    /**
     * @param object $xpdo
     * @param object $newsletter sxNewsletter
     * @param object $subscriber sxSubscriber
     * @return string|false
     */
    public static function render($xpdo, $newsletter, $subscriber)
    {
        $templateId = (int) $newsletter->get('template');
        /** @var modTemplate|null $template */
        $template = $templateId > 0 ? $xpdo->getObject('modTemplate', $templateId) : null;
        if (!$template || !($template instanceof modTemplate)) {
            return false;
        }

        $scriptProperties = array(
            'newsletter' => $newsletter->toArray(),
            'subscriber' => $subscriber->toArray(),
        );

        $userId = (int) $subscriber->get('user_id');
        if ($userId > 0) {
            $contexts = sxNewsletterQueueUsers::loadContexts($xpdo, array($userId));
            if (isset($contexts['eligible'][$userId])) {
                $scriptProperties['user'] = $contexts['eligible'][$userId]['user'];
                $scriptProperties['profile'] = $contexts['eligible'][$userId]['profile'];
            } elseif (in_array($userId, $contexts['loadedIds'], true)) {
                return false;
            }
        }

        $template->_cacheable = false;
        $template->_processed = false;
        $template->_output = '';
        $body = $template->process($scriptProperties);

        /** @var modParser|null $parser */
        $parser = $xpdo->getService(
            'parser',
            $xpdo->getOption('parser_class', null, 'modParser'),
            $xpdo->getOption('parser_class_path', null, '')
        );
        if ($parser && $parser instanceof modParser) {
            $maxIterations = (int) $xpdo->getOption('parser_max_iterations', null, 10);
            $parser->processElementTags('', $body, true, true, '[[', ']]', array(), $maxIterations);
        }

        return $body;
    }

    /**
     * @param object $xpdo
     * @param object $queue sxQueue-like
     * @param int $newsletterId
     * @return sxSubscriber|null
     */
    protected static function resolveSubscriber($xpdo, $queue, $newsletterId)
    {
        $subscriberId = (int) self::field($queue, 'subscriber_id');
        if ($subscriberId > 0) {
            /** @var sxSubscriber|null $subscriber */
            $subscriber = $xpdo->getObject('sxSubscriber', $subscriberId);
            if ($subscriber) {
                return $subscriber;
            }
        }

        $email = trim((string) self::field($queue, 'email_to'));
        if ($email === '') {
            return null;
        }

        /** @var sxSubscriber $guest */
        $guest = $xpdo->newObject('sxSubscriber');
        $guest->fromArray(array(
            'newsletter_id' => $newsletterId,
            'user_id'       => 0,
            'email'         => $email,
        ), '', true, true);

        return $guest;
    }

    /**
     * @param object $source
     * @param string $key
     * @return mixed
     */
    protected static function field($source, $key)
    {
        if (is_object($source) && method_exists($source, 'get')) {
            return $source->get($key);
        }

        return '';
    }
}
