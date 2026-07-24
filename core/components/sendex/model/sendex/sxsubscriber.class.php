<?php

require_once dirname(__FILE__) . '/sxsubscribercode.class.php';

class sxSubscriber extends xPDOSimpleObject
{
    /**
     * @param null $cacheFlag
     * @return bool
     */
    public function save($cacheFlag = null)
    {
        if (sxSubscriberCode::needsNewCode($this->get('code'))) {
            $this->set(
                'code',
                sxSubscriberCode::generate($this->user_id, $this->newsletter_id, $this->email)
            );
        }

        return parent::save($cacheFlag);
    }
}
