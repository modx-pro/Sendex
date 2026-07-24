<?php

use PHPUnit\Framework\TestCase;

class NewsletterConfirmEmailTest extends TestCase
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
        $this->newsletter->set('active', 1);
        $this->modx->newsletters[10] = $this->newsletter;
    }

    public function testEmptyHashReturnsFalse()
    {
        $this->assertFalse($this->newsletter->confirmEmail(''));
    }

    public function testMissingRegistryEntryReturnsFalse()
    {
        $this->assertFalse($this->newsletter->confirmEmail('unknown'));
    }

    public function testConfirmsForSameNewsletterAndConsumesHash()
    {
        $this->modx->registryEntries['hash1'] = array(
            'user_id'       => 4,
            'newsletter_id' => 10,
            'email'         => 'ok@example.com',
        );

        $this->assertTrue($this->newsletter->confirmEmail('hash1'));
        $this->assertCount(1, $this->modx->subscribers);
        $this->assertSame('sxOnSubscribe', $this->modx->invoked[1][0]);
        $this->assertArrayNotHasKey('hash1', $this->modx->registryEntries);
    }

    public function testConfirmsViaOtherActiveNewsletterAndConsumesHash()
    {
        $other = new TestableNewsletter($this->modx);
        $other->set('id', 20);
        $other->set('active', 1);
        $this->modx->newsletters[20] = $other;

        $this->modx->registryEntries['hash2'] = array(
            'user_id'       => 8,
            'newsletter_id' => 20,
            'email'         => 'other@example.com',
        );

        $this->assertTrue($this->newsletter->confirmEmail('hash2'));
        $this->assertCount(1, $this->modx->subscribers);
        $this->assertSame(20, (int) $this->modx->subscribers[0]->get('newsletter_id'));
        $this->assertArrayNotHasKey('hash2', $this->modx->registryEntries);
    }

    public function testInactiveOtherNewsletterKeepsHash()
    {
        $other = new TestableNewsletter($this->modx);
        $other->set('id', 20);
        $other->set('active', 0);
        $this->modx->newsletters[20] = $other;

        $entry = array(
            'user_id'       => 8,
            'newsletter_id' => 20,
            'email'         => 'other@example.com',
        );
        $this->modx->registryEntries['hash3'] = $entry;

        $this->assertFalse($this->newsletter->confirmEmail('hash3'));
        $this->assertCount(0, $this->modx->subscribers);
        $this->assertArrayHasKey('hash3', $this->modx->registryEntries);
        $this->assertSame(8, $this->modx->registryEntries['hash3']['user_id']);
        $this->assertSame('other@example.com', $this->modx->registryEntries['hash3']['email']);
    }

    public function testPluginCancelKeepsHashForRetry()
    {
        $this->modx->invokeResponses['sxOnBeforeSubscribe'] = array('not allowed');
        $entry = array(
            'user_id'       => 7,
            'newsletter_id' => 10,
            'email'         => 'retry@example.com',
            '_expires'      => time() + 120,
        );
        $this->modx->registryEntries['hash-cancel'] = $entry;

        $this->assertSame('not allowed', $this->newsletter->confirmEmail('hash-cancel'));
        $this->assertCount(0, $this->modx->subscribers);
        $this->assertArrayHasKey('hash-cancel', $this->modx->registryEntries);
        $this->assertSame('retry@example.com', $this->modx->registryEntries['hash-cancel']['email']);
        $this->assertLessThanOrEqual(120, $this->modx->lastRegisterTtl);
        $this->assertGreaterThanOrEqual(118, $this->modx->lastRegisterTtl);
    }

    public function testSaveFailureKeepsHashForRetry()
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
        $this->newsletter->set('active', 1);
        $this->modx->newsletters[10] = $this->newsletter;

        $entry = array(
            'user_id'       => 7,
            'newsletter_id' => 10,
            'email'         => 'fail@example.com',
        );
        $this->modx->registryEntries['hash-fail'] = $entry;

        $this->assertFalse($this->newsletter->confirmEmail('hash-fail'));
        $this->assertCount(0, $this->modx->subscribers);
        $this->assertArrayHasKey('hash-fail', $this->modx->registryEntries);
        $this->assertSame('fail@example.com', $this->modx->registryEntries['hash-fail']['email']);
        $this->assertSame(sxSubscribeRegistry::DEFAULT_TTL, $this->modx->lastRegisterTtl);
    }

    public function testAlreadySubscribedStillConsumesHash()
    {
        $existing = new sxSubscriber($this->modx);
        $existing->fromArray(array(
            'id'            => 1,
            'newsletter_id' => 10,
            'user_id'       => 4,
            'email'         => 'ok@example.com',
        ));
        $this->modx->subscribers[] = $existing;

        $this->modx->registryEntries['hash-dup'] = array(
            'user_id'       => 4,
            'newsletter_id' => 10,
            'email'         => 'ok@example.com',
        );

        $this->assertTrue($this->newsletter->confirmEmail('hash-dup'));
        $this->assertArrayNotHasKey('hash-dup', $this->modx->registryEntries);
    }
}
