<?php

use PHPUnit\Framework\TestCase;

class ProcessorBootstrapContractTest extends TestCase
{
    /** @var string */
    private $base;

    protected function setUp(): void
    {
        $this->base = dirname(__DIR__, 2) . '/core/components/sendex/processors/mgr/';
    }

    /**
     * @return string[]
     */
    private function processorFiles()
    {
        $files = array();
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->base, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $path = $file->getPathname();
            if (substr($path, -strlen('sendexprocessor.class.php')) === 'sendexprocessor.class.php') {
                continue;
            }
            $source = file_get_contents($path);
            if (strpos($source, 'Processor') === false) {
                continue;
            }
            $files[] = $path;
        }

        sort($files);

        return $files;
    }

    public function testAllProcessorsReturnClassName()
    {
        foreach ($this->processorFiles() as $path) {
            $source = file_get_contents($path);
            $this->assertMatchesRegularExpression(
                "/return\\s+'sx[A-Za-z0-9_]+Processor';/",
                $source,
                basename($path)
            );
        }
    }

    public function testProcessorReturnMatchesDeclaredClass()
    {
        foreach ($this->processorFiles() as $path) {
            $source = file_get_contents($path);
            if (!preg_match('/class\\s+(sx[A-Za-z0-9_]+Processor)/', $source, $classMatch)) {
                $this->fail('Missing processor class in ' . basename($path));
            }
            if (!preg_match("/return\\s+'(sx[A-Za-z0-9_]+Processor)';/", $source, $returnMatch)) {
                $this->fail('Missing return statement in ' . basename($path));
            }

            $this->assertSame($classMatch[1], $returnMatch[1], basename($path));
        }
    }

    public function testSendexProcessorBaseRequiresProcessorInput()
    {
        $source = file_get_contents($this->base . 'sendexprocessor.class.php');

        $this->assertStringContainsString('extends modProcessor', $source);
        $this->assertStringContainsString('sxprocessorinput.class.php', $source);
    }
}
