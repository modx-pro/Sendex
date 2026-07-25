<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../Support/TestSendexIdsProcessor.php';

class SendexProcessorAclTest extends TestCase
{
    /** @var FakeModX */
    private $modx;

    protected function setUp(): void
    {
        $this->modx = new FakeModX();
    }

    public function testRequireIdsUsesSameFailureForEmptyInput()
    {
        $processor = new TestSendexIdsProcessor($this->modx, array('ids' => ','));
        $result = $processor->process();

        $this->assertFalse($result['success']);
        $this->assertSame('sendex_queue_err_ns', $result['message']);
    }

    public function testFailureWhenPermissionMissing()
    {
        $this->modx->permissions['edit_document'] = false;
        $processor = new TestSendexIdsProcessor($this->modx, array('ids' => '1'));
        $result = $processor->process();

        $this->assertFalse($result['success']);
        $this->assertSame('access_denied', $result['message']);
    }

    public function testMgrProcessorsExtendSendexProcessor()
    {
        $base = dirname(__DIR__, 2) . '/core/components/sendex/processors/mgr/';
        $files = array(
            'newsletter/remove.class.php',
            'newsletter/enable.class.php',
            'newsletter/disable.class.php',
            'newsletter/send.class.php',
            'queue/remove.class.php',
            'queue/remove_all.class.php',
            'queue/add.class.php',
            'queue/send.class.php',
            'queue/send_all.class.php',
            'newsletter/subscriber/create.class.php',
            'newsletter/subscriber/remove.class.php',
        );

        foreach ($files as $file) {
            $source = file_get_contents($base . $file);
            $this->assertStringContainsString('extends sxSendexProcessor', $source, $file);
            $this->assertStringContainsString('failureIfNoPermission', $source, $file);
        }
    }

    public function testGetListProcessorsDeclareViewPermission()
    {
        $base = dirname(__DIR__, 2) . '/core/components/sendex/processors/mgr/';
        $files = array(
            'newsletter/getlist.class.php',
            'newsletter/get.class.php',
            'newsletter/subscriber/getlist.class.php',
            'queue/getlist.class.php',
        );

        foreach ($files as $file) {
            $source = file_get_contents($base . $file);
            $this->assertStringContainsString("'view_document'", $source, $file);
        }
    }

    public function testManagerControllerChecksViewPermission()
    {
        $source = file_get_contents(
            dirname(__DIR__, 2) . '/core/components/sendex/sendexmaincontroller.class.php'
        );

        $this->assertStringContainsString("hasPermission('view_sendex')", $source);
        $this->assertStringContainsString("hasPermission('view_document')", $source);
    }

    public function testTransportMenuDeclaresPermission()
    {
        $source = file_get_contents(
            dirname(__DIR__, 2) . '/_build/data/transport.menu.php'
        );

        $this->assertStringContainsString("'permissions' => 'view_document'", $source);
    }
}
