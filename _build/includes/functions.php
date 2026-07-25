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
 * MODX 2 uses modAction + modMenu composite; MODX 3 uses namespace + action on modMenu.
 *
 * @param modX|null $modx
 * @return bool
 */
function sendexUsesLegacyModAction($modx = null)
{
    return class_exists('modAction');
}

/**
 * MODX 3: remove generator artifacts and skip regen. MODX 2: allow regen.
 *
 * @param modX   $modx
 * @param string $packageModelDir e.g. core/components/sendex/model/sendex
 * @return bool true when caller should run xPDO schema regeneration
 */
function sendexPrepareModelForBuild($modx, $packageModelDir)
{
    if (sendexShouldRegenerateModel($modx)) {
        return true;
    }

    sendexRemoveModx3ModelArtifacts($packageModelDir);

    return false;
}

/**
 * Remove MODX 3 xPDO generator artifacts (PascalCase sx*.php with wrong namespaces).
 *
 * @param string $packageModelDir e.g. core/components/sendex/model/sendex
 */
function sendexRemoveModx3ModelArtifacts($packageModelDir)
{
    $patterns = array(
        $packageModelDir . '/mysql/sx[A-Z]*.php',
        $packageModelDir . '/sx[A-Z]*.php',
    );
    foreach ($patterns as $pattern) {
        foreach (glob($pattern) ?: array() as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
}

/**
 * Normalize xPDO-generated PHP: trim lines, PSR-12 brace spacing, single EOF newline.
 *
 * @param string $directory
 */
function sendexNormalizeGeneratedPhpFiles($directory)
{
    if (!is_dir($directory)) {
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (!$file->isFile() || !preg_match('/\.php$/i', $file->getFilename())) {
            continue;
        }

        $path = $file->getPathname();
        $lines = file($path, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            continue;
        }

        $trimmed = array_map(
            static function ($line) {
                return rtrim($line, " \t");
            },
            $lines
        );

        $fixed = array();
        $count = count($trimmed);
        for ($i = 0; $i < $count; $i++) {
            $fixed[] = $trimmed[$i];
            if (preg_match('/^\{\s*$/', $trimmed[$i])) {
                while ($i + 1 < $count && $trimmed[$i + 1] === '') {
                    $i++;
                }
            }
        }

        $normalized = rtrim(implode("\n", $fixed), "\r\n") . "\n";
        if ($normalized !== file_get_contents($path)) {
            file_put_contents($path, $normalized);
        }
    }
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
