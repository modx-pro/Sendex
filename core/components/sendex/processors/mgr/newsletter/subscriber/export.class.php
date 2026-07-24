<?php

/**
 * Export subscribers as CSV through the authenticated mgr connector.
 * Does not write files under assets/ or return a public URL.
 */

require_once dirname(__FILE__, 5) . '/model/sendex/sxsubscribercsv.class.php';

class sxSubscribersExportProcessor extends modObjectProcessor
{
    public $objectType = 'sxSubscriber';
    public $classKey = 'sxSubscriber';
    public $languageTopics = array('sendex');
    /** Matches other newsletter mutation processors; blocks anonymous connector calls. */
    public $permission = 'edit_document';


    /**
     * @return array|string
     */
    public function process()
    {
        $newsletterId = (int) $this->getProperty('newsletter_id');
        if (!$newsletterId) {
            return $this->failure($this->modx->lexicon('sendex_newsletter_err_ns'));
        }

        $fields = sxSubscriberCsv::resolveFields(
            $this->modx->getOption('sendex_export_fields', null, 'email', true)
        );
        if (empty($fields)) {
            return $this->failure($this->modx->lexicon('sendex_subscribers_export_fields_err'));
        }

        $rows = $this->fetchRows($newsletterId, $fields);
        if ($rows === null) {
            return $this->failure($this->modx->lexicon('sendex_subscribers_export_error'));
        }

        $filename = 'subscribers_' . date('Ymd-His') . '.csv';

        return $this->success('', array(
            'filename' => $filename,
            'csv' => sxSubscriberCsv::encode($rows),
        ));
    }


    /**
     * @param int $newsletterId
     * @param string[] $fields
     * @return array[]|null Null when the query cannot be executed
     */
    protected function fetchRows($newsletterId, array $fields)
    {
        $q = $this->modx->newQuery($this->classKey);
        $q->select(sxSubscriberCsv::selectColumns($fields, $this->classKey));

        if (sxSubscriberCsv::needsUserJoin($fields)) {
            $q->leftJoin('modUser', 'User', '`User`.`id`=`sxSubscriber`.`user_id`');
        }
        if (sxSubscriberCsv::needsProfileJoin($fields)) {
            $q->leftJoin(
                'modUserProfile',
                'Profile',
                'Profile.internalKey = sxSubscriber.user_id'
            );
        }

        $q->where(array('sxSubscriber.newsletter_id' => $newsletterId));
        if (!$q->prepare() || !is_object($q->stmt) || !$q->stmt->execute()) {
            return null;
        }

        $rows = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
        return is_array($rows) ? $rows : array();
    }
}

return 'sxSubscribersExportProcessor';
