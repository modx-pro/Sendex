<?php

require_once dirname(__FILE__, 4) . '/model/sendex/sxnewsletterlistquery.class.php';

/**
 * Get a list of Newsletters
 */
class sxNewsletterGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'sxNewsletter';
    public $classKey = 'sxNewsletter';
    public $defaultSortField = 'id';
    public $defaultSortDirection = 'DESC';
    public $permission = 'view_document';

    /**
     * Match mgr controller ACL: Sendex menu may grant view_sendex without view_document.
     *
     * @return bool
     */
    public function checkPermissions()
    {
        return $this->modx->hasPermission('view_sendex')
            || $this->modx->hasPermission('view_document');
    }

    /**
     * @param xPDOQuery $c
     *
     * @return xPDOQuery
     */
    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        return sxNewsletterListQuery::applyFilters($c, array(
            'query' => $this->getProperty('query'),
            'combo' => $this->getProperty('combo'),
        ));
    }

    /**
     * @param xPDOObject $object
     *
     * @return array
     */
    public function prepareRow(xPDOObject $object)
    {
        $array = sxNewsletterListQuery::enrichRow($this->modx, $object->toArray());
        $array['actions'] = array();

        // Update
        $array['actions'][] = array(
            'class'  => '',
            'button' => true,
            'menu'   => true,
            'icon'   => 'edit',
            'type'   => 'updateNewsletter',
        );
        // Disable
        if (empty($array['active'])) {
            $array['actions'][] = array(
                'class'  => '',
                'button' => true,
                'menu'   => true,
                'icon'   => 'check-circle-o',
                'type'   => 'enableNewsletter',
            );
        } else {
            // Enable
            $array['actions'][] = array(
                'class'  => '',
                'button' => true,
                'menu'   => true,
                'icon'   => 'power-off',
                'type'   => 'disableNewsletter',
            );
        }
        // Send to all subscribers (#29)
        $array['actions'][] = array(
            'class'  => '',
            'button' => true,
            'menu'   => true,
            'icon'   => 'send',
            'type'   => 'sendNewsletter',
        );
        // Remove
        $array['actions'][] = array(
            'class'  => '',
            'button' => true,
            'menu'   => true,
            'icon'   => 'trash-o',
            'type'   => 'removeNewsletter',
        );

        return $array;
    }
}

return 'sxNewsletterGetListProcessor';
