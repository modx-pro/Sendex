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
}
