<?php

use PHPUnit\Framework\TestCase;

class NewsletterSendProcessorTest extends TestCase
{
    /** @var FakeModX */
    private $modx;

    protected function setUp(): void
    {
        $this->modx = new FakeModX();
        require_once dirname(__DIR__, 2)
            . '/core/components/sendex/processors/mgr/newsletter/send.class.php';
    }

    public function testFailureWhenNewsletterMissing()
    {
        $processor = new sxNewsletterSendProcessor($this->modx, array('id' => 10));
        $response = $processor->process();

        $this->assertFalse($response['success']);
        $this->assertSame('sendex_newsletter_err_nf', $response['message']);
    }

    public function testFailureWhenIdMissing()
    {
        $processor = new sxNewsletterSendProcessor($this->modx, array());
        $response = $processor->process();

        $this->assertFalse($response['success']);
        $this->assertSame('sendex_newsletter_err_ns', $response['message']);
    }

    public function testSuccessDelegatesToDispatch()
    {
        $newsletter = new class ($this->modx) extends TestableNewsletter {
            /**
             * @param array $options
             * @return array
             */
            public function sendToSubscribers(array $options = array())
            {
                return array(
                    'success'  => true,
                    'message'  => 'sendex_newsletter_send_success:queued=2:sent=2',
                    'queued'   => 2,
                    'sent'     => 2,
                    'skipped'  => 0,
                    'failed'   => 0,
                );
            }
        };
        $newsletter->set('id', 10);
        $this->modx->newsletters[10] = $newsletter;

        $processor = new sxNewsletterSendProcessor($this->modx, array('id' => 10));
        $response = $processor->process();

        $this->assertTrue($response['success']);
        $this->assertSame(2, $response['object']['sent']);
    }

    public function testProcessorUsesDispatchClass()
    {
        $source = file_get_contents(
            dirname(__DIR__, 2) . '/core/components/sendex/processors/mgr/newsletter/send.class.php'
        );

        $this->assertStringContainsString('$newsletter->sendToSubscribers', $source);
        $this->assertStringContainsString('extends sxSendexProcessor', $source);
    }
}
