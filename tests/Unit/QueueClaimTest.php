<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/core/components/sendex/model/sendex/sxqueueclaim.class.php';
require_once dirname(__DIR__, 2) . '/core/components/sendex/model/sendex/sxqueuedeliver.class.php';

class QueueClaimTest extends TestCase
{
    /** @var FakeModX */
    private $modx;

    protected function setUp(): void
    {
        $this->modx = new FakeModX();
    }

    public function testTryClaimRemovesRowOnce()
    {
        $queue = $this->queue(5, 'a@example.com');
        $this->modx->queues[] = $queue;

        $this->assertTrue(sxQueueClaim::tryClaim($this->modx, 5));
        $this->assertCount(0, $this->modx->queues);
        $this->assertFalse(sxQueueClaim::tryClaim($this->modx, 5));
    }

    public function testTryClaimRejectsInvalidId()
    {
        $this->assertFalse(sxQueueClaim::tryClaim($this->modx, 0));
        $this->assertFalse(sxQueueClaim::tryClaim($this->modx, -1));
    }

    public function testDeliverSendsOnlyOnceAcrossTwoWorkers()
    {
        $queue = $this->queue(9, 'once@example.com');
        $this->modx->queues[] = $queue;

        $sends = 0;
        $mail = function () use (&$sends) {
            $sends++;
            return true;
        };

        $first = sxQueueDeliver::send($queue, $mail);
        $second = sxQueueDeliver::send($queue, $mail);

        $this->assertTrue($first);
        $this->assertFalse($second);
        $this->assertSame(1, $sends);
        $this->assertCount(0, $this->modx->queues);
    }

    public function testDeliverRequeuesOnMailFailure()
    {
        $queue = $this->queue(3, 'fail@example.com');
        $queue->set('email_subject', 'Hi');
        $this->modx->queues[] = $queue;

        $result = sxQueueDeliver::send($queue, function () {
            return 'SMTP down';
        });

        $this->assertSame('SMTP down', $result);
        $this->assertCount(1, $this->modx->queues);
        $this->assertSame('fail@example.com', $this->modx->queues[0]->get('email_to'));
        $this->assertNotSame(3, (int) $this->modx->queues[0]->get('id'));
    }

    /**
     * @param int $id
     * @param string $email
     * @return sxQueue
     */
    private function queue($id, $email)
    {
        $queue = new sxQueue($this->modx);
        $queue->fromArray(array(
            'id'            => $id,
            'newsletter_id' => 1,
            'subscriber_id' => 2,
            'email_to'      => $email,
            'email_subject' => 'S',
            'email_body'    => 'B',
            'email_from'    => 'from@example.com',
            'email_from_name' => 'From',
            'email_reply'   => 'from@example.com',
        ));

        return $queue;
    }
}
