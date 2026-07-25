<?php

use PHPUnit\Framework\TestCase;

class NewsletterSubscribeTest extends TestCase
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

    public function testRejectsInvalidEmail()
    {
        $this->assertFalse($this->newsletter->subscribe(0, 'not-an-email'));
        $this->assertSame(array(), $this->modx->invoked);
    }

    public function testLoadsEmailFromProfile()
    {
        $this->modx->profiles[5] = 'user@example.com';

        $this->assertTrue($this->newsletter->subscribe(5, ''));
        $this->assertCount(1, $this->modx->subscribers);
        $this->assertSame('user@example.com', $this->modx->subscribers[0]->get('email'));
    }

    public function testAlreadySubscribedIsIdempotentWithoutEvents()
    {
        $existing = new sxSubscriber($this->modx);
        $existing->fromArray(array(
            'id'            => 1,
            'newsletter_id' => 10,
            'user_id'       => 5,
            'email'         => 'user@example.com',
        ));
        $this->modx->subscribers[] = $existing;

        $this->assertTrue($this->newsletter->subscribe(5, 'user@example.com'));
        $this->assertSame(array(), $this->modx->invoked);
        $this->assertCount(1, $this->modx->subscribers);
    }

    public function testAlreadySubscribedUsesSingleSubscriberLookup()
    {
        $existing = new sxSubscriber($this->modx);
        $existing->fromArray(array(
            'id'            => 1,
            'newsletter_id' => 10,
            'user_id'       => 5,
            'email'         => 'user@example.com',
        ));
        $this->modx->subscribers[] = $existing;

        $before = isset($this->modx->getObjectCalls['sxSubscriber'])
            ? $this->modx->getObjectCalls['sxSubscriber']
            : 0;

        $this->assertTrue($this->newsletter->subscribe(5, 'user@example.com'));

        $this->assertSame(1, $this->modx->getObjectCalls['sxSubscriber'] - $before);
    }

    public function testFiresBeforeAndAfterEventsOnSuccess()
    {
        $this->assertTrue($this->newsletter->subscribe(7, 'new@example.com'));

        $this->assertCount(2, $this->modx->invoked);
        $this->assertSame('sxOnBeforeSubscribe', $this->modx->invoked[0][0]);
        $this->assertSame('sxOnSubscribe', $this->modx->invoked[1][0]);
        $this->assertInstanceOf('sxSubscriber', $this->modx->invoked[1][1]['subscriber']);
        $this->assertSame('snippet', $this->modx->invoked[0][1]['source']);
        $this->assertSame('snippet', $this->modx->invoked[1][1]['source']);
    }

    public function testSubscribePassesExplicitSource()
    {
        $this->assertTrue($this->newsletter->subscribe(7, 'mgr@example.com', 'mgr'));

        $this->assertSame('mgr', $this->modx->invoked[0][1]['source']);
        $this->assertSame('mgr', $this->modx->invoked[1][1]['source']);
    }

    public function testSubscribeNormalizesUnknownSourceToSnippet()
    {
        $this->assertTrue($this->newsletter->subscribe(7, 'x@example.com', 'weird'));

        $this->assertSame('snippet', $this->modx->invoked[0][1]['source']);
    }

    public function testBeforeCancelReturnsPluginMessageAndDoesNotSave()
    {
        $this->modx->invokeResponses['sxOnBeforeSubscribe'] = array('not allowed');

        $this->assertSame('not allowed', $this->newsletter->subscribe(7, 'new@example.com'));
        $this->assertCount(0, $this->modx->subscribers);
        $this->assertCount(1, $this->modx->invoked);
        $this->assertSame('sxOnBeforeSubscribe', $this->modx->invoked[0][0]);
    }

    public function testSaveFailureReturnsFalseWithoutAfterEvent()
    {
        $this->modx = new class extends FakeModX {
            public function newObject($class)
            {
                $subscriber = parent::newObject($class);
                $subscriber->saveResult = false;

                return $subscriber;
            }
        };
        $this->newsletter = new TestableNewsletter($this->modx);
        $this->newsletter->set('id', 10);

        $this->assertFalse($this->newsletter->subscribe(7, 'new@example.com'));
        $this->assertCount(1, $this->modx->invoked);
        $this->assertSame('sxOnBeforeSubscribe', $this->modx->invoked[0][0]);
    }

    public function testDoesNotCreateSecondRowWhenEmailChangedForSameUser()
    {
        $existing = new sxSubscriber($this->modx);
        $existing->fromArray(array(
            'id'            => 1,
            'newsletter_id' => 10,
            'user_id'       => 5,
            'email'         => 'old@example.com',
        ));
        $this->modx->subscribers[] = $existing;

        $this->assertTrue($this->newsletter->subscribe(5, 'new@example.com'));
        $this->assertCount(1, $this->modx->subscribers);
        $this->assertSame('new@example.com', $this->modx->subscribers[0]->get('email'));
        $this->assertSame(array(), $this->modx->invoked);
    }

    public function testPromotesGuestRowWhenUserConfirmsSameEmail()
    {
        $existing = new sxSubscriber($this->modx);
        $existing->fromArray(array(
            'id'            => 1,
            'newsletter_id' => 10,
            'user_id'       => 0,
            'email'         => 'same@example.com',
        ));
        $this->modx->subscribers[] = $existing;

        $this->assertTrue($this->newsletter->subscribe(8, 'same@example.com'));
        $this->assertCount(1, $this->modx->subscribers);
        $this->assertSame(8, (int) $this->modx->subscribers[0]->get('user_id'));
    }

    public function testAnonymousSubscribeResolvesExistingUserByEmail()
    {
        $this->modx->profiles[4] = 'member@example.com';

        $this->assertTrue($this->newsletter->subscribe(0, 'member@example.com'));
        $this->assertCount(1, $this->modx->subscribers);
        $this->assertSame(4, (int) $this->modx->subscribers[0]->get('user_id'));
    }
}
