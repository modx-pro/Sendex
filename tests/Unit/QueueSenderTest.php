<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/core/components/sendex/model/sendex/sxqueuesender.class.php';

class QueueSenderTest extends TestCase
{
    /** @var FakeModX */
    private $modx;

    protected function setUp(): void
    {
        $this->modx = new FakeModX();
    }

    public function testFlushCronStyleContinuesAfterFailure()
    {
        $this->addQueue(1);
        $this->addQueue(2);

        $calls = 0;
        $stats = sxQueueSender::flush($this->modx, array(
            'sendFn' => function () use (&$calls) {
                $calls++;
                if ($calls === 1) {
                    return 'SMTP down';
                }

                return true;
            },
        ));

        $this->assertSame(1, $stats['sent']);
        $this->assertSame(1, $stats['failed']);
        $this->assertSame('SMTP down', $stats['firstError']);
        $this->assertSame(2, $calls);
    }

    public function testFlushStopOnErrorBreaksEarly()
    {
        $this->addQueue(1);
        $this->addQueue(2);

        $calls = 0;
        $stats = sxQueueSender::flush($this->modx, array(
            'stopOnError' => true,
            'sendFn'    => function () use (&$calls) {
                $calls++;

                return 'fail';
            },
        ));

        $this->assertSame(1, $stats['failed']);
        $this->assertSame('fail', $stats['firstError']);
        $this->assertSame(1, $calls);
    }

    public function testFlushRespectsLimitAndCriteria()
    {
        $this->addQueue(1);
        $this->addQueue(2);
        $this->addQueue(3);

        $seen = array();
        sxQueueSender::flush($this->modx, array(
            'criteria' => array('id:IN' => array(1, 3)),
            'limit'    => 1,
            'sendFn'   => function ($queue) use (&$seen) {
                $seen[] = (int) $queue->get('id');

                return true;
            },
        ));

        $this->assertSame(array(1), $seen);
    }

    public function testFlushLogsNonTrueResultsWhenRequested()
    {
        $this->addQueue(5);

        sxQueueSender::flush($this->modx, array(
            'logErrors' => true,
            'sendFn'    => function () {
                return false;
            },
        ));

        $this->assertCount(1, $this->modx->logs);
        $this->assertStringContainsString('queue id 5', $this->modx->logs[0][1]);
        $this->assertStringContainsString('send skipped or failed', $this->modx->logs[0][1]);
    }

    /**
     * @param int $id
     */
    private function addQueue($id)
    {
        $queue = new sxQueue($this->modx);
        $queue->fromArray(array(
            'id'              => $id,
            'newsletter_id'   => 1,
            'subscriber_id'   => 1,
            'email_to'        => 'user' . $id . '@example.com',
            'email_subject'   => 'Hi',
            'email_body'      => 'Body',
            'email_from'      => 'from@example.com',
            'email_from_name' => 'From',
            'email_reply'     => 'from@example.com',
        ));
        $this->modx->queues[] = $queue;
    }
}
