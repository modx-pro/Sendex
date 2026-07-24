<?php

use PHPUnit\Framework\TestCase;

/**
 * #59 — newsletter remove must clear sxQueue for that newsletter_id.
 */
class NewsletterRemoveCascadeTest extends TestCase
{
    /** @var FakeModX */
    private $modx;

    protected function setUp(): void
    {
        $this->modx = new FakeModX();
    }

    public function testDeleteQueuesRemovesOnlyMatchingNewsletter()
    {
        $keep = new sxQueue($this->modx);
        $keep->fromArray(array(
            'id'            => 1,
            'newsletter_id' => 20,
            'email_to'      => 'keep@example.com',
        ));
        $keep->save();

        $drop = new sxQueue($this->modx);
        $drop->fromArray(array(
            'id'            => 2,
            'newsletter_id' => 10,
            'email_to'      => 'drop@example.com',
        ));
        $drop->save();

        $removed = sxNewsletterCascade::deleteQueues($this->modx, 10);

        $this->assertSame(1, $removed);
        $this->assertSame(0, $this->modx->getCount('sxQueue', array('newsletter_id' => 10)));
        $this->assertSame(1, $this->modx->getCount('sxQueue', array('newsletter_id' => 20)));
    }

    public function testNewsletterRemoveClearsQueuesAndSubscribers()
    {
        $newsletter = new TestableNewsletter($this->modx);
        $newsletter->set('id', 10);
        $this->modx->newsletters[10] = $newsletter;

        $subscriber = new sxSubscriber($this->modx);
        $subscriber->fromArray(array(
            'id'            => 1,
            'newsletter_id' => 10,
            'email'         => 'a@example.com',
            'code'          => 'c1',
        ));
        $subscriber->save();

        $queue = new sxQueue($this->modx);
        $queue->fromArray(array(
            'id'            => 5,
            'newsletter_id' => 10,
            'subscriber_id' => 1,
            'email_to'      => 'a@example.com',
        ));
        $queue->save();

        $other = new sxQueue($this->modx);
        $other->fromArray(array(
            'id'            => 6,
            'newsletter_id' => 99,
            'email_to'      => 'other@example.com',
        ));
        $other->save();

        $this->assertTrue($newsletter->remove());
        $this->assertSame(0, $this->modx->getCount('sxQueue', array('newsletter_id' => 10)));
        $this->assertSame(1, $this->modx->getCount('sxQueue', array('newsletter_id' => 99)));
        $this->assertSame(0, $this->modx->getCount('sxSubscriber', array('newsletter_id' => 10)));
        $this->assertArrayNotHasKey(10, $this->modx->newsletters);
    }

    public function testSchemaDeclaresQueuesComposite()
    {
        $schema = file_get_contents(
            dirname(__DIR__, 2) . '/core/components/sendex/model/schema/sendex.mysql.schema.xml'
        );
        $map = file_get_contents(
            dirname(__DIR__, 2) . '/core/components/sendex/model/sendex/mysql/sxnewsletter.map.inc.php'
        );

        $this->assertStringContainsString('alias="Queues"', $schema);
        $this->assertStringContainsString("'Queues'", $map);
    }
}
