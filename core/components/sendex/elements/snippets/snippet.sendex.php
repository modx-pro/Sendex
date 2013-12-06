<?php
/** @var array $scriptProperties */
/** @var Sendex $Sendex */
$Sendex = $modx->getService('sendex','Sendex',$modx->getOption('sendex_core_path',null,$modx->getOption('core_path').'components/sendex/').'model/sendex/',$scriptProperties);
/** @var pdoTools $pdoTools */
$pdoTools = $modx->getService('pdoTools');

if (!($Sendex instanceof Sendex) || !($pdoTools instanceof pdoTools)) return '';

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

if ($newsletter->isSubscribed($modx->user->id)) {
	return !empty($tplUnsubscribeForm)
		? $pdoTools->getChunk($tplUnsubscribeForm, $newsletter->toArray())
		: 'Parameter "tplUnsubscribeForm" is empty';
}
elseif (!$newsletter->isSubscribed($modx->user->id)) {
	return !empty($tplSubscribeForm)
		? $pdoTools->getChunk($tplSubscribeForm, $newsletter->toArray())
		: 'Parameter "tplSubscribeForm" is empty';
}