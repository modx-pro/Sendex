<?php

use PHPUnit\Framework\TestCase;

class NewsletterAddQueuesTest extends TestCase
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

    public function testNoSubscribersReturnsLexiconKey()
    {
        $this->assertSame(
            'sendex_newsletter_err_no_subscribers',
            $this->newsletter->addQueues()
        );
    }

    public function testMissingTemplateReturnsLexiconKey()
    {
        $this->newsletter->set('template', 0);
        $subscriber = new sxSubscriber($this->modx);
        $subscriber->fromArray(array(
            'id'            => 1,
            'newsletter_id' => 10,
            'user_id'       => 0,
            'email'         => 'a@example.com',
        ));
        $this->modx->subscribers[] = $subscriber;

        $this->assertSame(
            'sendex_newsletter_err_no_template',
            $this->newsletter->addQueues()
        );
    }

    public function testQueuesEmailForGuestSubscriber()
    {
        $subscriber = new sxSubscriber($this->modx);
        $subscriber->fromArray(array(
            'id'            => 1,
            'newsletter_id' => 10,
            'user_id'       => 0,
            'email'         => 'guest@example.com',
        ));
        $this->modx->subscribers[] = $subscriber;

        $this->assertSame(1, $this->newsletter->addQueues());
        $this->assertCount(1, $this->modx->queues);
        $this->assertSame('guest@example.com', $this->modx->queues[0]->get('email_to'));
        $this->assertSame('Hello', $this->modx->queues[0]->get('email_subject'));
        $this->assertSame(1, $this->modx->queues[0]->get('subscriber_id'));
    }

    public function testSkipsUserWithoutProfile()
    {
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

        $this->assertSame('sendex_newsletter_err_no_queues', $this->newsletter->addQueues());
        $this->assertCount(0, $this->modx->queues);
    }

    public function testSkipsInactiveUser()
    {
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
        $user->active = false;
        $this->modx->users[5] = $user;

        $profile = new modUserProfile($this->modx);
        $profile->set('blocked', false);
        $profile->set('email', 'user@example.com');
        $this->modx->userProfiles[5] = $profile;

        $this->assertSame('sendex_newsletter_err_no_queues', $this->newsletter->addQueues());
        $this->assertCount(0, $this->modx->queues);
    }

    public function testQueuesForActiveUserWithProfile()
    {
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

        $profile = new modUserProfile($this->modx);
        $profile->set('blocked', false);
        $profile->set('email', 'user@example.com');
        $this->modx->userProfiles[5] = $profile;

        $this->assertSame(1, $this->newsletter->addQueues());
        $this->assertCount(1, $this->modx->queues);
        $this->assertSame('Body for user@example.com', $this->modx->queues[0]->get('email_body'));
        // Must be sxSubscriber.id (1), not modUser.id (5)
        $this->assertSame(1, $this->modx->queues[0]->get('subscriber_id'));
    }

    public function testSchemaQueueIndexNameMatchesSubscriberIdColumn()
    {
        $schema = file_get_contents(
            dirname(__DIR__, 2) . '/core/components/sendex/model/schema/sendex.mysql.schema.xml'
        );

        $this->assertStringContainsString('defaultEngine="InnoDB"', $schema);
        $this->assertStringContainsString(
            'alias="subscriber_id" name="subscriber_id"',
            $schema
        );
        $this->assertStringNotContainsString(
            'alias="subscriber_id" name="user_id"',
            $schema
        );
    }
}
