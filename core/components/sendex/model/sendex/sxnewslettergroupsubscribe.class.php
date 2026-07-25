<?php

require_once dirname(__FILE__) . '/sxsubscribercode.class.php';
require_once dirname(__FILE__) . '/sxsubscribermatch.class.php';

/**
 * Bulk subscribe active group members to a newsletter (#70).
 * Mgr sync path: no per-row sxOn*Subscribe events (#44).
 */
class sxNewsletterGroupSubscribe
{
    /** @var int */
    const INSERT_CHUNK = 500;

    /**
     * @param sxNewsletter $newsletter
     * @param int $groupId
     * @return true|string[] true on success, or "username: message" errors
     */
    public static function subscribeGroup(sxNewsletter $newsletter, $groupId)
    {
        $xpdo = $newsletter->xpdo;
        $newsletterId = (int) $newsletter->get('id');
        $groupId = (int) $groupId;

        $members = static::fetchGroupMembers($xpdo, $groupId);
        if ($members === array()) {
            return true;
        }

        /** @var sxSubscriber[] $existing */
        $existing = $xpdo->getCollection('sxSubscriber', array(
            'newsletter_id' => $newsletterId,
        ));

        $plan = self::plan($members, $newsletterId, $existing, $xpdo);
        $errors = $plan['errors'];

        if ($plan['promote'] !== array()) {
            $errors = array_merge($errors, self::promoteGuestRows($xpdo, $plan['promote']));
        }

        if ($plan['insert'] !== array()) {
            $errors = array_merge(
                $errors,
                self::bulkInsert($xpdo, $newsletterId, $plan['insert'])
            );
        }

        return $errors === array() ? true : $errors;
    }

    /**
     * @param array<int,array{user_id:int,username:string,email:string}> $members
     * @param int $newsletterId
     * @param sxSubscriber[] $existing
     * @param object $xpdo
     * @return array{insert:array<int,array{user_id:int,username:string,email:string}>,promote:array<int,array{subscriber_id:int,user_id:int,username:string}>,errors:string[]}
     */
    public static function plan(array $members, $newsletterId, array $existing, $xpdo)
    {
        $newsletterId = (int) $newsletterId;
        $insert = array();
        $promote = array();
        $errors = array();

        foreach ($members as $member) {
            $userId = (int) $member['user_id'];
            $email = sxSubscriberMatch::normalizeEmail($member['email']);
            $username = (string) $member['username'];

            if ($email === '' || !preg_match('/.+@.+\..+/i', $email)) {
                $errors[] = $username . ': ' . $xpdo->lexicon('sendex_subscriber_err_email');
                continue;
            }

            $action = self::resolveAction($newsletterId, $userId, $email, $existing);
            if ($action === 'skip') {
                continue;
            }
            if ($action === 'insert') {
                $insert[] = array(
                    'user_id'  => $userId,
                    'username' => $username,
                    'email'    => $email,
                );
                continue;
            }

            $promote[] = array(
                'subscriber_id' => $action['subscriber_id'],
                'user_id'       => $userId,
                'username'      => $username,
            );
        }

        return array(
            'insert'  => $insert,
            'promote' => $promote,
            'errors'  => $errors,
        );
    }

    /**
     * @param object $xpdo
     * @param int $groupId
     * @return array<int,array{user_id:int,username:string,email:string}>
     */
    public static function fetchGroupMembers($xpdo, $groupId)
    {
        $groupId = (int) $groupId;
        if ($groupId <= 0) {
            return array();
        }

        $c = $xpdo->newQuery('modUser');
        $c->select($xpdo->getSelectColumns('modUser', 'modUser', '', array('id', 'username')));
        $c->select(array(
            'Profile.email' => 'email',
        ));
        $c->innerJoin('modUserGroupMember', 'UserGroupMembers');
        $c->innerJoin(
            'modUserGroup',
            'UserGroup',
            '`UserGroupMembers`.`user_group` = `UserGroup`.`id`'
                . ' AND `UserGroup`.`id` = ' . $groupId
        );
        $c->innerJoin(
            'modUserProfile',
            'Profile',
            '`Profile`.`internalKey` = `modUser`.`id`'
        );
        $c->where(array(
            'modUser.active'  => true,
            'Profile.blocked' => false,
        ));

        $members = array();
        if ($c->prepare() && $c->stmt->execute()) {
            while ($row = $c->stmt->fetch(PDO::FETCH_ASSOC)) {
                $members[] = array(
                    'user_id'  => (int) $row['id'],
                    'username' => (string) $row['username'],
                    'email'    => (string) $row['email'],
                );
            }
        }

        return $members;
    }

    /**
     * @param int $newsletterId
     * @param int $userId
     * @param string $email
     * @param sxSubscriber[] $existing
     * @return string|array{type:string,subscriber_id:int}
     */
    protected static function resolveAction($newsletterId, $userId, $email, array $existing)
    {
        foreach ($existing as $subscriber) {
            $matches = sxSubscriberMatch::rowMatches(
                $newsletterId,
                $userId,
                $email,
                (int) $subscriber->get('newsletter_id'),
                (int) $subscriber->get('user_id'),
                (string) $subscriber->get('email')
            );
            if (!$matches) {
                continue;
            }

            if ($userId > 0 && (int) $subscriber->get('user_id') === 0) {
                return array(
                    'type'          => 'promote',
                    'subscriber_id' => (int) $subscriber->get('id'),
                );
            }

            return 'skip';
        }

        return 'insert';
    }

    /**
     * @param object $xpdo
     * @param array<int,array{subscriber_id:int,user_id:int,username:string}> $rows
     * @return string[]
     */
    protected static function promoteGuestRows($xpdo, array $rows)
    {
        $errors = array();

        foreach ($rows as $row) {
            /** @var sxSubscriber $subscriber */
            $subscriber = $xpdo->getObject('sxSubscriber', (int) $row['subscriber_id']);
            if (!$subscriber) {
                $errors[] = $row['username'] . ': ' . $xpdo->lexicon('sendex_subscriber_err_save');
                continue;
            }

            $subscriber->set('user_id', (int) $row['user_id']);
            if (!$subscriber->save()) {
                $errors[] = $row['username'] . ': ' . $xpdo->lexicon('sendex_subscriber_err_save');
            }
        }

        return $errors;
    }

    /**
     * @param object $xpdo
     * @param int $newsletterId
     * @param array<int,array{user_id:int,username:string,email:string}> $rows
     * @return string[]
     */
    protected static function bulkInsert($xpdo, $newsletterId, array $rows)
    {
        if ($rows === array()) {
            return array();
        }

        $table = $xpdo->getTableName('sxSubscriber');
        $errors = array();

        foreach (array_chunk($rows, self::INSERT_CHUNK) as $chunk) {
            $placeholders = array();
            $values = array();

            foreach ($chunk as $row) {
                $placeholders[] = '(?, ?, ?, ?)';
                $values[] = (int) $newsletterId;
                $values[] = (int) $row['user_id'];
                $values[] = $row['email'];
                $values[] = sxSubscriberCode::generate(
                    $row['user_id'],
                    $newsletterId,
                    $row['email']
                );
            }

            $sql = 'INSERT INTO ' . $table
                . ' (`newsletter_id`, `user_id`, `email`, `code`) VALUES '
                . implode(', ', $placeholders);

            $conn = $xpdo->getConnection();
            $stmt = $conn->prepare($sql);
            if (!$stmt || !$stmt->execute($values)) {
                foreach ($chunk as $row) {
                    $errors[] = $row['username'] . ': ' . $xpdo->lexicon('sendex_subscriber_err_save');
                }
            }
        }

        return $errors;
    }
}
