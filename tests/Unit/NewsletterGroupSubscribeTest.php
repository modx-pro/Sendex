<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../Support/TestableGroupSubscribe.php';

class NewsletterGroupSubscribeTest extends TestCase
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
        TestableGroupSubscribe::$fixtureMembers = array();
    }

    public function testPlanSkipsAlreadySubscribedByUserId()
    {
        $existing = new sxSubscriber($this->modx);
        $existing->fromArray(array(
            'id'            => 1,
            'newsletter_id' => 10,
            'user_id'       => 5,
            'email'         => 'a@example.com',
        ));
        $this->modx->subscribers[] = $existing;

        $plan = sxNewsletterGroupSubscribe::plan(
            array(array(
                'user_id'  => 5,
                'username' => 'alice',
                'email'    => 'a@example.com',
            )),
            10,
            array($existing),
            $this->modx
        );

        $this->assertSame(array(), $plan['insert']);
        $this->assertSame(array(), $plan['promote']);
    }

    public function testPlanSkipsWhenEmailAlreadySubscribedToAnotherUser()
    {
        $existing = new sxSubscriber($this->modx);
        $existing->fromArray(array(
            'id'            => 1,
            'newsletter_id' => 10,
            'user_id'       => 3,
            'email'         => 'shared@example.com',
        ));
        $this->modx->subscribers[] = $existing;

        $plan = sxNewsletterGroupSubscribe::plan(
            array(array(
                'user_id'  => 9,
                'username' => 'bob',
                'email'    => 'shared@example.com',
            )),
            10,
            array($existing),
            $this->modx
        );

        $this->assertSame(array(), $plan['insert']);
        $this->assertSame(array(), $plan['promote']);
    }

    public function testPlanPromotesGuestRowInsteadOfInsert()
    {
        $existing = new sxSubscriber($this->modx);
        $existing->fromArray(array(
            'id'            => 2,
            'newsletter_id' => 10,
            'user_id'       => 0,
            'email'         => 'same@example.com',
        ));
        $this->modx->subscribers[] = $existing;

        $plan = sxNewsletterGroupSubscribe::plan(
            array(array(
                'user_id'  => 8,
                'username' => 'carol',
                'email'    => 'same@example.com',
            )),
            10,
            array($existing),
            $this->modx
        );

        $this->assertSame(array(), $plan['insert']);
        $this->assertCount(1, $plan['promote']);
        $this->assertSame(2, $plan['promote'][0]['subscriber_id']);
        $this->assertSame(8, $plan['promote'][0]['user_id']);
    }

    public function testPlanReportsInvalidEmail()
    {
        $plan = sxNewsletterGroupSubscribe::plan(
            array(array(
                'user_id'  => 3,
                'username' => 'bad',
                'email'    => 'not-an-email',
            )),
            10,
            array(),
            $this->modx
        );

        $this->assertSame(array(), $plan['insert']);
        $this->assertCount(1, $plan['errors']);
        $this->assertStringContainsString('bad:', $plan['errors'][0]);
    }

    public function testSubscribeGroupUsesSingleBulkInsertForManyMembers()
    {
        TestableGroupSubscribe::$fixtureMembers = array();
        for ($i = 1; $i <= 1000; $i++) {
            TestableGroupSubscribe::$fixtureMembers[] = array(
                'user_id'  => $i,
                'username' => 'user' . $i,
                'email'    => 'user' . $i . '@example.com',
            );
        }

        $result = TestableGroupSubscribe::subscribeGroup($this->newsletter, 99);

        $this->assertTrue($result);
        $this->assertCount(1000, $this->modx->subscribers);
        $this->assertSame(2, $this->modx->getConnection()->executeCalls);
        $this->assertSame(array(), $this->modx->invoked);
    }

    public function testSubscribeGroupDoesNotDuplicateExistingMembers()
    {
        $existing = new sxSubscriber($this->modx);
        $existing->fromArray(array(
            'id'            => 1,
            'newsletter_id' => 10,
            'user_id'       => 1,
            'email'         => 'user1@example.com',
        ));
        $this->modx->subscribers[] = $existing;

        TestableGroupSubscribe::$fixtureMembers = array(
            array(
                'user_id'  => 1,
                'username' => 'user1',
                'email'    => 'user1@example.com',
            ),
            array(
                'user_id'  => 2,
                'username' => 'user2',
                'email'    => 'user2@example.com',
            ),
        );

        $result = TestableGroupSubscribe::subscribeGroup($this->newsletter, 1);

        $this->assertTrue($result);
        $this->assertCount(2, $this->modx->subscribers);
        $this->assertSame(1, $this->modx->getConnection()->executeCalls);
    }

    public function testSubscribeGroupPromotesGuestWithoutEvents()
    {
        $existing = new sxSubscriber($this->modx);
        $existing->fromArray(array(
            'id'            => 4,
            'newsletter_id' => 10,
            'user_id'       => 0,
            'email'         => 'guest@example.com',
        ));
        $this->modx->subscribers[] = $existing;

        TestableGroupSubscribe::$fixtureMembers = array(array(
            'user_id'  => 12,
            'username' => 'guest-user',
            'email'    => 'guest@example.com',
        ));

        $result = TestableGroupSubscribe::subscribeGroup($this->newsletter, 1);

        $this->assertTrue($result);
        $this->assertCount(1, $this->modx->subscribers);
        $this->assertSame(12, (int) $this->modx->subscribers[0]->get('user_id'));
        $this->assertSame(0, $this->modx->getConnection()->executeCalls);
        $this->assertSame(array(), $this->modx->invoked);
    }
}
