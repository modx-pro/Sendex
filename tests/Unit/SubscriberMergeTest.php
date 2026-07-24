<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/core/components/sendex/model/sendex/sxsubscribermerge.class.php';

class SubscriberMergeTest extends TestCase
{
    /** @var FakeModX */
    private $modx;

    protected function setUp(): void
    {
        $this->modx = new FakeModX();
    }

    public function testAttachGuestsByEmailPromotesMatchingRows()
    {
        $guestA = $this->guest(1, 10, 'same@example.com');
        $guestB = $this->guest(2, 11, 'SAME@example.com');
        $other = $this->guest(3, 10, 'other@example.com');
        $linked = new sxSubscriber($this->modx);
        $linked->fromArray(array(
            'id'            => 4,
            'newsletter_id' => 12,
            'user_id'       => 9,
            'email'         => 'same@example.com',
        ));

        $this->modx->subscribers = array($guestA, $guestB, $other, $linked);

        $updated = sxSubscriberMerge::attachGuestsByEmail($this->modx, 5, 'same@example.com');

        $this->assertSame(2, $updated);
        $this->assertSame(5, (int) $guestA->get('user_id'));
        $this->assertSame(5, (int) $guestB->get('user_id'));
        $this->assertSame(0, (int) $other->get('user_id'));
        $this->assertSame(9, (int) $linked->get('user_id'));
    }

    public function testAttachGuestsByEmailIgnoresInvalidIdentity()
    {
        $this->modx->subscribers[] = $this->guest(1, 10, 'a@example.com');

        $this->assertSame(0, sxSubscriberMerge::attachGuestsByEmail($this->modx, 0, 'a@example.com'));
        $this->assertSame(0, sxSubscriberMerge::attachGuestsByEmail($this->modx, 5, ''));
        $this->assertSame(0, (int) $this->modx->subscribers[0]->get('user_id'));
    }

    public function testAttachGuestsForUserReadsProfileEmail()
    {
        $this->modx->subscribers[] = $this->guest(1, 10, 'member@example.com');
        $this->modx->profiles[8] = 'member@example.com';

        $user = new modUser($this->modx);
        $user->set('id', 8);

        $this->assertSame(1, sxSubscriberMerge::attachGuestsForUser($this->modx, $user));
        $this->assertSame(8, (int) $this->modx->subscribers[0]->get('user_id'));
    }

    public function testEmailFromUserReturnsEmptyWithoutProfile()
    {
        $user = new modUser($this->modx);
        $user->set('id', 3);

        $this->assertSame('', sxSubscriberMerge::emailFromUser($this->modx, $user));
        $this->assertSame('', sxSubscriberMerge::emailFromUser($this->modx, null));
    }

    public function testPluginSourceRegistersActivateAndSaveEvents()
    {
        $plugin = file_get_contents(
            dirname(__DIR__, 2) . '/core/components/sendex/elements/plugins/plugin.sendex.php'
        );
        $transport = file_get_contents(
            dirname(__DIR__, 2) . '/_build/data/transport.plugins.php'
        );

        $this->assertStringContainsString("case 'OnUserActivate':", $plugin);
        $this->assertStringContainsString("case 'OnBeforeUserActivate':", $plugin);
        $this->assertStringContainsString("case 'OnUserSave':", $plugin);
        $this->assertStringContainsString('sxSubscriberMerge::attachGuestsForUser', $plugin);
        $this->assertStringContainsString("'OnUserActivate'", $transport);
        $this->assertStringContainsString("'OnBeforeUserActivate'", $transport);
        $this->assertStringContainsString("'OnUserSave'", $transport);
    }

    /**
     * @param int $id
     * @param int $newsletterId
     * @param string $email
     * @return sxSubscriber
     */
    private function guest($id, $newsletterId, $email)
    {
        $subscriber = new sxSubscriber($this->modx);
        $subscriber->fromArray(array(
            'id'            => $id,
            'newsletter_id' => $newsletterId,
            'user_id'       => 0,
            'email'         => $email,
        ));

        return $subscriber;
    }
}
