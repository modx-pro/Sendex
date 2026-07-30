<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/core/components/sendex/model/sendex/sxnewslettermailer.class.php';

class NewsletterMailerTest extends TestCase
{
    /** @var FakeModX */
    private $modx;

    protected function setUp(): void
    {
        $this->modx = new FakeModX();
    }

    public function testResolveHeadersUsesNewsletterFields()
    {
        $newsletter = new TestableNewsletter($this->modx);
        $newsletter->set('email_from', 'from@example.com');
        $newsletter->set('email_from_name', 'From');
        $newsletter->set('email_reply', 'reply@example.com');

        $headers = sxNewsletterMailer::resolveHeaders($newsletter, $this->modx);

        $this->assertSame(array(
            'email_from'      => 'from@example.com',
            'email_from_name' => 'From',
            'email_reply'     => 'reply@example.com',
        ), $headers);
    }

    public function testResolveHeadersFallsBackToSiteOptions()
    {
        $headers = sxNewsletterMailer::resolveHeaders(array(), $this->modx);

        $this->assertSame('noreply@example.com', $headers['email_from']);
        $this->assertSame('Example', $headers['email_from_name']);
        $this->assertSame('noreply@example.com', $headers['email_reply']);
    }

    public function testResolveHeadersSanitizesCrLfInHeaderFields()
    {
        $newsletter = new TestableNewsletter($this->modx);
        $newsletter->set('email_from', "from@example.com\r\nBcc:bad@example.com");
        $newsletter->set('email_from_name', "From\r\nTeam");
        $newsletter->set('email_reply', "reply@example.com\nCc:bad@example.com");

        $headers = sxNewsletterMailer::resolveHeaders($newsletter, $this->modx);

        $this->assertSame('from@example.com Bcc:bad@example.com', $headers['email_from']);
        $this->assertSame('From Team', $headers['email_from_name']);
        $this->assertSame('reply@example.com Cc:bad@example.com', $headers['email_reply']);
    }

    public function testBuildMessageForSubscriber()
    {
        $newsletter = new TestableNewsletter($this->modx);
        $newsletter->set('email_from', 'from@example.com');
        $newsletter->set('email_from_name', 'From');
        $newsletter->set('email_reply', 'reply@example.com');

        $subscriber = new sxSubscriber($this->modx);
        $subscriber->fromArray(array(
            'id'            => 1,
            'newsletter_id' => 10,
            'user_id'       => 0,
            'email'         => 'guest@example.com',
        ));

        $message = sxNewsletterMailer::buildMessage(
            $newsletter,
            $subscriber,
            'Hello',
            '<p>Body</p>',
            $this->modx
        );

        $this->assertSame('guest@example.com', $message['email_to']);
        $this->assertSame('Hello', $message['email_subject']);
        $this->assertSame('<p>Body</p>', $message['email_body']);
        $this->assertSame('reply@example.com', $message['email_reply']);
    }

    public function testBuildMessageSanitizesRecipientHeader()
    {
        $newsletter = new TestableNewsletter($this->modx);
        $subscriber = new sxSubscriber($this->modx);
        $subscriber->fromArray(array(
            'id'            => 1,
            'newsletter_id' => 10,
            'user_id'       => 0,
            'email'         => "guest@example.com\r\nBcc:bad@example.com",
        ));

        $message = sxNewsletterMailer::buildMessage(
            $newsletter,
            $subscriber,
            'Hello',
            '<p>Body</p>',
            $this->modx
        );

        $this->assertSame('guest@example.com Bcc:bad@example.com', $message['email_to']);
    }

    public function testBuildActivationMessageUsesEmailReply()
    {
        $message = sxNewsletterMailer::buildActivationMessage(
            $this->modx,
            'guest@example.com',
            array(
                'email_body'      => 'Activate',
                'email_from'      => 'from@example.com',
                'email_from_name' => 'From',
                'email_reply'     => 'reply@example.com',
                'email_subject'   => 'Confirm',
            )
        );

        $this->assertSame('reply@example.com', $message['email_reply']);
        $this->assertSame('Confirm', $message['email_subject']);
    }

    public function testBuildActivationMessageSanitizesToHeader()
    {
        $message = sxNewsletterMailer::buildActivationMessage(
            $this->modx,
            "guest@example.com\nCc:bad@example.com",
            array(
                'email_body' => 'Activate',
            )
        );

        $this->assertSame('guest@example.com Cc:bad@example.com', $message['email_to']);
    }

    public function testConfigureMailerSetsHeadersAndHtml()
    {
        $mail = new FakeMail();
        sxNewsletterMailer::configureMailer($mail, array(
            'email_to'        => 'to@example.com',
            'email_body'      => 'Body',
            'email_from'      => 'from@example.com',
            'email_from_name' => 'From',
            'email_subject'   => 'Subject',
            'email_reply'     => 'reply@example.com',
        ));

        $this->assertSame('Body', $mail->sets[modMail::MAIL_BODY]);
        $this->assertSame('from@example.com', $mail->sets[modMail::MAIL_FROM]);
        $this->assertSame('From', $mail->sets[modMail::MAIL_FROM_NAME]);
        $this->assertSame('Subject', $mail->sets[modMail::MAIL_SUBJECT]);
        $this->assertSame('to@example.com', $mail->addresses['to']);
        $this->assertSame('reply@example.com', $mail->addresses['reply-to']);
    }

    public function testMessageFromQueueSanitizesHeaderFields()
    {
        $queue = new sxQueue($this->modx);
        $queue->fromArray(array(
            'id'              => 1,
            'newsletter_id'   => 10,
            'subscriber_id'   => 1,
            'email_to'        => "to@example.com\r\nBcc:bad@example.com",
            'email_subject'   => 'Subject',
            'email_body'      => 'Body',
            'email_from'      => "from@example.com\nCc:bad@example.com",
            'email_from_name' => "From\r\nTeam",
            'email_reply'     => "reply@example.com\r\nBcc:bad@example.com",
        ));

        $message = sxNewsletterMailer::messageFromQueue($queue);

        $this->assertSame('to@example.com Bcc:bad@example.com', $message['email_to']);
        $this->assertSame('from@example.com Cc:bad@example.com', $message['email_from']);
        $this->assertSame('From Team', $message['email_from_name']);
        $this->assertSame('reply@example.com Bcc:bad@example.com', $message['email_reply']);
    }

    public function testMessageFromQueueUsesNewsletterHeadersWhenLinked()
    {
        $newsletter = new TestableNewsletter($this->modx);
        $newsletter->set('id', 10);
        $newsletter->set('email_from', 'fixed@example.com');
        $newsletter->set('email_from_name', 'Fixed Sender');
        $newsletter->set('email_reply', 'reply-fixed@example.com');
        $this->modx->newsletters[10] = $newsletter;

        $queue = new sxQueue($this->modx);
        $queue->fromArray(array(
            'id'              => 1,
            'newsletter_id'   => 10,
            'subscriber_id'   => 1,
            'email_to'        => 'to@example.com',
            'email_subject'   => 'Subject',
            'email_body'      => 'Body',
            'email_from'      => 'bad-address',
            'email_from_name' => 'Old Name',
            'email_reply'     => 'bad-reply',
        ));

        $message = sxNewsletterMailer::messageFromQueue($queue);

        $this->assertSame('fixed@example.com', $message['email_from']);
        $this->assertSame('Fixed Sender', $message['email_from_name']);
        $this->assertSame('reply-fixed@example.com', $message['email_reply']);
        $this->assertSame('Subject', $message['email_subject']);
    }

    public function testSanitizeHeaderStripsControlCharacters()
    {
        $this->assertSame('from@example.comBcc:bad@example.com', sxNewsletterMailer::sanitizeHeader("from@example.com\x00Bcc:bad@example.com"));
    }

    public function testMailPathsDelegateToNewsletterMailer()
    {
        $base = dirname(__DIR__, 2) . '/core/components/sendex/model/sendex/';
        $sendex = file_get_contents($base . 'sendex.class.php');
        $sender = file_get_contents($base . 'sxqueuesender.class.php');
        $builder = file_get_contents($base . 'sxnewsletterqueuebuilder.class.php');

        $this->assertStringContainsString('sxNewsletterMailer::configureMailer', $sendex);
        $this->assertStringContainsString('sxNewsletterMailer::configureMailer', $sender);
        $mailer = file_get_contents($base . 'sxnewslettermailer.class.php');
        $this->assertStringContainsString('sxNewsletterMailer::resolveHeaders', $builder);
        $this->assertStringContainsString('sxQueueBodyRenderer::renderForQueue', $mailer);
    }
}
