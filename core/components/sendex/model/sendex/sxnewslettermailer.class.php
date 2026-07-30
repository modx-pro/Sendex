<?php

require_once dirname(__FILE__) . '/sxqueuebodyrenderer.class.php';

/**
 * Shared newsletter mail headers + PHPMailer setup (#66).
 *
 * Post-#62 mail paths (issue originally named sxNewsletter::send/checkEmail):
 * - activation: snippet → Sendex::sendEmail → buildActivationMessage
 * - queue build: sxNewsletterQueueBuilder::addQueues → compact row (headers only)
 * - queue send: sxQueueSender::deliverMail → messageFromQueue (+ sxQueueBodyRenderer when body empty).
 *   From/Reply headers come from the linked newsletter at send time (#123), not the queue snapshot.
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
        $from = self::sanitizeHeader(self::readField($source, 'email_from'));
        if ($from === '') {
            $from = self::sanitizeHeader($xpdo->getOption('emailsender'));
        }

        $fromName = self::sanitizeHeader(self::readField($source, 'email_from_name'));
        if ($fromName === '') {
            $fromName = self::sanitizeHeader($xpdo->getOption('site_name'));
        }

        $reply = self::sanitizeHeader(self::readField($source, 'email_reply'));
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
                'email_to'      => self::sanitizeHeader($subscriber->get('email')),
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
            'email_to'        => self::sanitizeHeader($to),
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

        $headers = self::resolveHeaders($queue, $queue->xpdo);
        $newsletterId = (int) self::readField($queue, 'newsletter_id');
        if ($newsletterId > 0) {
            $newsletter = $queue->xpdo->getObject('sxNewsletter', $newsletterId);
            if ($newsletter) {
                $headers = self::resolveHeaders($newsletter, $queue->xpdo);
            }
        }

        return array(
            'email_to'        => self::sanitizeHeader(self::readField($queue, 'email_to')),
            'email_body'      => $body,
            'email_from'      => $headers['email_from'],
            'email_from_name' => $headers['email_from_name'],
            'email_subject'   => self::readField($queue, 'email_subject'),
            'email_reply'     => $headers['email_reply'],
        );
    }

    /**
     * @param object $mail modPHPMailer
     * @param array $message
     */
    public static function configureMailer($mail, array $message)
    {
        $mail->set(sxModxCompat::mailConst('BODY'), $message['email_body']);
        $mail->set(sxModxCompat::mailConst('FROM'), $message['email_from']);
        $mail->set(sxModxCompat::mailConst('FROM_NAME'), $message['email_from_name']);
        $mail->set(sxModxCompat::mailConst('SUBJECT'), $message['email_subject']);
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

    /**
     * Remove CR/LF and control chars from email header fields (#103 P3).
     *
     * @param mixed $value
     * @return string
     */
    public static function sanitizeHeader($value)
    {
        $value = (string) $value;
        $value = preg_replace('/[\r\n]+/', ' ', $value);
        if ($value === null) {
            return '';
        }
        $value = preg_replace('/[\x00-\x1F\x7F]/', '', $value);
        if ($value === null) {
            return '';
        }

        return trim($value);
    }
}
