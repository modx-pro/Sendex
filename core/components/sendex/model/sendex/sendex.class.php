<?php

require_once dirname(__FILE__) . '/sxnewslettermailer.class.php';

/**
 * The base class for Sendex.
 */

class Sendex
{
    /* @var modX $modx */
    public $modx;
    /* @var SendexControllerRequest $request */
    protected $request;
    /** @var array */
    public $config = array();
    public $initialized = array();
    public $chunks = array();


    /**
     * @param modX $modx
     * @param array $config
     */
    public function __construct(modX &$modx, array $config = array())
    {
        $this->modx = &$modx;

        $corePath = $this->modx->getOption(
            'sendex_core_path',
            $config,
            $this->modx->getOption('core_path') . 'components/sendex/'
        );
        if (is_file($corePath . 'bootstrap.php')) {
            require_once $corePath . 'bootstrap.php';
            sendexRegisterAutoload($corePath);
        }
        $assetsUrl = $this->modx->getOption(
            'sendex_assets_url',
            $config,
            $this->modx->getOption('assets_url') . 'components/sendex/'
        );
        $connectorUrl = $assetsUrl . 'connector.php';

        $this->config = array_merge(array(
            'assetsUrl'        => $assetsUrl,
            'cssUrl'           => $assetsUrl . 'css/',
            'jsUrl'            => $assetsUrl . 'js/',
            'imagesUrl'        => $assetsUrl . 'images/',
            'connectorUrl'     => $connectorUrl,

            'corePath'         => $corePath,
            'modelPath'        => $corePath . 'model/',
            'chunksPath'       => $corePath . 'elements/chunks/',
            'templatesPath'    => $corePath . 'elements/templates/',
            'chunkSuffix'      => '.chunk.tpl',
            'snippetsPath'     => $corePath . 'elements/snippets/',
            'processorsPath'   => $corePath . 'processors/',
            'hideExportButton' => (bool) $this->modx->getOption(
                'sendex_hide_export_button',
                null,
                false
            ),
        ), $config);

        $this->modx->addPackage('sendex', $this->config['modelPath']);
        $this->modx->lexicon->load('sendex:default');
    }


    /**
     * Sends email with activation link
     *
     * @param $email
     * @param array $options
     *
     * @return string|bool
     */
    public function sendEmail($email, array $options = array())
    {
        /** @var modPHPMailer $mail */
        $mail = sxModxCompat::getMail($this->modx);
        sxNewsletterMailer::configureMailer(
            $mail,
            sxNewsletterMailer::buildActivationMessage($this->modx, $email, $options)
        );

        $response = !$mail->send()
            ? $mail->mailer->ErrorInfo
            : true;
        $mail->reset();

        return $response;
    }
}
