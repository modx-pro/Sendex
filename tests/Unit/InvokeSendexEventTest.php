<?php

use PHPUnit\Framework\TestCase;

class InvokeSendexEventTest extends TestCase
{
    /** @var FakeModX */
    private $modx;

    /** @var TestableNewsletter */
    private $newsletter;

    protected function setUp(): void
    {
        $this->modx = new FakeModX();
        $this->newsletter = new TestableNewsletter($this->modx);
        $this->newsletter->set('id', 1);
    }

    public function testReturnsTrueWhenNoPluginOutput()
    {
        $this->assertTrue($this->newsletter->callInvokeSendexEvent('sxOnBeforeSubscribe', array()));
    }

    public function testReturnsFirstNonEmptyPluginMessage()
    {
        $this->modx->invokeResponses['sxOnBeforeSubscribe'] = array('', '  blocked  ', 'ignored');

        $this->assertSame(
            'blocked',
            $this->newsletter->callInvokeSendexEvent('sxOnBeforeSubscribe', array())
        );
    }

    public function testIgnoresNonStringPluginOutput()
    {
        $this->modx->invokeResponses['sxOnBeforeSubscribe'] = array(0, false, null, array('x'));

        $this->assertTrue($this->newsletter->callInvokeSendexEvent('sxOnBeforeSubscribe', array()));
    }

    public function testSkipsInvokeWhenXpdoIsNotModX()
    {
        $xpdo = new xPDOSimpleObject();
        $newsletter = new TestableNewsletter($xpdo);
        $newsletter->xpdo = $xpdo;

        $this->assertTrue($newsletter->callInvokeSendexEvent('sxOnBeforeSubscribe', array()));
    }
}
