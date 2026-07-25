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
 * Remove MODX 3 xPDO generator artifacts (PascalCase sx*.php with wrong namespaces).
 *
 * @param string $packageModelDir e.g. core/components/sendex/model/sendex
 */
function sendexRemoveModx3ModelArtifacts($packageModelDir)
{
    $artifacts = array(
        'sxNewsletter.php',
        'sxQueue.php',
        'sxSubscriber.php',
    );

    foreach ($artifacts as $name) {
        foreach (array($packageModelDir . '/mysql/' . $name, $packageModelDir . '/' . $name) as $path) {
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
 * Strip trailing whitespace from generated PHP files (xPDO generator leaves spaces after =>).
 *
 * @param string $directory
 * @deprecated Use sendexNormalizeGeneratedPhpFiles()
 */
function sendexTrimTrailingWhitespaceInPhpFiles($directory)
{
    sendexNormalizeGeneratedPhpFiles($directory);
}

/**
 * Ensure every PHP file under model ends with exactly one newline (PSR-12).
 *
 * @param string $directory
 * @deprecated Use sendexNormalizeGeneratedPhpFiles()
 */
function sendexNormalizePhpFileEndings($directory)
{
    sendexNormalizeGeneratedPhpFiles($directory);
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
