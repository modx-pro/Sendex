<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/core/components/sendex/model/sendex/sxsubscribermatch.class.php';

class SubscriberMatchTest extends TestCase
{
    public function testRowMatchesOrWhenBothIdentityPartsSet(): void
    {
        $this->assertTrue(sxSubscriberMatch::rowMatches(
            10,
            5,
            'new@example.com',
            10,
            5,
            'old@example.com'
        ));
        $this->assertTrue(sxSubscriberMatch::rowMatches(
            10,
            9,
            'guest@example.com',
            10,
            0,
            'guest@example.com'
        ));
        $this->assertFalse(sxSubscriberMatch::rowMatches(
            10,
            5,
            'a@example.com',
            10,
            8,
            'b@example.com'
        ));
    }

    public function testWhereClauseBuildsOrGroup(): void
    {
        $where = sxSubscriberMatch::whereClause(3, 4, 'a@example.com');
        $this->assertSame(3, $where['newsletter_id']);
        $this->assertSame(
            array(
                'user_id'    => 4,
                'OR:email:=' => 'a@example.com',
            ),
            $where[0]
        );
    }

    public function testResolveUserIdFromProfileEmail(): void
    {
        $modx = new FakeModX();
        $modx->profiles[12] = 'user@example.com';

        $this->assertSame(12, sxSubscriberMatch::resolveUserId($modx, 0, 'user@example.com'));
        $this->assertSame(7, sxSubscriberMatch::resolveUserId($modx, 7, 'user@example.com'));
        $this->assertSame(0, sxSubscriberMatch::resolveUserId($modx, 0, 'missing@example.com'));
    }
}
