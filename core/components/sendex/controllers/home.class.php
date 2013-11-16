<?php
/**
 * The home manager controller for Sendex.
 *
 */
class SendexHomeManagerController extends SendexMainController {
	/* @var Sendex $Sendex */
	public $Sendex;


	/**
	 * @param array $scriptProperties
	 */
	public function process(array $scriptProperties = array()) {
	}


	/**
	 * @return null|string
	 */
	public function getPageTitle() {
		return $this->modx->lexicon('sendex');
	}


	/**
	 * @return void
	 */
	public function loadCustomCssJs() {
		$this->addJavascript($this->Sendex->config['jsUrl'] . 'mgr/widgets/items.grid.js');
		$this->addJavascript($this->Sendex->config['jsUrl'] . 'mgr/widgets/home.panel.js');
		$this->addJavascript($this->Sendex->config['jsUrl'] . 'mgr/sections/home.js');
		$this->addHtml('<script type="text/javascript">
		Ext.onReady(function() {
			MODx.load({ xtype: "sendex-page-home"});
		});
		</script>');
	}


	/**
	 * @return string
	 */
	public function getTemplateFile() {
		return $this->Sendex->config['templatesPath'] . 'home.tpl';
	}
}