<?php

use PHPUnit\Framework\TestCase;

/**
 * #59 — newsletter remove must clear sxQueue for that newsletter_id (Queues composite).
 */
class NewsletterRemoveCascadeTest extends TestCase
{
    /** @var FakeModX */
    private $modx;

    protected function setUp(): void
    {
        $this->modx = new FakeModX();
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

        $this->assertCount(1, $newsletter->getMany('Queues'));

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
        $this->assertStringContainsString('owner="local"', $schema);
        $this->assertStringContainsString("'Queues'", $map);
    }
}
