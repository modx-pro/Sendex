<?php

use PHPUnit\Framework\TestCase;

class QueueSendProcessorTest extends TestCase
{
    /** @var FakeModX */
    private $modx;

    protected function setUp(): void
    {
        $this->modx = new FakeModX();
        require_once dirname(__DIR__, 2)
            . '/core/components/sendex/processors/mgr/queue/send.class.php';
        require_once dirname(__DIR__, 2)
            . '/core/components/sendex/processors/mgr/queue/send_all.class.php';
    }

    public function testSendProcessorRequiresIds()
    {
        $processor = new sxQueueSendProcessor($this->modx, array('ids' => ''));
        $response = $processor->process();

        $this->assertFalse($response['success']);
        $this->assertSame('sendex_queue_err_ns', $response['message']);
    }

    public function testSendProcessorFailsOnMailError()
    {
        $this->addQueue(7);
        /** @var FakeMail $mail */
        $mail = $this->modx->getService('mail');
        $mail->sendResult = false;
        $mail->mailer->ErrorInfo = 'SMTP down';

        $processor = new sxQueueSendProcessor($this->modx, array('ids' => '7'));
        $response = $processor->process();

        $this->assertFalse($response['success']);
        $this->assertSame('SMTP down', $response['message']);
    }

    public function testSendAllProcessorFailsOnFirstError()
    {
        $this->addQueue(1);
        $this->addQueue(2);
        /** @var FakeMail $mail */
        $mail = $this->modx->getService('mail');
        $mail->sendResult = false;
        $mail->mailer->ErrorInfo = 'boom';

        $processor = new sxQueueSendAllProcessor($this->modx);
        $response = $processor->process();

        $this->assertFalse($response['success']);
        $this->assertSame('boom', $response['message']);
        $this->assertTrue($this->hasQueueId(2), 'stopOnError must not send the second row');
    }

    public function testProcessorsDelegateToQueueSender()
    {
        $send = file_get_contents(
            dirname(__DIR__, 2) . '/core/components/sendex/processors/mgr/queue/send.class.php'
        );
        $sendAll = file_get_contents(
            dirname(__DIR__, 2) . '/core/components/sendex/processors/mgr/queue/send_all.class.php'
        );
        $cron = file_get_contents(
            dirname(__DIR__, 2) . '/core/components/sendex/cron/send.php'
        );

        $this->assertStringContainsString('sxQueueSender::flush', $send);
        $this->assertStringContainsString('sxQueueSender::flush', $sendAll);
        $this->assertStringContainsString('sxQueueSender::flush', $cron);
        $this->assertStringNotContainsString('$queue->send()', $send);
        $this->assertStringNotContainsString('$queue->send()', $sendAll);
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

    /**
     * @param int $id
     * @return bool
     */
    private function hasQueueId($id)
    {
        foreach ($this->modx->queues as $queue) {
            if ((int) $queue->get('id') === $id) {
                return true;
            }
        }

        return false;
    }
}
