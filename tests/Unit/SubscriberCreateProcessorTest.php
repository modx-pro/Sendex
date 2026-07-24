<?php

use PHPUnit\Framework\TestCase;

class SubscriberCreateProcessorTest extends TestCase
{
    /** @var FakeModX */
    private $modx;

    /** @var sxSubscriberCreateProcessor */
    private $processor;

    protected function setUp(): void
    {
        $this->modx = new FakeModX();
        $newsletter = new TestableNewsletter($this->modx);
        $newsletter->set('id', 10);
        $this->modx->newsletters[10] = $newsletter;
        $this->processor = new sxSubscriberCreateProcessor($this->modx);
    }

    public function testRequiresUserAndNewsletter()
    {
        $result = $this->processor->process();
        $this->assertFalse($result['success']);
        $this->assertSame('sendex_subscriber_err_save', $result['message']);
    }

    public function testFailsWithoutProfile()
    {
        $this->processor->properties = array(
            'user_id'       => 5,
            'newsletter_id' => 10,
        );
        $result = $this->processor->process();
        $this->assertFalse($result['success']);
        $this->assertSame('sendex_subscriber_err_email', $result['message']);
    }

    public function testFailsOnInvalidProfileEmail()
    {
        $this->modx->profiles[5] = 'not-an-email';
        $this->processor->properties = array(
            'user_id'       => 5,
            'newsletter_id' => 10,
        );
        $result = $this->processor->process();
        $this->assertFalse($result['success']);
        $this->assertSame('sendex_subscriber_err_email', $result['message']);
    }

    public function testFailsOnDuplicate()
    {
        $this->modx->profiles[5] = 'a@example.com';
        $existing = new sxSubscriber($this->modx);
        $existing->fromArray(array(
            'newsletter_id' => 10,
            'user_id'       => 5,
            'email'         => 'a@example.com',
        ));
        $this->modx->subscribers[] = $existing;

        $this->processor->properties = array(
            'user_id'       => 5,
            'newsletter_id' => 10,
        );
        $result = $this->processor->process();
        $this->assertFalse($result['success']);
        $this->assertSame('sendex_subscriber_err_ae', $result['message']);
    }

    public function testFailsWhenNewsletterMissing()
    {
        $this->modx->profiles[5] = 'a@example.com';
        unset($this->modx->newsletters[10]);
        $this->processor->properties = array(
            'user_id'       => 5,
            'newsletter_id' => 10,
        );
        $result = $this->processor->process();
        $this->assertFalse($result['success']);
        $this->assertSame('sendex_newsletter_err_nf', $result['message']);
    }

    public function testSuccessSubscribesAndFiresEvents()
    {
        $this->modx->profiles[5] = 'a@example.com';
        $this->processor->properties = array(
            'user_id'       => 5,
            'newsletter_id' => 10,
        );
        $result = $this->processor->process();
        $this->assertTrue($result['success']);
        $this->assertCount(1, $this->modx->subscribers);
        $this->assertSame('sxOnBeforeSubscribe', $this->modx->invoked[0][0]);
        $this->assertSame('sxOnSubscribe', $this->modx->invoked[1][0]);
    }

    public function testPropagatesPluginCancelMessage()
    {
        $this->modx->profiles[5] = 'a@example.com';
        $this->modx->invokeResponses['sxOnBeforeSubscribe'] = array('blocked by plugin');
        $this->processor->properties = array(
            'user_id'       => 5,
            'newsletter_id' => 10,
        );
        $result = $this->processor->process();
        $this->assertFalse($result['success']);
        $this->assertSame('blocked by plugin', $result['message']);
    }
}
