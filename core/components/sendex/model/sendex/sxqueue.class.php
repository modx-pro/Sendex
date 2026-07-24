<?php

require_once dirname(__FILE__) . '/sxqueuedeliver.class.php';

class sxQueue extends xPDOSimpleObject
{
    /** {inheritDoc} */
    public function save($cacheFlag = null)
    {
        if ($this->get('email_to') == '') {
            return false;
        }

        $hash = sha1(serialize(array(
            'subscriber_id'   => $this->subscriber_id,
            'newsletter_id'   => $this->newsletter_id,
            'email_to'        => $this->email_to,
            'email_subject'   => $this->email_subject,
            'email_body'      => $this->email_body,
            'email_from'      => $this->email_from,
            'email_from_name' => $this->email_from_name,
            'email_reply'     => $this->email_reply,
        )));

        if (!$this->xpdo->getCount('sxQueue', array('hash' => $hash))) {
            $this->set('hash', $hash);
            return parent::save($cacheFlag);
        } else {
            return true;
        }
    }


    /**
     * Sends an email to subscriber
     *
     * @return bool|string
     */
    public function send()
    {
        $queue = $this;

        return sxQueueDeliver::send($this, function () use ($queue) {
            /** @var modPHPMailer $mail */
            $mail = $queue->xpdo->getService('mail', 'mail.modPHPMailer');
            $mail->set(modMail::MAIL_BODY, $queue->email_body);
            $mail->set(modMail::MAIL_FROM, $queue->email_from);
            $mail->set(modMail::MAIL_FROM_NAME, $queue->email_from_name);
            $mail->set(modMail::MAIL_SUBJECT, $queue->email_subject);
            $mail->address('to', $queue->email_to);
            $mail->address('reply-to', $queue->email_reply);
            $mail->setHTML(true);
            if (!$mail->send()) {
                $queue->xpdo->log(
                    xPDO::LOG_LEVEL_ERROR,
                    'An error occurred while trying to send the email: '
                        . $mail->mailer->ErrorInfo
                );
                $mail->reset();
                return $mail->mailer->ErrorInfo;
            }

            $mail->reset();
            return true;
        });
    }
}
