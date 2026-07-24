<?php

switch ($modx->event->name) {
    case 'OnManagerPageInit':
        $cssFile = MODX_ASSETS_URL . 'components/sendex/css/mgr/main.css';
        $modx->regClientCSS($cssFile);
        break;

    case 'OnUserActivate':
    case 'OnBeforeUserActivate':
    case 'OnUserSave':
        $corePath = $modx->getOption(
            'sendex_core_path',
            null,
            $modx->getOption('core_path') . 'components/sendex/'
        );
        require_once $corePath . 'model/sendex/sxsubscribermerge.class.php';

        if (empty($user) && !empty($modx->event->params['user'])) {
            $user = $modx->event->params['user'];
        }

        if (!empty($user)) {
            $modx->addPackage('sendex', $corePath . 'model/');
            sxSubscriberMerge::attachGuestsForUser($modx, $user);
        }
        break;
}
