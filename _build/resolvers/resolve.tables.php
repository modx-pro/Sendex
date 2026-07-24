<?php

/**
 * Legacy tables resolver — schema is applied by Phinx (resolve.migrations).
 * Kept as a no-op so older build docs that mention "tables" stay harmless.
 */

if ($object->xpdo) {
    /* @var modX $modx */
    $modx =& $object->xpdo;

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            $modx->log(
                modX::LOG_LEVEL_INFO,
                '[Sendex] Schema managed by Phinx (see resolve.migrations / core/components/sendex/migrations).'
            );
            break;

        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }
}

return true;
