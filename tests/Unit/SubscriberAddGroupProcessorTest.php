<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../Support/TestableGroupSubscribe.php';

class SubscriberAddGroupProcessorTest extends TestCase
{
    /** @var FakeModX */
    private $modx;

    /** @var sxSubscriberAddGroupProcessor */
    private $processor;

    protected function setUp(): void
    {
        $this->modx = new FakeModX();
        $newsletter = new TestableNewsletter($this->modx);
        $newsletter->set('id', 10);
        $this->modx->newsletters[10] = $newsletter;
        $this->processor = new sxSubscriberAddGroupProcessor($this->modx);
        TestableGroupSubscribe::$fixtureMembers = array();
    }

    public function testRequiresGroupAndNewsletter()
    {
        $result = $this->processor->process();
        $this->assertFalse($result['success']);
        $this->assertSame('sendex_subscriber_err_group', $result['message']);
    }

    public function testRequiresPermission()
    {
        $this->modx->permissions['edit_document'] = false;
        $this->processor->properties = array(
            'group_id'      => 3,
            'newsletter_id' => 10,
        );
        $result = $this->processor->process();
        $this->assertFalse($result['success']);
        $this->assertSame('access_denied', $result['message']);
    }

    public function testFailsWhenNewsletterMissing()
    {
        $this->processor->properties = array(
            'group_id'      => 3,
            'newsletter_id' => 99,
        );
        $result = $this->processor->process();
        $this->assertFalse($result['success']);
        $this->assertSame('sendex_newsletter_err_nf', $result['message']);
    }

    public function testDelegatesToSubscribeGroup()
    {
        $newsletter = new class ($this->modx) extends TestableNewsletter {
            public function subscribeGroup($group_id = 0)
            {
                TestableGroupSubscribe::$fixtureMembers = array(array(
                    'user_id'  => 7,
                    'username' => 'member',
                    'email'    => 'member@example.com',
                ));

                return TestableGroupSubscribe::subscribeGroup($this, $group_id);
            }
        };
        $newsletter->set('id', 10);
        $this->modx->newsletters[10] = $newsletter;

        $this->processor->properties = array(
            'group_id'      => 3,
            'newsletter_id' => 10,
        );
        $result = $this->processor->process();

        $this->assertTrue($result['success']);
        $this->assertCount(1, $this->modx->subscribers);
    }
}
