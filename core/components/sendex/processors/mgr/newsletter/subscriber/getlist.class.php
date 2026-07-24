<?php

/**
 * Get a list of Subscribers
 */

require_once dirname(dirname(__DIR__)) . '/likequery.class.php';

class sxSubscriberGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'sxSubscriber';
    public $classKey = 'sxSubscriber';
    public $defaultSortField = 'id';
    public $defaultSortDirection = 'DESC';
    public $permission = 'view_document';


    /**
     * @param xPDOQuery $c
     *
     * @return xPDOQuery
     */
    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $c->where(array('newsletter_id' => $this->getProperty('newsletter_id')));
        $c->leftJoin('modUser', 'modUser', 'sxSubscriber.user_id = modUser.id');
        $c->leftJoin('modUserProfile', 'modUserProfile', 'sxSubscriber.user_id = modUserProfile.internalKey');

        $c->select($this->modx->getSelectColumns($this->classKey, $this->classKey));
        $c->select('modUser.username, modUserProfile.fullname');

        $like = SendexLikeQuery::prepare($this->getProperty('query', ''));
        if ($like !== null) {
            $c->where(array(
                'email:LIKE' => $like,
                'OR:modUser.username:LIKE' => $like,
            ));
        }

        return $c;
    }


    /** {@inheritDoc} */
    public function prepareRow(xPDOObject $object)
    {
        $array = $object->toArray();

        if (empty($array['username'])) {
            $array['username'] = 'Guest';
        }
        if (empty($array['fullname'])) {
            $array['fullname'] = 'Anonymous';
        }

        $array['actions'] = array();
        // Remove
        $array['actions'][] = array(
            'class'  => '',
            'button' => true,
            'menu'   => true,
            'icon'   => 'trash-o',
            'type'   => 'removeSubscriber',
        );

        return $array;
    }
}

return 'sxSubscriberGetListProcessor';
