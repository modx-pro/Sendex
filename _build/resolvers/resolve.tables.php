<?php

if ($object->xpdo) {
	/* @var modX $modx */
	$modx =& $object->xpdo;

	switch ($options[xPDOTransport::PACKAGE_ACTION]) {
		case xPDOTransport::ACTION_INSTALL:
			$modelPath = $modx->getOption('sendex_core_path',null,$modx->getOption('core_path').'components/sendex/').'model/';
			$modx->addPackage('sendex', $modelPath);

			$manager = $modx->getManager();
			$objects = array(
				'sxNewsletter',
				'sxSubscriber',
				'sxQueue',
			);
			foreach ($objects as $object) {
				$manager->createObjectContainer($object);
			}
			break;

		case xPDOTransport::ACTION_UPGRADE:
			break;

		case xPDOTransport::ACTION_UNINSTALL:
			break;
	}
}
return true;