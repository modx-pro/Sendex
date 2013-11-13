<?php

require_once dirname(__FILE__) . '/model/sendex/sendex.class.php';

/**
 * Class SendexMainController
 */
abstract class SendexMainController extends modExtraManagerController {
	/** @var Sendex $Sendex */
	public $Sendex;


	/**
	 * @return void
	 */
	public function initialize() {
		$this->Sendex = new Sendex($this->modx);

		$this->modx->regClientCSS($this->Sendex->config['cssUrl'] . 'mgr/main.css');
		$this->modx->regClientStartupScript($this->Sendex->config['jsUrl'] . 'mgr/sendex.js');
		$this->modx->regClientStartupHTMLBlock('<script type="text/javascript">
		Ext.onReady(function() {
			Sendex.config = ' . $this->modx->toJSON($this->Sendex->config) . ';
			Sendex.config.connector_url = "' . $this->Sendex->config['connectorUrl'] . '";
		});
		</script>');

		parent::initialize();
	}


	/**
	 * @return array
	 */
	public function getLanguageTopics() {
		return array('sendex:default');
	}


	/**
	 * @return bool
	 */
	public function checkPermissions() {
		return true;
	}
}


/**
 * Class IndexManagerController
 */
class IndexManagerController extends SendexMainController {

	/**
	 * @return string
	 */
	public static function getDefaultController() {
		return 'home';
	}
}