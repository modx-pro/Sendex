<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/core/components/sendex/model/sendex/sxqueuebodyrenderer.class.php';
require_once dirname(__DIR__, 2) . '/core/components/sendex/model/sendex/sxqueuesender.class.php';

class QueueBodyRendererTest extends TestCase
{
    /** @var FakeModX */
    private $modx;

    protected function setUp(): void
    {
        $this->modx = new FakeModX();
    }

    public function testUsesStoredBodyWhenNonEmpty()
    {
        $queue = new sxQueue($this->modx);
        $queue->set('email_body', '<p>legacy</p>');

        $this->assertTrue(sxQueueBodyRenderer::usesStoredBody($queue));
    }

    public function testCompactRowHasEmptyBody()
    {
        $queue = new sxQueue($this->modx);
        $queue->set('email_body', '');

        $this->assertFalse(sxQueueBodyRenderer::usesStoredBody($queue));
    }

    public function testRenderForQueuePersonalizesSubscriber()
    {
        $newsletter = new TestableNewsletter($this->modx);
        $newsletter->set('id', 1);
        $newsletter->set('template', 1);
        $this->modx->newsletters[1] = $newsletter;
        $this->modx->templates[1] = new modTemplate();

        $subscriber = new sxSubscriber($this->modx);
        $subscriber->fromArray(array(
            'id'            => 5,
            'newsletter_id' => 1,
            'user_id'       => 0,
            'email'         => 'guest@example.com',
        ));
        $this->modx->subscribers[] = $subscriber;

        $queue = new sxQueue($this->modx);
        $queue->fromArray(array(
            'newsletter_id' => 1,
            'subscriber_id' => 5,
            'email_to'      => 'guest@example.com',
            'email_body'    => '',
        ));

        $this->assertSame('Body for guest@example.com', sxQueueBodyRenderer::renderForQueue($this->modx, $queue));
    }

    public function testDeliverMailUsesStoredBodyForLegacyRows()
    {
        $queue = new sxQueue($this->modx);
        $queue->fromArray(array(
            'id'            => 1,
            'email_to'      => 'legacy@example.com',
            'email_subject' => 'Hi',
            'email_body'    => 'Stored HTML',
            'email_from'    => 'from@example.com',
            'email_from_name' => 'From',
            'email_reply'   => 'from@example.com',
        ));

        /** @var FakeMail $mail */
        $mail = $this->modx->getService('mail');
        $this->assertTrue(sxQueueSender::deliverMail($queue));
        $this->assertSame('Stored HTML', $mail->sets[modMail::MAIL_BODY]);
    }

    public function testDeliverMailRendersCompactBodyAtSendTime()
    {
        $newsletter = new TestableNewsletter($this->modx);
        $newsletter->set('id', 1);
        $newsletter->set('template', 1);
        $this->modx->newsletters[1] = $newsletter;
        $this->modx->templates[1] = new modTemplate();

        $subscriber = new sxSubscriber($this->modx);
        $subscriber->fromArray(array(
            'id'            => 2,
            'newsletter_id' => 1,
            'user_id'       => 0,
            'email'         => 'send@example.com',
        ));
        $this->modx->subscribers[] = $subscriber;

        $queue = new sxQueue($this->modx);
        $queue->fromArray(array(
            'id'              => 2,
            'newsletter_id'   => 1,
            'subscriber_id'   => 2,
            'email_to'        => 'send@example.com',
            'email_subject'   => 'Hi',
            'email_body'      => '',
            'email_from'      => 'from@example.com',
            'email_from_name' => 'From',
            'email_reply'     => 'from@example.com',
        ));

        /** @var FakeMail $mail */
        $mail = $this->modx->getService('mail');
        $this->assertTrue(sxQueueSender::deliverMail($queue));
        $this->assertSame('Body for send@example.com', $mail->sets[modMail::MAIL_BODY]);
    }

    public function testFlattenNestedResourceLinksRewritesSiteStartAndNumericNesting()
    {
        $nested = '<a href="[[~[[++site_start]]?scheme=`full`&sx_action=`unsubscribe`]]">x</a>';
        $this->assertSame(
            '<a href="[[~57?scheme=`full`&sx_action=`unsubscribe`]]">x</a>',
            sxQueueBodyRenderer::flattenNestedResourceLinks($nested, 57)
        );

        $residual = '[[~[[57]]?code=`abc`]]';
        $this->assertSame(
            '[[~57?code=`abc`]]',
            sxQueueBodyRenderer::flattenNestedResourceLinks($residual, 57)
        );
    }

    public function testBuildUnsubscribeUrlUsesSiteStartAndParams()
    {
        $this->modx->options['site_start'] = 57;

        $newsletter = new TestableNewsletter($this->modx);
        $newsletter->set('id', 1);

        $subscriber = new sxSubscriber($this->modx);
        $subscriber->fromArray(array(
            'id'            => 2,
            'newsletter_id' => 1,
            'code'          => 'deadbeef',
            'email'         => 'a@example.com',
        ));

        $url = sxQueueBodyRenderer::buildUnsubscribeUrl($this->modx, $newsletter, $subscriber);

        $this->assertStringContainsString('id=57', $url);
        $this->assertStringContainsString('sx_action=unsubscribe', $url);
        $this->assertStringContainsString('newsletter_id=1', $url);
        $this->assertStringContainsString('code=deadbeef', $url);
    }

    public function testBuildUnsubscribeUrlPrefersSendexUnsubscribePage()
    {
        $this->modx->options['site_start'] = 57;
        $this->modx->options['sendex_unsubscribe_page'] = 12;

        $newsletter = new TestableNewsletter($this->modx);
        $newsletter->set('id', 3);

        $subscriber = new sxSubscriber($this->modx);
        $subscriber->fromArray(array(
            'id'            => 4,
            'newsletter_id' => 3,
            'code'          => 'c0de',
            'email'         => 'b@example.com',
        ));

        $url = sxQueueBodyRenderer::buildUnsubscribeUrl($this->modx, $newsletter, $subscriber);
        $this->assertStringContainsString('id=12', $url);
        $this->assertStringNotContainsString('id=57', $url);
    }
}
