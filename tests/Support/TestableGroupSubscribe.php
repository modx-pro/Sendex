<?php

require_once dirname(__DIR__, 2) . '/core/components/sendex/model/sendex/sxnewslettergroupsubscribe.class.php';

class TestableGroupSubscribe extends sxNewsletterGroupSubscribe
{
    /** @var array<int,array{user_id:int,username:string,email:string}> */
    public static $fixtureMembers = array();

    /**
     * @param object $xpdo
     * @param int $groupId
     * @return array<int,array{user_id:int,username:string,email:string}>
     */
    public static function fetchGroupMembers($xpdo, $groupId)
    {
        return self::$fixtureMembers;
    }
}
