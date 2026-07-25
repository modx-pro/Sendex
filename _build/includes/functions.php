<?php

/**
 * Legacy alias for transport build scripts when MODX 3 PSR-4 classes are loaded
 * but global aliases (modPackageBuilder, etc.) are not registered yet.
 *
 * @param modX $modx
 */
function sendexEnsureBuildClassAliases($modx)
{
    if (!class_exists('modPackageBuilder')) {
        $modx->loadClass('modPackageBuilder', 'MODX\\Revolution\\Transport\\', false, true);
        if (class_exists('MODX\\Revolution\\Transport\\modPackageBuilder')) {
            class_alias('MODX\\Revolution\\Transport\\modPackageBuilder', 'modPackageBuilder');
        }
    }
}

/**
 * MODX 3 xPDO schema generator emits sendex\\sx* class names; Sendex keeps global sx* maps.
 *
 * @param modX $modx
 * @return bool
 */
function sendexShouldRegenerateModel($modx)
{
    $version = $modx->getVersionData();

    return (int) ($version['version'] ?? 2) < 3;
}

/**
 * @param $filename
 *
 * @return string
 */

function getSnippetContent($filename)
{
    $file = trim(file_get_contents($filename));
    preg_match('#\<\?php(.*)#is', $file, $data);

    return rtrim(rtrim(trim($data[1]), '?>'));
}


/**
 * Recursive directory remove
 *
 * @param $dir
 */
function rrmdir($dir)
{
    if (is_dir($dir)) {
        $objects = scandir($dir);

        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir . "/" . $object) == "dir") {
                    rrmdir($dir . "/" . $object);
                } else {
                    unlink($dir . "/" . $object);
                }
            }
        }

        reset($objects);
        rmdir($dir);
    }
}
