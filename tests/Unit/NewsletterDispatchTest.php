<?php

use PHPUnit\Framework\TestCase;

class NewsletterDispatchTest extends TestCase
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
        $this->newsletter->set('template', 1);
        $this->newsletter->set('email_subject', 'Hello');
        $this->newsletter->set('email_from', 'from@example.com');
        $this->modx->templates[1] = new modTemplate();
        require_once dirname(__DIR__, 2)
            . '/core/components/sendex/model/sendex/sxnewsletterdispatch.class.php';
    }

    public function testFailsWhenNoSubscribersAndNoPendingQueues()
    {
        $result = sxNewsletterDispatch::queueAndSend($this->newsletter);

        $this->assertFalse($result['success']);
        $this->assertSame('sendex_newsletter_err_no_subscribers', $result['message']);
        $this->assertSame(0, $result['queued']);
        $this->assertSame(0, $result['sent']);
    }

    public function testQueuesAndSendsForGuestSubscriber()
    {
        $subscriber = new sxSubscriber($this->modx);
        $subscriber->fromArray(array(
            'id'            => 1,
            'newsletter_id' => 10,
            'user_id'       => 0,
            'email'         => 'guest@example.com',
        ));
        $this->modx->subscribers[] = $subscriber;

        $result = sxNewsletterDispatch::queueAndSend($this->newsletter, array(
            'sendFn' => function () {
                return true;
            },
        ));

        $this->assertTrue($result['success']);
        $this->assertSame(1, $result['queued']);
        $this->assertSame(1, $result['sent']);
        $this->assertStringContainsString('queued=1', $result['message']);
        $this->assertStringContainsString('sent=1', $result['message']);
    }

    public function testFlushesExistingQueuesWhenAddQueuesCreatesNothing()
    {
        $subscriber = new sxSubscriber($this->modx);
        $subscriber->fromArray(array(
            'id'            => 1,
            'newsletter_id' => 10,
            'user_id'       => 5,
            'email'         => 'user@example.com',
        ));
        $this->modx->subscribers[] = $subscriber;

        $user = new modUser($this->modx);
        $user->set('id', 5);
        $user->active = false;
        $this->modx->users[5] = $user;

        $queue = new sxQueue($this->modx);
        $queue->fromArray(array(
            'id'              => 99,
            'newsletter_id'   => 10,
            'subscriber_id'   => 1,
            'email_to'        => 'user@example.com',
            'email_subject'   => 'Hello',
            'email_body'      => '',
            'email_from'      => 'from@example.com',
            'email_from_name' => 'From',
            'email_reply'     => 'from@example.com',
        ));
        $this->modx->queues[] = $queue;

        $result = sxNewsletterDispatch::queueAndSend($this->newsletter, array(
            'sendFn' => function () {
                return true;
            },
        ));

        $this->assertTrue($result['success']);
        $this->assertSame(0, $result['queued']);
        $this->assertSame(1, $result['sent']);
    }

    public function testFailsWhenAddQueuesCreatesNothingAndNoPendingQueues()
    {
        $subscriber = new sxSubscriber($this->modx);
        $subscriber->fromArray(array(
            'id'            => 1,
            'newsletter_id' => 10,
            'user_id'       => 5,
            'email'         => 'user@example.com',
        ));
        $this->modx->subscribers[] = $subscriber;

        $user = new modUser($this->modx);
        $user->set('id', 5);
        $user->active = true;
        $this->modx->users[5] = $user;

        $result = sxNewsletterDispatch::queueAndSend($this->newsletter);

        $this->assertFalse($result['success']);
        $this->assertSame('sendex_newsletter_err_no_queues', $result['message']);
    }

    public function testReturnsMailErrorFromFlush()
    {
        $subscriber = new sxSubscriber($this->modx);
        $subscriber->fromArray(array(
            'id'            => 1,
            'newsletter_id' => 10,
            'user_id'       => 0,
            'email'         => 'guest@example.com',
        ));
        $this->modx->subscribers[] = $subscriber;

        $result = sxNewsletterDispatch::queueAndSend($this->newsletter, array(
            'sendFn' => function () {
                return 'SMTP down';
            },
        ));

        $this->assertFalse($result['success']);
        $this->assertSame('SMTP down', $result['message']);
        $this->assertSame(1, $result['queued']);
        $this->assertSame(0, $result['sent']);
    }
}
