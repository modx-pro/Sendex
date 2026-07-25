<?php

/**
 * Class SendexMainController
 */

abstract class SendexMainController extends modExtraManagerController
{
    /** @var Sendex $Sendex */
    public $Sendex;


    /**
     * @return void
     */
    public function initialize()
    {
        $version = $this->modx->getVersionData();
        // MODx.modx23 = modern mgr UI (MODX 2.3+ and MODX 3.x), not a separate MODX 3 branch.
        $modx23 = !empty($version) && version_compare($version['full_version'], '2.3.0', '>=');
        if (!$modx23) {
            $this->addCss(MODX_ASSETS_URL . 'components/sendex/css/mgr/font-awesome.min.css');
        }
        $this->addCss(MODX_ASSETS_URL . 'components/sendex/css/mgr/bootstrap.buttons.min.css');

        $corePath = $this->modx->getOption(
            'sendex_core_path',
            null,
            $this->modx->getOption('core_path') . 'components/sendex/'
        );
        require_once $corePath . 'bootstrap.php';

        $this->Sendex = sendexBootstrap($this->modx);
        $this->addCss($this->Sendex->config['cssUrl'] . 'mgr/main.css');
        $this->addJavascript($this->Sendex->config['jsUrl'] . 'mgr/sendex.js');
        $this->addJavascript($this->Sendex->config['jsUrl'] . 'mgr/misc/utils.js');
        $this->addHtml('<script type="text/javascript">
        Ext.onReady(function() {
            MODx.modx23 = ' . (int) $modx23 . ';
            Sendex.config = ' . $this->modx->toJSON($this->Sendex->config) . ';
            Sendex.config.connector_url = "' . $this->Sendex->config['connectorUrl'] . '";
        });
        </script>');

        parent::initialize();
    }


    /**
     * @return array
     */
    public function getLanguageTopics()
    {
        return array('sendex:default');
    }


    /**
     * @return bool
     */
    public function checkPermissions()
    {
        return true;
    }
}
