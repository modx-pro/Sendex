<?php

/** @var array $scriptProperties */

/** @var Sendex $Sendex */

$corePath = $modx->getOption(
    'sendex_core_path',
    null,
    $modx->getOption('core_path') . 'components/sendex/'
);
$Sendex = $modx->getService('sendex', 'Sendex', $corePath . 'model/sendex/', $scriptProperties);
if (!($Sendex instanceof Sendex)) {
    return '';
}

require_once $corePath . 'model/sendex/sxuserprofile.class.php';
require_once $corePath . 'model/sendex/sxunsubscriberesolve.class.php';
require_once $corePath . 'model/sendex/sxsubscribeconfirm.class.php';
require_once $corePath . 'model/sendex/sxsubscribeajaxresponse.class.php';

$tplSubscribeAuth = $modx->getOption('tplSubscribeAuth', $scriptProperties, 'tpl.Sendex.subscribe.auth');
$tplSubscribeGuest = $modx->getOption('tplSubscribeGuest', $scriptProperties, 'tpl.Sendex.subscribe.guest');
$tplUnsubscribe = $modx->getOption('tplUnsubscribe', $scriptProperties, 'tpl.Sendex.unsubscribe');
$tplActivate = $modx->getOption('tplActivate', $scriptProperties, 'tpl.Sendex.activate');
if (empty($linkTTL)) {
    $linkTTL = 1800;
}
$requireConfirm = sxSubscribeConfirm::isRequired($modx, $scriptProperties);
$widgetKey = trim((string) $modx->getOption('widgetKey', $scriptProperties, ''));
$loadJs = sxSubscribeAjaxResponse::parseEnabled(
    $modx->getOption('loadJs', $scriptProperties, true),
    true
);
$isAjax = sxSubscribeAjaxResponse::isRequest($scriptProperties);

if (empty($id) || !$newsletter = $modx->getObject('sxNewsletter', $id)) {
    return $modx->lexicon('sendex_newsletter_err_ns');
}

/** @var sxNewsletter $newsletter */
if (!$newsletter->active && empty($showInactive)) {
    return $modx->lexicon('sendex_newsletter_err_disabled');
}

$placeholders = $newsletter->toArray();
$placeholders['message'] = '';
$placeholders['class'] = '';
$placeholders['error'] = 0;
$placeholders['widget_key'] = $widgetKey;
$newsletterId = (int) $id;
$handlesRequest = !empty($_REQUEST['sx_action'])
    && sxSubscribeAjaxResponse::matchesRequest($newsletterId, $widgetKey, $_REQUEST);
$isAuthenticated = $modx->user->isAuthenticated($modx->context->key);
if ($isAuthenticated) {
    $placeholders = sxUserProfile::authenticatedPlaceholders($modx, $modx->user, $placeholders);
}

if ($handlesRequest) {
    $params = $_GET;
    unset($params[$modx->getOption('request_param_alias')]);
    unset($params[$modx->getOption('request_param_id')]);
    $eventSource = $isAjax ? 'ajax' : 'snippet';

    switch ($_REQUEST['sx_action']) {
        case 'subscribe':
            if ($isAuthenticated && $modx->user->id) {
                $response = $newsletter->subscribe($modx->user->id, '', $eventSource);
                if ($response !== true) {
                    $placeholders['message'] = is_string($response)
                        ? $response
                        : $modx->lexicon('sendex_subscribe_err_email_wrong');
                    $placeholders['error'] = 1;
                }
            } elseif (!empty($_REQUEST['email'])) {
                $email = htmlentities(strip_tags(urldecode($_REQUEST['email'])));
                $guestResult = $newsletter->subscribeGuest(
                    $email,
                    $modx->user->id,
                    $linkTTL,
                    $requireConfirm
                );
                if ($guestResult['status'] === 'already') {
                    $placeholders['message'] = $modx->lexicon('sendex_subscribe_err_already');
                } elseif ($guestResult['status'] === 'invalid') {
                    $placeholders['message'] = $modx->lexicon('sendex_subscribe_err_email_wrong');
                    $placeholders['error'] = 1;
                } elseif ($guestResult['status'] === 'subscribed') {
                    $placeholders['message'] = $modx->lexicon('sendex_subscribe_email_confirmed');
                    $params['sx_confirmed'] = 1;
                } elseif ($guestResult['status'] === 'error') {
                    $placeholders['message'] = $guestResult['message'];
                    $placeholders['error'] = 1;
                } elseif ($guestResult['status'] === 'confirm') {
                    $params['hash'] = $guestResult['hash'];
                    $params['sx_action'] = 'confirm';
                    $params['newsletter_id'] = $newsletterId;
                    $placeholders['link'] = $modx->makeUrl($modx->resource->id, $modx->context->key, $params, 'full');
                    $placeholders['email_body'] = $modx->getChunk($tplActivate, $placeholders);
                    $response = $Sendex->sendEmail($email, $placeholders);
                    if ($response !== true) {
                        $placeholders['message'] = $modx->lexicon('sendex_subscribe_err_email_send');
                        $placeholders['error'] = 1;
                    } else {
                        $placeholders['message'] = $modx->lexicon('sendex_subscribe_email_subscribed');
                        $params['sx_subscribed'] = 1;
                    }
                }
            } else {
                $placeholders['message'] = $modx->lexicon('sendex_subscribe_err_email_ns');
                $placeholders['error'] = 1;
            }
            unset($params['email'], $params['hash']);
            break;
        case 'confirm':
            if (!empty($_REQUEST['hash'])) {
                $response = $newsletter->confirmEmail($_REQUEST['hash']);
                if ($response === true) {
                    $placeholders['message'] = $modx->lexicon('sendex_subscribe_email_confirmed');
                    $params['sx_confirmed'] = 1;
                } else {
                    $placeholders['message'] = is_string($response)
                        ? $response
                        : $modx->lexicon('sendex_subscribe_err_email_wrong');
                    $placeholders['error'] = 1;
                }
                unset($params['hash']);
            }
            break;
        case 'unsubscribe':
            if (!empty($_REQUEST['code'])) {
                $code = $_REQUEST['code'];
                $target = sxUnsubscribeResolve::forCode($modx, $code, $newsletter);
                if ($target) {
                    $newsletter = $target;
                    $placeholders = array_merge(
                        $newsletter->toArray(),
                        array(
                            'message' => '',
                            'class'   => '',
                            'error'   => 0,
                            'widget_key' => $widgetKey,
                        )
                    );
                    if ($isAuthenticated) {
                        $placeholders = sxUserProfile::authenticatedPlaceholders(
                            $modx,
                            $modx->user,
                            $placeholders
                        );
                    }
                }
                $response = $newsletter->unSubscribe($code, $eventSource);
                if ($response === true) {
                    $placeholders['message'] = $modx->lexicon('sendex_subscribe_email_unsubscribed');
                    $params['sx_unsubscribed'] = 1;
                } else {
                    $placeholders['message'] = is_string($response)
                        ? $response
                        : $modx->lexicon('sendex_subscriber_err_remove');
                    $placeholders['error'] = 1;
                }
            }
            unset($params['code'], $params['newsletter_id']);
            break;
    }

    unset($params['sx_action']);
    if (!$isAjax && empty($placeholders['message'])) {
        $modx->sendRedirect($modx->makeUrl($modx->resource->id, $modx->context->key, $params, 'full'));
    }
}

if (!empty($placeholders['message'])) {
    $placeholders['class'] = !empty($placeholders['error'])
        ? 'sendex-error'
        : $modx->getOption('msgClass', $scriptProperties, 'active');
}

if ($isAuthenticated && ($subscriberId = $newsletter->isSubscribed($modx->user->id))) {
    if ($subscriber = $modx->getObject('sxSubscriber', $subscriberId)) {
        $placeholders = array_merge($subscriber->toArray(), $placeholders);
    }
    $output = $modx->getChunk($tplUnsubscribe, $placeholders);
} else {
    $output = $isAuthenticated
        ? $modx->getChunk($tplSubscribeAuth, $placeholders)
        : $modx->getChunk($tplSubscribeGuest, $placeholders);
}

if ($isAjax && $handlesRequest) {
    sxSubscribeAjaxResponse::send($placeholders, $output);
}

if ($loadJs && !defined('SENDEX_FRONTEND_JS')) {
    define('SENDEX_FRONTEND_JS', true);
    $modx->regClientScript(
        $modx->getOption('assets_url') . 'components/sendex/js/web/sendex.js'
    );
}

return $output;
