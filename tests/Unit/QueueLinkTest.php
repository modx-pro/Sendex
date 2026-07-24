<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/core/components/sendex/model/sendex/sxqueuelink.class.php';

class QueueLinkTest extends TestCase
{
    public function testSubscriberIdFromSubscriberUsesPrimaryKey()
    {
        $modx = new FakeModX();
        $subscriber = new sxSubscriber($modx);
        $subscriber->fromArray(array(
            'id'      => 42,
            'user_id' => 7,
            'email'   => 'a@example.com',
        ));

        $this->assertSame(42, sxQueueLink::subscriberIdFromSubscriber($subscriber));
    }

    public function testRemapKeepsValidSubscriberPrimaryKey()
    {
        $byId = function ($id, $newsletterId) {
            if ($id === 42 && $newsletterId === 3) {
                return $this->subscriber(42, 7, 'a@example.com');
            }

            return null;
        };

        $resolved = sxQueueLink::remapStoredSubscriberId(
            42,
            3,
            'a@example.com',
            $byId,
            function () {
                return null;
            },
            function () {
                return null;
            }
        );

        $this->assertSame(42, $resolved);
    }

    public function testRemapLegacyUserIdToSubscriberId()
    {
        $resolved = sxQueueLink::remapStoredSubscriberId(
            7,
            3,
            'a@example.com',
            function () {
                return null;
            },
            function ($userId, $newsletterId) {
                if ($userId === 7 && $newsletterId === 3) {
                    return $this->subscriber(42, 7, 'a@example.com');
                }

                return null;
            },
            function () {
                return null;
            }
        );

        $this->assertSame(42, $resolved);
    }

    public function testRemapZeroUsesEmail()
    {
        $resolved = sxQueueLink::remapStoredSubscriberId(
            0,
            3,
            'guest@example.com',
            function () {
                return null;
            },
            function () {
                return null;
            },
            function ($email, $newsletterId) {
                if ($email === 'guest@example.com' && $newsletterId === 3) {
                    return $this->subscriber(9, 0, 'guest@example.com');
                }

                return null;
            }
        );

        $this->assertSame(9, $resolved);
    }

    public function testRemapUnknownPositiveStoredLeftUnchanged()
    {
        $resolved = sxQueueLink::remapStoredSubscriberId(
            99,
            3,
            'x@example.com',
            function () {
                return null;
            },
            function () {
                return null;
            },
            function () {
                return null;
            }
        );

        $this->assertSame(99, $resolved);
    }

    /**
     * @param int $id
     * @param int $userId
     * @param string $email
     * @return sxSubscriber
     */
    private function subscriber($id, $userId, $email)
    {
        $modx = new FakeModX();
        $subscriber = new sxSubscriber($modx);
        $subscriber->fromArray(array(
            'id'      => $id,
            'user_id' => $userId,
            'email'   => $email,
        ));

        return $subscriber;
    }
}
