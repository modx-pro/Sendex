<?php

use PHPUnit\Framework\TestCase;

class SubscriberRemoveProcessorTest extends TestCase
{
    /** @var FakeModX */
    private $modx;

    /** @var sxSubscriberRemoveProcessor */
    private $processor;

    protected function setUp(): void
    {
        $this->modx = new FakeModX();
        $newsletter = new TestableNewsletter($this->modx);
        $newsletter->set('id', 10);
        $this->modx->newsletters[10] = $newsletter;
        $this->processor = new sxSubscriberRemoveProcessor($this->modx);
    }

    public function testRequiresIds()
    {
        $this->processor->properties = array('ids' => '');
        $result = $this->processor->process();
        $this->assertFalse($result['success']);
        $this->assertSame('sendex_subscribers_err_ns', $result['message']);
    }

    public function testRejectsCommaOnlyIds()
    {
        $this->processor->properties = array('ids' => '0,');
        $result = $this->processor->process();
        $this->assertFalse($result['success']);
        $this->assertSame('sendex_subscribers_err_ns', $result['message']);
    }

    public function testRequiresPermission()
    {
        $this->modx->permissions['edit_document'] = false;
        $this->processor->properties = array('ids' => '1');
        $result = $this->processor->process();
        $this->assertFalse($result['success']);
        $this->assertSame('access_denied', $result['message']);
    }

    public function testRemovesViaUnSubscribe()
    {
        $subscriber = new sxSubscriber($this->modx);
        $subscriber->fromArray(array(
            'id'            => 1,
            'newsletter_id' => 10,
            'user_id'       => 5,
            'email'         => 'a@example.com',
            'code'          => 'code1',
        ));
        $this->modx->subscribers[] = $subscriber;

        $this->processor->properties = array('ids' => '1');
        $result = $this->processor->process();
        $this->assertTrue($result['success']);
        $this->assertCount(0, $this->modx->subscribers);
        $this->assertSame('sxOnBeforeUnsubscribe', $this->modx->invoked[0][0]);
        $this->assertSame('sxOnUnsubscribe', $this->modx->invoked[1][0]);
    }

    public function testFailureWhenPluginCancels()
    {
        $subscriber = new sxSubscriber($this->modx);
        $subscriber->fromArray(array(
            'id'            => 1,
            'newsletter_id' => 10,
            'user_id'       => 5,
            'email'         => 'a@example.com',
            'code'          => 'code1',
        ));
        $this->modx->subscribers[] = $subscriber;
        $this->modx->invokeResponses['sxOnBeforeUnsubscribe'] = array('keep them');

        $this->processor->properties = array('ids' => '1');
        $result = $this->processor->process();
        $this->assertFalse($result['success']);
        $this->assertSame('keep them', $result['message']);
        $this->assertCount(1, $this->modx->subscribers);
    }

    public function testFailureWhenUnSubscribeReturnsFalse()
    {
        $subscriber = new sxSubscriber($this->modx);
        $subscriber->fromArray(array(
            'id'            => 1,
            'newsletter_id' => 10,
            'user_id'       => 5,
            'email'         => 'a@example.com',
            'code'          => 'code1',
        ));
        $subscriber->removeResult = false;
        $this->modx->subscribers[] = $subscriber;

        $this->processor->properties = array('ids' => '1');
        $result = $this->processor->process();
        $this->assertFalse($result['success']);
        $this->assertSame('sendex_subscriber_err_remove', $result['message']);
    }

    public function testOrphanRemoveWithoutNewsletter()
    {
        $subscriber = new sxSubscriber($this->modx);
        $subscriber->fromArray(array(
            'id'            => 2,
            'newsletter_id' => 99,
            'user_id'       => 5,
            'email'         => 'a@example.com',
            'code'          => 'orphan',
        ));
        $this->modx->subscribers[] = $subscriber;

        $this->processor->properties = array('ids' => '2');
        $result = $this->processor->process();
        $this->assertTrue($result['success']);
        $this->assertCount(0, $this->modx->subscribers);
        $this->assertSame(array(), $this->modx->invoked);
    }

    public function testOrphanRemoveFailure()
    {
        $subscriber = new sxSubscriber($this->modx);
        $subscriber->fromArray(array(
            'id'            => 2,
            'newsletter_id' => 99,
            'user_id'       => 5,
            'email'         => 'a@example.com',
            'code'          => 'orphan',
        ));
        $subscriber->removeResult = false;
        $this->modx->subscribers[] = $subscriber;

        $this->processor->properties = array('ids' => '2');
        $result = $this->processor->process();
        $this->assertFalse($result['success']);
        $this->assertSame('sendex_subscriber_err_remove', $result['message']);
    }
}
