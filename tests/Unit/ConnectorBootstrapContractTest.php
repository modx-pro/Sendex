<?php

use PHPUnit\Framework\TestCase;

class ConnectorBootstrapContractTest extends TestCase
{
    /** @var string */
    private $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 2);
    }

    public function testBootstrapDefinesSharedInitFunctions()
    {
        $source = file_get_contents($this->root . '/core/components/sendex/bootstrap.php');

        $this->assertStringContainsString('function sendexBootstrap', $source);
        $this->assertStringContainsString('function sendexRegisterAutoload', $source);
        $this->assertStringContainsString('function sendexEnsureProcessorBase', $source);
        $this->assertStringContainsString('spl_autoload_register', $source);
    }

    public function testConnectorUsesBootstrap()
    {
        $source = file_get_contents($this->root . '/assets/components/sendex/connector.php');

        $this->assertStringContainsString('bootstrap.php', $source);
        $this->assertStringContainsString('sendexBootstrap($modx)', $source);
        $this->assertStringContainsString('handleRequest', $source);
        $this->assertStringContainsString('processors_path', $source);
        $this->assertStringNotContainsString('new Sendex($modx)', $source);
    }

    public function testMgrControllerUsesBootstrap()
    {
        $source = file_get_contents(
            $this->root . '/core/components/sendex/sendexmaincontroller.class.php'
        );

        $this->assertStringContainsString('bootstrap.php', $source);
        $this->assertStringContainsString('sendexBootstrap($this->modx)', $source);
    }

    public function testCronUsesBootstrap()
    {
        $source = file_get_contents($this->root . '/core/components/sendex/cron/send.php');

        $this->assertStringContainsString('bootstrap.php', $source);
        $this->assertStringContainsString('sendexBootstrap($modx)', $source);
        $this->assertStringContainsString('sxQueueSender::flush', $source);
    }
}
