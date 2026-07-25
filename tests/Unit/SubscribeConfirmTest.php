<?php

use PHPUnit\Framework\TestCase;

class SubscribeConfirmTest extends TestCase
{
    /** @var FakeModX */
    private $modx;

    protected function setUp(): void
    {
        $this->modx = new FakeModX();
        require_once dirname(__DIR__, 2)
            . '/core/components/sendex/model/sendex/sxsubscribeconfirm.class.php';
    }

    public function testParseBoolRecognizesOffValues()
    {
        $this->assertFalse(sxSubscribeConfirm::parseBool('0'));
        $this->assertFalse(sxSubscribeConfirm::parseBool('false'));
        $this->assertFalse(sxSubscribeConfirm::parseBool('no'));
    }

    public function testParseBoolRecognizesOnValues()
    {
        $this->assertTrue(sxSubscribeConfirm::parseBool('1'));
        $this->assertTrue(sxSubscribeConfirm::parseBool('true'));
        $this->assertTrue(sxSubscribeConfirm::parseBool('yes'));
    }

    public function testSnippetPropertyOverridesSystemSetting()
    {
        $this->modx->options['sendex_confirm_email'] = true;

        $this->assertFalse(sxSubscribeConfirm::isRequired($this->modx, array('confirmEmail' => '0')));
        $this->assertTrue(sxSubscribeConfirm::isRequired($this->modx, array('confirmEmail' => '1')));
    }

    public function testFallsBackToSystemSetting()
    {
        $this->modx->options['sendex_confirm_email'] = false;

        $this->assertFalse(sxSubscribeConfirm::isRequired($this->modx, array()));
    }

    public function testDefaultIsConfirmRequired()
    {
        $this->assertTrue(sxSubscribeConfirm::isRequired($this->modx, array()));
    }
}
