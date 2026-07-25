<?php

require_once dirname(__FILE__) . '/sxqueuebodyrenderer.class.php';

/**
 * Shared newsletter mail headers + PHPMailer setup (#66).
 *
 * Post-#62 mail paths (issue originally named sxNewsletter::send/checkEmail):
 * - activation: snippet → Sendex::sendEmail → buildActivationMessage
 * - queue build: sxNewsletterQueueBuilder::addQueues → compact row (headers only)
 * - queue send: sxQueueSender::deliverMail → messageFromQueue (+ sxQueueBodyRenderer when body empty)
 */
class sxNewsletterMailer
{
    /**
     * Resolve From / Reply-To defaults from newsletter fields or site options.
     *
     * @param object|array $source sxNewsletter, options array, or queue-like row
     * @param object $xpdo
     * @return array{email_from:string,email_from_name:string,email_reply:string}
     */
    public static function resolveHeaders($source, $xpdo)
    {
        $from = trim((string) self::readField($source, 'email_from'));
        if ($from === '') {
            $from = (string) $xpdo->getOption('emailsender');
        }

        $fromName = trim((string) self::readField($source, 'email_from_name'));
        if ($fromName === '') {
            $fromName = (string) $xpdo->getOption('site_name');
        }

        $reply = trim((string) self::readField($source, 'email_reply'));
        if ($reply === '') {
            $reply = $from;
        }

        return array(
            'email_from'      => $from,
            'email_from_name' => $fromName,
            'email_reply'     => $reply,
        );
    }

    /**
     * Build queue/send payload for one subscriber.
     *
     * @param object $newsletter sxNewsletter
     * @param object $subscriber sxSubscriber
     * @param string $subject
     * @param string $body
     * @param object $xpdo
     * @return array
     */
    public static function buildMessage($newsletter, $subscriber, $subject, $body, $xpdo)
    {
        return array_merge(
            self::resolveHeaders($newsletter, $xpdo),
            array(
                'email_to'      => $subscriber->get('email'),
                'email_subject' => $subject,
                'email_body'    => $body,
            )
        );
    }

    /**
     * Activation mail after checkEmail (snippet → Sendex::sendEmail).
     *
     * @param object $xpdo
     * @param string $to
     * @param array $options newsletter placeholders + email_body
     * @return array
     */
    public static function buildActivationMessage($xpdo, $to, array $options)
    {
        $headers = self::resolveHeaders($options, $xpdo);

        return array(
            'email_to'        => $to,
            'email_body'      => $xpdo->getOption('email_body', $options, ''),
            'email_subject'   => $xpdo->getOption(
                'email_subject',
                $options,
                $xpdo->lexicon('sendex_subscribe_activate_subject'),
                true
            ),
            'email_from'      => $headers['email_from'],
            'email_from_name' => $headers['email_from_name'],
            'email_reply'     => $headers['email_reply'],
        );
    }

    /**
     * @param object $queue sxQueue
     * @return array|false
     */
    public static function messageFromQueue($queue)
    {
        $body = (string) self::readField($queue, 'email_body');
        if (!sxQueueBodyRenderer::usesStoredBody($queue)) {
            $rendered = sxQueueBodyRenderer::renderForQueue($queue->xpdo, $queue);
            if ($rendered === false) {
                return false;
            }
            $body = $rendered;
        }

        return array(
            'email_to'        => self::readField($queue, 'email_to'),
            'email_body'      => $body,
            'email_from'      => self::readField($queue, 'email_from'),
            'email_from_name' => self::readField($queue, 'email_from_name'),
            'email_subject'   => self::readField($queue, 'email_subject'),
            'email_reply'     => self::readField($queue, 'email_reply'),
        );
    }

    /**
     * @param object $mail modPHPMailer
     * @param array $message
     */
    public static function configureMailer($mail, array $message)
    {
        $mail->set(modMail::MAIL_BODY, $message['email_body']);
        $mail->set(modMail::MAIL_FROM, $message['email_from']);
        $mail->set(modMail::MAIL_FROM_NAME, $message['email_from_name']);
        $mail->set(modMail::MAIL_SUBJECT, $message['email_subject']);
        $mail->address('to', $message['email_to']);
        $mail->address('reply-to', $message['email_reply']);
        $mail->setHTML(true);
    }

    /**
     * @param object|array $source
     * @param string $key
     * @return mixed
     */
    protected static function readField($source, $key)
    {
        if (is_array($source)) {
            return isset($source[$key]) ? $source[$key] : '';
        }

        if (is_object($source) && method_exists($source, 'get')) {
            return $source->get($key);
        }

        return '';
    }
}
