<?php

use PHPUnit\Framework\TestCase;

class NewsletterUnsubscribeTest extends TestCase
{
    /** @var FakeModX */
    private $modx;

    /** @var TestableNewsletter */
    private $newsletter;

    protected function setUp(): void
    {
        $this->modx = new FakeModX();
        $this->newsletter = new TestableNewsletter($this->modx);
        $this->newsletter->set('id', 10);
    }

    /**
     * @return sxSubscriber
     */
    private function addSubscriber(array $fields)
    {
        $subscriber = new sxSubscriber($this->modx);
        $subscriber->fromArray($fields);
        $this->modx->subscribers[] = $subscriber;

        return $subscriber;
    }

    public function testMissingCodeReturnsFalse()
    {
        $this->assertFalse($this->newsletter->unSubscribe('missing'));
        $this->assertSame(array(), $this->modx->invoked);
    }

    public function testMismatchedNewsletterIdReturnsFalseWithoutEvents()
    {
        $this->addSubscriber(array(
            'id'            => 1,
            'newsletter_id' => 99,
            'user_id'       => 5,
            'email'         => 'user@example.com',
            'code'          => 'abc123',
        ));

        $this->assertFalse($this->newsletter->unSubscribe('abc123'));
        $this->assertCount(1, $this->modx->subscribers);
        $this->assertSame(array(), $this->modx->invoked);
    }

    public function testFiresBeforeAndAfterEventsOnSuccess()
    {
        $this->addSubscriber(array(
            'id'            => 1,
            'newsletter_id' => 10,
            'user_id'       => 5,
            'email'         => 'user@example.com',
            'code'          => 'abc123',
        ));

        $this->assertTrue($this->newsletter->unSubscribe('abc123'));
        $this->assertCount(0, $this->modx->subscribers);
        $this->assertCount(2, $this->modx->invoked);
        $this->assertSame('sxOnBeforeUnsubscribe', $this->modx->invoked[0][0]);
        $this->assertSame('sxOnUnsubscribe', $this->modx->invoked[1][0]);
        $this->assertSame('abc123', $this->modx->invoked[0][1]['code']);
        $this->assertSame('snippet', $this->modx->invoked[0][1]['source']);
        $this->assertSame('snippet', $this->modx->invoked[1][1]['source']);
    }

    public function testUnsubscribePassesExplicitSource()
    {
        $this->addSubscriber(array(
            'id'            => 1,
            'newsletter_id' => 10,
            'user_id'       => 5,
            'email'         => 'user@example.com',
            'code'          => 'abc123',
        ));

        $this->assertTrue($this->newsletter->unSubscribe('abc123', 'mgr'));
        $this->assertSame('mgr', $this->modx->invoked[0][1]['source']);
    }

    public function testBeforeCancelReturnsPluginMessageAndKeepsSubscriber()
    {
        $this->addSubscriber(array(
            'id'            => 1,
            'newsletter_id' => 10,
            'user_id'       => 5,
            'email'         => 'user@example.com',
            'code'          => 'abc123',
        ));
        $this->modx->invokeResponses['sxOnBeforeUnsubscribe'] = array('stay subscribed');

        $this->assertSame('stay subscribed', $this->newsletter->unSubscribe('abc123'));
        $this->assertCount(1, $this->modx->subscribers);
        $this->assertCount(1, $this->modx->invoked);
    }

    public function testRemoveFailureReturnsFalseWithoutAfterEvent()
    {
        $subscriber = $this->addSubscriber(array(
            'id'            => 1,
            'newsletter_id' => 10,
            'user_id'       => 5,
            'email'         => 'user@example.com',
            'code'          => 'abc123',
        ));
        $subscriber->removeResult = false;

        $this->assertFalse($this->newsletter->unSubscribe('abc123'));
        $this->assertCount(1, $this->modx->subscribers);
        $this->assertCount(1, $this->modx->invoked);
        $this->assertSame('sxOnBeforeUnsubscribe', $this->modx->invoked[0][0]);
    }
}
