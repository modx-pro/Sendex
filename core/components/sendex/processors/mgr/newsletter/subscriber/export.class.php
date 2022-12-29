<?php

/**
 * Export Subscribers
 */
class sxSubscribersExportProcessor extends modObjectProcessor
{
    public $objectType = 'sxSubscriber';
    public $classKey = 'sxSubscriber';
    public $languageTopics = array('sendex');
    public $permission = '';


    /**
     * @return json
     */
    public function process()
    {

        if (!$newsletter_id = $this->getProperty('newsletter_id')) {
            return $this->failure($this->modx->lexicon('sendex_newsletter_err_ns'));
        }

        $assetsUrl = $this->modx->getOption('sendex_assets_url', null, $this->modx->getOption('assets_url') . 'components/sendex/', true);

        $optionFields = array_map('trim', explode(',', $this->modx->getOption('sendex_export_fields', null, 'email', true)));
        $allowedFields = ['id', 'user_id', 'email', 'username', 'fullname', 'phone', 'mobilephone'];
        $exportFields = array_intersect($optionFields, $allowedFields);
        $selectfields = [];

        $q = $this->modx->newQuery($this->classKey);

        foreach ($exportFields as $field) {
            if (in_array($field, ['fullname', 'phone', 'mobilephone'])) {
                $selectfields[] = 'Profile.' . $field;
            } elseif ($field == 'username') {
                $selectfields[] = 'User.' . $field;
            } else {
                $selectfields[] =  $this->classKey . '.' . $field;
            }
        }

        foreach ($exportFields as $field) {
            if ($field == 'username') {
                $q->leftJoin('modUser', 'User',  '`User`.`id`=`sxSubscriber`.`user_id`');
                break;
            }
        }

        foreach ($exportFields as $field) {
            if (in_array($field, ['fullname', 'phone', 'mobilephone'])) {
                $q->leftJoin('modUserProfile', 'Profile', 'Profile.internalKey = sxSubscriber.user_id');
                break;
            }
        }

        $q->select($selectfields);
        $q->where(array(
            'sxSubscriber.newsletter_id' => $newsletter_id
        ));

        $out = [
            'success' => true,
            'url' => $assetsUrl . 'subscribers.csv?' . time(),
        ];
        $fp = fopen('subscribers.csv', 'w');

        $q->prepare();
        $q->stmt->execute();
        $rows = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            fputcsv($fp, $row);
        }
        fclose($fp);

        return json_encode($out, 256);
    }
}

return 'sxSubscribersExportProcessor';
