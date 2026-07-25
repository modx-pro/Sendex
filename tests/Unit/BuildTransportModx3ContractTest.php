<?php

use PHPUnit\Framework\TestCase;

class BuildTransportModx3ContractTest extends TestCase
{
    /** @var string */
    private $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 2);
    }

    public function testBuildConfigDefinesAssetsPathPlaceholder()
    {
        $source = file_get_contents($this->root . '/_build/build.config.php');

        $this->assertStringContainsString("define('PKG_ASSETS_PATH'", $source);
        $this->assertStringContainsString("{assets_path}components/", $source);
    }

    public function testBuildTransportRegistersNamespaceWithAssetsPath()
    {
        $source = file_get_contents($this->root . '/_build/build.transport.php');

        $this->assertStringContainsString('sendexEnsureBuildClassAliases($modx)', $source);
        $this->assertStringContainsString(
            'registerNamespace(PKG_NAME_LOWER, false, true, PKG_NAMESPACE_PATH, PKG_ASSETS_PATH)',
            $source
        );
        $this->assertStringContainsString('sendexShouldRegenerateModel', $source);
    }

    public function testTransportMenuUsesCapabilityCheckForModAction()
    {
        $source = file_get_contents($this->root . '/_build/data/transport.menu.php');

        $this->assertStringContainsString("class_exists('modAction')", $source);
        $this->assertStringContainsString("\$menuFields['namespace'] = PKG_NAME_LOWER", $source);
        $this->assertStringNotContainsString('instanceof modAction', $source);
    }

    public function testModelMetadataKeepsGlobalSxClassNames()
    {
        $source = file_get_contents(
            $this->root . '/core/components/sendex/model/sendex/metadata.mysql.php'
        );

        $this->assertStringContainsString("'sxNewsletter'", $source);
        $this->assertStringContainsString("'sxSubscriber'", $source);
        $this->assertStringContainsString("'sxQueue'", $source);
        $this->assertStringNotContainsString('MODX\\Revolution\\sx', $source);
        $this->assertStringNotContainsString('sendex\\sx', $source);
    }

    public function testInnodbMigrationDoesNotOverridePhinxTablesProperty()
    {
        $source = file_get_contents(
            $this->root . '/core/components/sendex/migrations/20260724130000_innodb_queue_subscriber_link.php'
        );

        $this->assertStringContainsString('$sendexTableNames', $source);
        $this->assertStringNotContainsString('protected $tables', $source);
    }
}
