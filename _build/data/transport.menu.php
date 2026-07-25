<?php

$menus = array();

$tmp = array(
    'sendex' => array(
        'description' => 'sendex_menu_desc',
        'action'      => array('controller' => 'index'),
    ),
);

$usesLegacyModAction = sendexUsesLegacyModAction($modx);

$i = 0;
foreach ($tmp as $k => $v) {
    $action = null;
    $menuFields = array(
        'text'      => $k,
        'parent'    => 'components',
        'icon'      => 'images/icons/plugin.gif',
        'menuindex' => $i,
        'params'    => '',
        'handler'   => '',
    );

    if (!empty($v['action'])) {
        if ($usesLegacyModAction) {
            /* @var modAction $action */
            $action = $modx->newObject('modAction');
            $action->fromArray(array_merge(array(
                'namespace'   => PKG_NAME_LOWER,
                'id'          => 0,
                'parent'      => 0,
                'haslayout'   => 1,
                'lang_topics' => PKG_NAME_LOWER . ':default',
                'assets'      => '',
            ), $v['action']), '', true, true);
        } else {
            $controller = !empty($v['action']['controller']) ? $v['action']['controller'] : 'index';
            $menuFields['action'] = $controller;
            $menuFields['namespace'] = PKG_NAME_LOWER;
        }
        unset($v['action']);
    }

    /* @var modMenu $menu */
    $menu = $modx->newObject('modMenu');
    $menu->fromArray(array_merge($menuFields, $v), '', true, true);

    if ($usesLegacyModAction && !empty($action)) {
        $menu->addOne($action);
    }

    $menus[] = $menu;
    $i++;
}

unset($action, $menu, $i);
return $menus;
