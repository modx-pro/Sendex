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
		$this->modx->regClientStartupScript($this->Sendex->config['jsUrl'] . 'mgr/widgets/items.grid.js');
		$this->modx->regClientStartupScript($this->Sendex->config['jsUrl'] . 'mgr/widgets/home.panel.js');
		$this->modx->regClientStartupScript($this->Sendex->config['jsUrl'] . 'mgr/sections/home.js');
	}


	/**
	 * @return string
	 */
	public function getTemplateFile() {
		return $this->Sendex->config['templatesPath'] . 'home.tpl';
	}
}