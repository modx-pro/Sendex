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

    public function testTryClaimMarksRowOnce()
    {
        $queue = $this->queue(5, 'a@example.com');
        $this->modx->queues[] = $queue;

        $this->assertTrue(sxQueueClaim::tryClaim($this->modx, 5));
        $this->assertCount(1, $this->modx->queues);
        $this->assertNotEmpty($this->modx->queues[0]->get('claimed_at'));
        $this->assertSame(1, (int) $this->modx->queues[0]->get('attempts'));
        $this->assertNotEmpty($this->modx->queues[0]->get('expires_at'));
        $this->assertFalse(sxQueueClaim::tryClaim($this->modx, 5));
    }

    public function testTryClaimReclaimsExpiredLease()
    {
        $queue = $this->queue(6, 'stale@example.com');
        $queue->set('claimed_at', date('Y-m-d H:i:s', time() - 3600));
        $queue->set('expires_at', date('Y-m-d H:i:s', time() - 60));
        $this->modx->queues[] = $queue;

        $this->assertTrue(sxQueueClaim::tryClaim($this->modx, 6));
        $this->assertSame(1, (int) $this->modx->queues[0]->get('attempts'));
        $this->assertNotEmpty($this->modx->queues[0]->get('expires_at'));
    }

    public function testTryClaimUsesAtomicDeleteWithoutPreloadingRow()
    {
        $this->modx->queues[] = $this->queue(8, 'atomic@example.com');

        $first = sxQueueClaim::tryClaim($this->modx, 8);
        $second = sxQueueClaim::tryClaim($this->modx, 8);

        $this->assertTrue($first);
        $this->assertFalse($second);
        $this->assertSame(0, isset($this->modx->getObjectCalls['sxQueue']) ? $this->modx->getObjectCalls['sxQueue'] : 0);
        $this->assertSame(2, $this->modx->getConnection()->executeCalls);
    }

    public function testTryClaimDoesNotDeleteRowWhenUpdateClaimFails()
    {
        $this->modx->queues[] = $this->queue(10, 'legacy@example.com');
        $this->modx->getConnection()->failClaimUpdate = true;

        $this->assertFalse(sxQueueClaim::tryClaim($this->modx, 10));
        $this->assertCount(1, $this->modx->queues);
        $this->assertSame(1, $this->modx->getConnection()->executeCalls);
    }

    public function testTryClaimFallsBackToDeleteOnLegacyUnknownColumnError()
    {
        $this->modx->queues[] = $this->queue(12, 'legacy-delete@example.com');
        $this->modx->getConnection()->failClaimUpdate = true;
        $this->modx->getConnection()->claimUpdateErrorCode = '42S22';
        $this->modx->getConnection()->claimUpdateErrorMessage = 'Unknown column claimed_at';

        $this->assertTrue(sxQueueClaim::tryClaim($this->modx, 12));
        $this->assertCount(0, $this->modx->queues);
        $this->assertSame(2, $this->modx->getConnection()->executeCalls);
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

    public function testDeliverDeleteFallbackRunsWhenRemoveFails()
    {
        $queue = $this->queue(11, 'force-delete@example.com');
        $queue->removeResult = false;
        $this->modx->queues[] = $queue;

        $result = sxQueueDeliver::send($queue, function () {
            return true;
        });

        $this->assertTrue($result);
        $this->assertCount(0, $this->modx->queues);
        $this->assertSame(2, $this->modx->getConnection()->executeCalls);
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
        $this->assertSame(3, (int) $this->modx->queues[0]->get('id'));
        $this->assertSame(1, (int) $this->modx->queues[0]->get('attempts'));
        $this->assertNull($this->modx->queues[0]->get('claimed_at'));
    }

    public function testDeliverRequeuesOnMailFailureForLegacyDeleteClaim()
    {
        $queue = $this->queue(14, 'legacy-fail@example.com');
        $this->modx->queues[] = $queue;
        $this->modx->getConnection()->failClaimUpdate = true;
        $this->modx->getConnection()->claimUpdateErrorCode = '42S22';
        $this->modx->getConnection()->claimUpdateErrorMessage = 'Unknown column claimed_at';

        $result = sxQueueDeliver::send($queue, function () {
            return 'legacy smtp error';
        });

        $this->assertSame('legacy smtp error', $result);
        $this->assertCount(1, $this->modx->queues);
        $this->assertSame('legacy-fail@example.com', $this->modx->queues[0]->get('email_to'));
    }

    public function testDeliverReleaseClaimFallsBackToSqlWhenSaveFails()
    {
        $queue = $this->queue(13, 'fallback@example.com');
        $queue->saveResult = false;
        $this->modx->queues[] = $queue;

        $result = sxQueueDeliver::send($queue, function () {
            return 'smtp failed';
        });

        $this->assertSame('smtp failed', $result);
        $this->assertNull($this->modx->queues[0]->get('claimed_at'));
        $this->assertNull($this->modx->queues[0]->get('expires_at'));
        $this->assertSame(2, $this->modx->getConnection()->executeCalls);
    }

    public function testDeliverPinsLeaseWhenDeleteFallbackFailsAfterSend()
    {
        $queue = $this->queue(15, 'pin@example.com');
        $queue->removeResult = false;
        $this->modx->getConnection()->failDelete = true;
        $this->modx->queues[] = $queue;

        $result = sxQueueDeliver::send($queue, function () {
            return true;
        });

        $this->assertTrue($result);
        $this->assertCount(1, $this->modx->queues);
        $this->assertNotNull($this->modx->queues[0]->get('claimed_at'));
        $this->assertNull($this->modx->queues[0]->get('expires_at'));
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
            'claimed_at'    => null,
            'attempts'      => 0,
            'expires_at'    => null,
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
