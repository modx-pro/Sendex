<?php

use PHPUnit\Framework\TestCase;

class NewsletterIsSubscribedTest extends TestCase
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

    public function testReturnsZeroWhenNoMatch()
    {
        $this->assertSame(0, $this->newsletter->isSubscribed(1, 'a@example.com'));
    }

    public function testReturnsSubscriberIdWhenMatched()
    {
        $subscriber = new sxSubscriber($this->modx);
        $subscriber->fromArray(array(
            'id'            => 42,
            'newsletter_id' => 10,
            'user_id'       => 5,
            'email'         => 'a@example.com',
        ));
        $this->modx->subscribers[] = $subscriber;

        $this->assertSame(42, $this->newsletter->isSubscribed(5, 'a@example.com'));
    }

    public function testMatchesByEmailOnly()
    {
        $subscriber = new sxSubscriber($this->modx);
        $subscriber->fromArray(array(
            'id'            => 7,
            'newsletter_id' => 10,
            'user_id'       => 0,
            'email'         => 'guest@example.com',
        ));
        $this->modx->subscribers[] = $subscriber;

        $this->assertSame(7, $this->newsletter->isSubscribed(0, 'guest@example.com'));
    }

    public function testMatchesByUserIdWhenEmailChanged()
    {
        $subscriber = new sxSubscriber($this->modx);
        $subscriber->fromArray(array(
            'id'            => 11,
            'newsletter_id' => 10,
            'user_id'       => 5,
            'email'         => 'old@example.com',
        ));
        $this->modx->subscribers[] = $subscriber;

        $this->assertSame(11, $this->newsletter->isSubscribed(5, 'new@example.com'));
    }

    public function testMatchesGuestEmailWhenUserSubscribesSameAddress()
    {
        $subscriber = new sxSubscriber($this->modx);
        $subscriber->fromArray(array(
            'id'            => 12,
            'newsletter_id' => 10,
            'user_id'       => 0,
            'email'         => 'same@example.com',
        ));
        $this->modx->subscribers[] = $subscriber;

        $this->assertSame(12, $this->newsletter->isSubscribed(9, 'same@example.com'));
    }

    public function testUserIdOnlyUsesProfileEmailForOrLookup()
    {
        $this->modx->profiles[5] = 'profile@example.com';
        $subscriber = new sxSubscriber($this->modx);
        $subscriber->fromArray(array(
            'id'            => 15,
            'newsletter_id' => 10,
            'user_id'       => 0,
            'email'         => 'profile@example.com',
        ));
        $this->modx->subscribers[] = $subscriber;

        $this->assertSame(15, $this->newsletter->isSubscribed(5));
    }
}
