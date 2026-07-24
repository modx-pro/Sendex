<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/core/components/sendex/model/sendex/sxnewsletterqueueusers.class.php';

class NewsletterQueueUsersTest extends TestCase
{
    /** @var FakeModX */
    private $modx;

    protected function setUp(): void
    {
        $this->modx = new FakeModX();
    }

    public function testLoadContextsReturnsEligibleUsersOnly()
    {
        $active = new modUser($this->modx);
        $active->set('id', 1);
        $active->active = true;
        $this->modx->users[1] = $active;

        $blocked = new modUser($this->modx);
        $blocked->set('id', 2);
        $blocked->active = true;
        $this->modx->users[2] = $blocked;

        $profile1 = new modUserProfile($this->modx);
        $profile1->set('internalKey', 1);
        $profile1->set('blocked', false);
        $profile1->set('email', 'a@example.com');
        $this->modx->userProfiles[1] = $profile1;

        $profile2 = new modUserProfile($this->modx);
        $profile2->set('internalKey', 2);
        $profile2->set('blocked', true);
        $profile2->set('email', 'b@example.com');
        $this->modx->userProfiles[2] = $profile2;

        $contexts = sxNewsletterQueueUsers::loadContexts($this->modx, array(1, 2, 99));

        $this->assertSame(array(1, 2), $contexts['loadedIds']);
        $this->assertArrayHasKey(1, $contexts['eligible']);
        $this->assertArrayNotHasKey(2, $contexts['eligible']);
        $this->assertSame('a@example.com', $contexts['eligible'][1]['profile']['email']);
    }

    public function testLoadContextsUsesTwoBatchQueries()
    {
        for ($i = 1; $i <= 50; $i++) {
            $user = new modUser($this->modx);
            $user->set('id', $i);
            $user->active = true;
            $this->modx->users[$i] = $user;

            $profile = new modUserProfile($this->modx);
            $profile->set('internalKey', $i);
            $profile->set('blocked', false);
            $profile->set('email', 'user' . $i . '@example.com');
            $this->modx->userProfiles[$i] = $profile;
        }

        sxNewsletterQueueUsers::loadContexts($this->modx, range(1, 50));

        $this->assertSame(1, $this->modx->getCollectionCalls['modUser']);
        $this->assertSame(1, $this->modx->getCollectionCalls['modUserProfile']);
        $this->assertSame(0, isset($this->modx->getObjectCalls['modUser']) ? $this->modx->getObjectCalls['modUser'] : 0);
    }
}
