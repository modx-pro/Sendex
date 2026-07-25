<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/core/components/sendex/model/sendex/sxqueuesender.class.php';
require_once dirname(__DIR__, 2) . '/core/components/sendex/model/sendex/sxqueuedeliver.class.php';
require_once dirname(__DIR__, 2) . '/core/components/sendex/model/sendex/sxmodxcompat.class.php';

class QueueLifecycleEventsTest extends TestCase
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
        $this->newsletter->set('email_from_name', 'From');
        $this->newsletter->set('email_reply', 'reply@example.com');
        $this->modx->templates[1] = new modTemplate();
    }

    public function testBeforeAddQueuesCancelPreventsQueueRows()
    {
        $this->addGuestSubscriber();
        $this->modx->invokeResponses['sxOnBeforeAddQueues'] = array('blocked');

        $this->assertSame('blocked', $this->newsletter->addQueues());
        $this->assertCount(0, $this->modx->queues);
        $this->assertSame('sxOnBeforeAddQueues', $this->modx->invoked[0][0]);
    }

    public function testAddQueuesFiresAfterEventWithCreatedCount()
    {
        $this->addGuestSubscriber();

        $this->assertSame(1, $this->newsletter->addQueues());
        $names = array_column($this->modx->invoked, 0);
        $this->assertContains('sxOnBeforeAddQueues', $names);
        $this->assertContains('sxOnAddQueues', $names);
        $after = null;
        foreach ($this->modx->invoked as $item) {
            if ($item[0] === 'sxOnAddQueues') {
                $after = $item[1];
            }
        }
        $this->assertSame(1, $after['created']);
    }

    public function testBeforeQueueSendCancelSkipsWithoutRequeue()
    {
        $queue = $this->queueRow(5);
        $this->modx->queues[] = $queue;
        $this->modx->invokeResponses['sxOnBeforeQueueSend'] = array('skip me');

        $result = sxQueueSender::sendOne($queue);

        $this->assertFalse($result);
        $this->assertCount(0, $this->modx->queues);
        $names = array_column($this->modx->invoked, 0);
        $this->assertContains('sxOnBeforeQueueSend', $names);
        $this->assertNotContains('sxOnQueueSend', $names);
        $this->assertNotContains('sxOnQueueSendFailed', $names);
    }

    public function testBeforeQueueSendCanMutateMessageBody()
    {
        $queue = $this->queueRow(6);
        $queue->set('email_body', '<p>orig</p>');
        $this->modx->queues[] = $queue;

        $this->modx->invokeMutators['sxOnBeforeQueueSend'] = function (array &$params) {
            $params['message']['email_body'] = '<p>tracked</p>';
        };

        $mail = new FakeMail();
        $this->modx->services['mail'] = $mail;

        $this->assertTrue(sxQueueSender::sendOne($queue));
        $this->assertSame('<p>tracked</p>', $mail->sets[sxModxCompat::mailConst('BODY')]);
        $names = array_column($this->modx->invoked, 0);
        $this->assertContains('sxOnQueueSend', $names);
    }

    public function testQueueSendFailedFiresOnMailError()
    {
        $queue = $this->queueRow(7);
        $queue->set('email_body', 'body');
        $this->modx->queues[] = $queue;

        $mail = new FakeMail();
        $mail->sendResult = false;
        $this->modx->services['mail'] = $mail;

        $result = sxQueueSender::sendOne($queue);

        $this->assertSame('SMTP down', $result);
        $this->assertCount(1, $this->modx->queues);
        $names = array_column($this->modx->invoked, 0);
        $this->assertContains('sxOnQueueSendFailed', $names);
        $this->assertNotContains('sxOnQueueSend', $names);
    }

    public function testFlushCompleteFiresWithStats()
    {
        $this->modx->queues[] = $this->queueRow(1);
        $this->modx->queues[] = $this->queueRow(2);

        $stats = sxQueueSender::flush($this->modx, array(
            'criteria' => array('newsletter_id' => 10),
            'sendFn'   => function () {
                return true;
            },
        ));

        $this->assertSame(2, $stats['sent']);
        $found = null;
        foreach ($this->modx->invoked as $item) {
            if ($item[0] === 'sxOnQueueFlushComplete') {
                $found = $item[1];
            }
        }
        $this->assertNotNull($found);
        $this->assertSame(10, $found['newsletter_id']);
        $this->assertSame(2, $found['stats']['sent']);
    }

    public function testTransportRegistersQueueEvents()
    {
        $transport = file_get_contents(
            dirname(__DIR__, 2) . '/_build/data/transport.events.php'
        );
        $eventNames = array(
            'sxOnBeforeAddQueues',
            'sxOnAddQueues',
            'sxOnBeforeQueueSend',
            'sxOnQueueSend',
            'sxOnQueueSendFailed',
            'sxOnQueueFlushComplete',
        );
        foreach ($eventNames as $name) {
            $this->assertStringContainsString("'" . $name . "'", $transport);
        }
    }

    private function addGuestSubscriber()
    {
        $subscriber = new sxSubscriber($this->modx);
        $subscriber->fromArray(array(
            'id'            => 1,
            'newsletter_id' => 10,
            'user_id'       => 0,
            'email'         => 'guest@example.com',
        ));
        $this->modx->subscribers[] = $subscriber;
    }

    /**
     * @param int $id
     * @return sxQueue
     */
    private function queueRow($id)
    {
        $queue = new sxQueue($this->modx);
        $queue->fromArray(array(
            'id'              => $id,
            'newsletter_id'   => 10,
            'subscriber_id'   => 1,
            'email_to'        => 'a@example.com',
            'email_subject'   => 'Hi',
            'email_body'      => '<p>hi</p>',
            'email_from'      => 'from@example.com',
            'email_from_name' => 'From',
            'email_reply'     => 'from@example.com',
        ));

        return $queue;
    }
}
