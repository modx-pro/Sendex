<?php
/** @var array $scriptProperties */
/** @var Sendex $Sendex */
$Sendex = $modx->getService('sendex','Sendex',$modx->getOption('sendex_core_path',null,$modx->getOption('core_path').'components/sendex/').'model/sendex/',$scriptProperties);
/** @var pdoTools $pdoTools */
$pdoTools = $modx->getService('pdoTools');

if (!($Sendex instanceof Sendex) || !($pdoTools instanceof pdoTools)) return '';

if (empty($tplActivate)) {$tplActivate = '@INLINE [[+link]]';}
if (empty($linkTTL)) {$linkTTL = 1800;}

if (!$modx->user->isAuthenticated($modx->context->key)) {
	return $modx->lexicon('sendex_err_auth_req');
}
elseif (empty($id) || !$newsletter = $modx->getObject('sxNewsletter', $id)) {
	return $modx->lexicon('sendex_newsletter_err_ns');
}

/** @var sxNewsletter $newsletter */
if (!$newsletter->active && empty($showInactive)) {
	return $modx->lexicon('sendex_newsletter_err_disabled');
}

$placeholders = $newsletter->toArray();
$placeholders['message'] = '';
$placeholders['error'] = 0;
if ($modx->user->isAuthenticated($modx->context->key)) {
	$placeholders = array_merge(
		$modx->user->toArray(),
		$modx->user->Profile->toArray(),
		$placeholders
	);
}


if (!empty($_REQUEST['sx_action'])) {
	$params = $_GET;
	unset($params[$modx->getOption('request_param_alias')]);
	unset($params[$modx->getOption('request_param_id')]);

	switch ($_REQUEST['sx_action']) {
		case 'subscribe':
			if (!empty($_REQUEST['email'])) {
				$email = htmlentities(strip_tags(urldecode($_REQUEST['email'])));
				$response = $newsletter->checkEmail($email, $modx->user->id, $linkTTL);
				if ($response === true) {
					$placeholders['message'] = $modx->lexicon('sendex_subscribe_err_already');
				}
				elseif ($response === false) {
					$placeholders['message'] = $modx->lexicon('sendex_subscribe_err_email_wrong');
					$placeholders['error'] = 1;
				}
				else {
					$params['hash'] = $response;
					$params['sx_action'] = 'confirm';
					$placeholders['link'] = $modx->makeUrl($modx->resource->id, $modx->context->key, $params, 'full');
					$placeholders['email_body'] = $pdoTools->getChunk($tplActivate, $placeholders);
					$Sendex->sendEmail($email, $placeholders);
				}
			}
			else {
				$placeholders['message'] = $modx->lexicon('sendex_subscribe_err_email_ns');
				$placeholders['error'] = 1;
			}
			unset($params['email'], $params['hash']);
			break;
		case 'confirm':
			if (!empty($_REQUEST['hash'])) {
				$response = $newsletter->confirmEmail($_REQUEST['hash']);
				unset($params['hash']);
			}
			break;
		case 'unsubscribe':
			if (!empty($_REQUEST['code'])) {
				$response = $newsletter->unSubscribe($_REQUEST['code']);
			}
			unset($params['code']);
			break;
	}

	unset($params['sx_action']);
	if (empty($placeholders['message'])) {
		$modx->sendRedirect($modx->makeUrl($modx->resource->id, $modx->context->key, $params, 'full'));
	}
}


if ($id = $newsletter->isSubscribed($modx->user->id)) {
	if ($subscriber = $modx->getObject('sxSubscriber', $id)) {
		$placeholders = array_merge($subscriber->toArray(), $placeholders);
	}
	return !empty($tplUnsubscribeForm)
		? $pdoTools->getChunk($tplUnsubscribeForm, $placeholders)
		: 'Parameter "tplUnsubscribeForm" is empty';
}
else {
	return !empty($tplSubscribeForm)
		? $pdoTools->getChunk($tplSubscribeForm, $placeholders)
		: 'Parameter "tplSubscribeForm" is empty';
}