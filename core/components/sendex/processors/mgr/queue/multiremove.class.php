<?php
class sxQueueMultyRemoveProcessor extends modProcessor {
	public $checkRemovePermission = true;
	public $objectType = 'sxQueue';
	public $classKey = 'sxQueue';
	public $languageTopics = array('modextra');

	public function process() {

        $processorProps = array('processors_path' => dirname(__FILE__) . '/');
        foreach (explode(',',$this->getProperty('queues')) as $id) {
            $response = $this->modx->runProcessor('remove', array('id' => $id), $processorProps);
            if ($response->isError()) {
                return $response->response;
            }
        }
        return $this->success();

	}

}

return 'sxQueueMultyRemoveProcessor';