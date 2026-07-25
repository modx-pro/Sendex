<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/_build/includes/functions.php';

class BuildHelpersTest extends TestCase
{
    /** @var string */
    private $tempModelDir;

    protected function setUp(): void
    {
        $this->tempModelDir = sys_get_temp_dir() . '/sendex-build-' . uniqid('', true);
        mkdir($this->tempModelDir . '/mysql', 0777, true);
    }

    protected function tearDown(): void
    {
        if (!is_dir($this->tempModelDir)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->tempModelDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($this->tempModelDir);
    }

    public function testRemoveModx3ModelArtifactsDeletesPascalCaseSxFiles()
    {
        $paths = array(
            $this->tempModelDir . '/mysql/sxNewsletter.php',
            $this->tempModelDir . '/mysql/sxQueue.php',
            $this->tempModelDir . '/sxSubscriber.php',
            $this->tempModelDir . '/mysql/sxnewsletter.map.inc.php',
        );
        foreach ($paths as $path) {
            file_put_contents($path, '<?php');
        }

        sendexRemoveModx3ModelArtifacts($this->tempModelDir);

        $this->assertFileDoesNotExist($paths[0]);
        $this->assertFileDoesNotExist($paths[1]);
        $this->assertFileDoesNotExist($paths[2]);
        $this->assertFileExists($paths[3]);
    }

    public function testUsesLegacyModActionReflectsModActionClassPresence()
    {
        $this->assertSame(class_exists('modAction'), sendexUsesLegacyModAction());
    }
}
