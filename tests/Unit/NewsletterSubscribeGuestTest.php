<?php

use PHPUnit\Framework\TestCase;

class NewsletterSubscribeGuestTest extends TestCase
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

    public function testImmediateSubscribeWithoutConfirm()
    {
        $result = $this->newsletter->subscribeGuest('guest@example.com', 0, 1800, false);

        $this->assertSame('subscribed', $result['status']);
        $this->assertCount(1, $this->modx->subscribers);
        $this->assertSame(array(), $this->modx->registryEntries);
        $this->assertSame('guest', $this->modx->invoked[0][1]['source']);
        $this->assertSame('guest', $this->modx->invoked[1][1]['source']);
    }

    public function testConfirmFlowStoresHashWhenRequired()
    {
        $result = $this->newsletter->subscribeGuest('guest@example.com', 0, 600, true);

        $this->assertSame('confirm', $result['status']);
        $this->assertArrayHasKey('hash', $result);
        $this->assertArrayHasKey($result['hash'], $this->modx->registryEntries);
        $this->assertCount(0, $this->modx->subscribers);
    }

    public function testConfirmFlowReturnsRateLimitedWhenWindowActive()
    {
        sxSubscribeRegistry::touchConfirmRateLimit($this->modx, 'guest@example.com', 120);

        $result = $this->newsletter->subscribeGuest('guest@example.com', 0, 600, true, 120);

        $this->assertSame('rate_limited', $result['status']);
    }

    public function testAlreadySubscribedReturnsAlready()
    {
        $subscriber = new sxSubscriber($this->modx);
        $subscriber->fromArray(array(
            'id'            => 1,
            'newsletter_id' => 10,
            'user_id'       => 0,
            'email'         => 'guest@example.com',
        ));
        $this->modx->subscribers[] = $subscriber;

        $withConfirm = $this->newsletter->subscribeGuest('guest@example.com', 0, 1800, true);
        $withoutConfirm = $this->newsletter->subscribeGuest('guest@example.com', 0, 1800, false);

        $this->assertSame('already', $withConfirm['status']);
        $this->assertSame('subscribed', $withoutConfirm['status']);
    }

    public function testInvalidEmailReturnsInvalid()
    {
        $result = $this->newsletter->subscribeGuest('bad', 0, 1800, false);

        $this->assertSame('invalid', $result['status']);
    }

    public function testPluginCancelReturnsErrorStatus()
    {
        $this->modx->invokeResponses['sxOnBeforeSubscribe'] = array('blocked');

        $result = $this->newsletter->subscribeGuest('guest@example.com', 0, 1800, false);

        $this->assertSame('error', $result['status']);
        $this->assertSame('blocked', $result['message']);
    }
}
