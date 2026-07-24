<?php

use PHPUnit\Framework\TestCase;

class QueueAddProcessorTest extends TestCase
{
    /** @var FakeModX */
    private $modx;

    protected function setUp(): void
    {
        $this->modx = new FakeModX();
        require_once dirname(__DIR__, 2)
            . '/core/components/sendex/processors/mgr/queue/add.class.php';
    }

    public function testFailureWhenNoQueuesCreated()
    {
        $newsletter = new TestableNewsletter($this->modx);
        $newsletter->set('id', 10);
        $newsletter->set('template', 1);
        $this->modx->newsletters[10] = $newsletter;
        $this->modx->templates[1] = new modTemplate();

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

        $processor = new sxQueueAddProcessor($this->modx, array('newsletter_id' => 10));
        $response = $processor->process();

        $this->assertFalse($response['success']);
        $this->assertSame('sendex_newsletter_err_no_queues', $response['message']);
    }

    public function testSuccessReportsCreatedCount()
    {
        $newsletter = new TestableNewsletter($this->modx);
        $newsletter->set('id', 10);
        $newsletter->set('template', 1);
        $newsletter->set('email_subject', 'Hello');
        $newsletter->set('email_from', 'from@example.com');
        $this->modx->newsletters[10] = $newsletter;
        $this->modx->templates[1] = new modTemplate();

        $subscriber = new sxSubscriber($this->modx);
        $subscriber->fromArray(array(
            'id'            => 1,
            'newsletter_id' => 10,
            'user_id'       => 0,
            'email'         => 'guest@example.com',
        ));
        $this->modx->subscribers[] = $subscriber;

        $processor = new sxQueueAddProcessor($this->modx, array('newsletter_id' => 10));
        $response = $processor->process();

        $this->assertTrue($response['success']);
        $this->assertSame(1, $response['object']['count']);
        $this->assertStringContainsString('count=1', $response['message']);
    }
}
