<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/core/components/sendex/processors/mgr/likequery.class.php';

class LikeQueryTest extends TestCase
{
    public function testPrepareReturnsNullForEmpty(): void
    {
        $this->assertNull(SendexLikeQuery::prepare(''));
        $this->assertNull(SendexLikeQuery::prepare('   '));
        $this->assertNull(SendexLikeQuery::prepare(null));
    }

    public function testPrepareWrapsAndEscapesWildcards(): void
    {
        $this->assertSame('%alice%', SendexLikeQuery::prepare('alice'));
        $this->assertSame('%100\\%%', SendexLikeQuery::prepare('100%'));
        $this->assertSame('%a\\_b%', SendexLikeQuery::prepare('a_b'));
    }
}
