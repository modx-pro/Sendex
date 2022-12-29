<?php

/**
 * The base class for Sendex.
 */

class Sendex
{
	/* @var modX $modx */
	public $modx;
	/* @var SendexControllerRequest $request */
	protected $request;
	public $initialized = array();
	public $chunks = array();


	/**
	 * @param modX $modx
	 * @param array $config
	 */
	function __construct(modX &$modx, array $config = array())
	{
		$this->modx = &$modx;

		$corePath = $this->modx->getOption('sendex_core_path', $config, $this->modx->getOption('core_path') . 'components/sendex/');
		$assetsUrl = $this->modx->getOption('sendex_assets_url', $config, $this->modx->getOption('assets_url') . 'components/sendex/');
		$connectorUrl = $assetsUrl . 'connector.php';

		$this->config = array_merge(array(
			'assetsUrl' => $assetsUrl,
			'cssUrl' => $assetsUrl . 'css/',
			'jsUrl' => $assetsUrl . 'js/',
			'imagesUrl' => $assetsUrl . 'images/',
			'connectorUrl' => $connectorUrl,

			'corePath' => $corePath,
			'modelPath' => $corePath . 'model/',
			'chunksPath' => $corePath . 'elements/chunks/',
			'templatesPath' => $corePath . 'elements/templates/',
			'chunkSuffix' => '.chunk.tpl',
			'snippetsPath' => $corePath . 'elements/snippets/',
			'processorsPath' => $corePath . 'processors/',
			'hideExportButton' => (bool)$this->modx->getOption('sendex_hide_export_button', null, false)
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
		$mail = $this->modx->getService('mail', 'mail.modPHPMailer');

		$mail->set(modMail::MAIL_BODY, $this->modx->getOption('email_body', $options, ''));
		$mail->set(modMail::MAIL_FROM, $this->modx->getOption('email_from', $options, $this->modx->getOption('emailsender'), true));
		$mail->set(modMail::MAIL_FROM_NAME, $this->modx->getOption('email_from_name', $options, $this->modx->getOption('site_name'), true));
		$mail->set(modMail::MAIL_SUBJECT, $this->modx->getOption('email_subject', $options, $this->modx->lexicon('sendex_subscribe_activate_subject'), true));

		$mail->address('to', $email);
		$mail->address('reply-to', $this->modx->getOption('email_from', $options, $this->modx->getOption('emailsender'), true));
		$mail->setHTML(true);

		$response = !$mail->send()
			? $mail->mailer->errorInfo
			: true;
		$mail->reset();

		return $response;
	}
}
