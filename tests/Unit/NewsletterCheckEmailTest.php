<?php

use PHPUnit\Framework\TestCase;

class NewsletterCheckEmailTest extends TestCase
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
        $this->assertFalse($this->newsletter->checkEmail('bad'));
    }

    public function testLoadsEmailFromProfile()
    {
        $this->modx->profiles[3] = 'from-profile@example.com';

        $hash = $this->newsletter->checkEmail('', 3);
        $this->assertIsString($hash);
        $this->assertArrayHasKey($hash, $this->modx->registryEntries);
        $this->assertSame('from-profile@example.com', $this->modx->registryEntries[$hash]['email']);
    }

    public function testReturnsTrueWhenAlreadySubscribed()
    {
        $subscriber = new sxSubscriber($this->modx);
        $subscriber->fromArray(array(
            'id'            => 1,
            'newsletter_id' => 10,
            'user_id'       => 3,
            'email'         => 'a@example.com',
        ));
        $this->modx->subscribers[] = $subscriber;

        $this->assertTrue($this->newsletter->checkEmail('a@example.com', 3));
        $this->assertSame(array(), $this->modx->registryEntries);
    }

    public function testStoresHashInRegistry()
    {
        $hash = $this->newsletter->checkEmail('new@example.com', 9, 600);
        $this->assertIsString($hash);
        $this->assertSame(10, $this->modx->registryEntries[$hash]['newsletter_id']);
        $this->assertSame(9, $this->modx->registryEntries[$hash]['user_id']);
    }
}
