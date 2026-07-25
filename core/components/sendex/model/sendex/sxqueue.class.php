<?php

require_once dirname(__FILE__) . '/sxqueuesender.class.php';

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
     * Send queue email (delegates to sxQueueSender; compact body rendered at send #64).
     *
     * @return true|false|string
     */
    public function send()
    {
        return sxQueueSender::sendOne($this);
    }
}
