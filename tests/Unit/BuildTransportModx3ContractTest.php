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
        $this->assertStringContainsString('sendexPrepareModelForBuild', $source);
    }

    public function testBuildTransportCleansModx3ModelArtifactsWhenSkippingRegeneration()
    {
        $source = file_get_contents($this->root . '/_build/build.transport.php');

        $this->assertStringContainsString('sendexPrepareModelForBuild($sendexBuildModx, $sendexModelDir)', $source);
        $this->assertStringNotContainsString('sendexNormalizeGeneratedPhpFiles($sendexModelDir)', $source);
    }

    public function testBuildModelSkipsRegenerationOnModx3AndNormalizesGeneratedPhp()
    {
        $source = file_get_contents($this->root . '/_build/build.model.php');
        $functions = file_get_contents($this->root . '/_build/includes/functions.php');

        $this->assertStringContainsString(
            'sendexPrepareModelForBuild($modx, $sources[\'model\'] . PKG_NAME_LOWER)',
            $source
        );
        $this->assertStringContainsString('function sendexPrepareModelForBuild', $functions);
        $this->assertStringContainsString('function sendexRemoveModx3ModelArtifacts', $functions);
        $this->assertStringContainsString('function sendexUsesLegacyModAction', $functions);
        $this->assertStringContainsString('function sendexNormalizeGeneratedPhpFiles', $functions);
        $this->assertStringContainsString("preg_match('/^\\{\\s*$/',", $functions);
    }

    public function testTransportMenuUsesCapabilityCheckForModAction()
    {
        $source = file_get_contents($this->root . '/_build/data/transport.menu.php');

        $this->assertStringContainsString('sendexUsesLegacyModAction($modx)', $source);
        $this->assertStringContainsString("\$menuFields['namespace'] = PKG_NAME_LOWER", $source);
        $this->assertStringContainsString("'controller' => 'index'", $source);
        $this->assertStringNotContainsString("\$controller = 'home'", $source);
        $this->assertStringNotContainsString('instanceof modAction', $source);
    }

    public function testModx3IndexControllerUsesSendexPrefixedClassName()
    {
        $source = file_get_contents(
            $this->root . '/core/components/sendex/controllers/index.class.php'
        );

        $this->assertStringContainsString('class SendexIndexManagerController', $source);
        $this->assertStringContainsString('extends SendexHomeManagerController', $source);
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
