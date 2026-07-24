<?php

/**
 * Queue row → sxSubscriber link: subscriber_id is always sxSubscriber.id (#52 / #67).
 */
class sxQueueLink
{
    /**
     * Canonical value for new queue rows.
     *
     * @param object $subscriber xPDOObject / stub with get('id')
     * @return int
     */
    public static function subscriberIdFromSubscriber($subscriber)
    {
        return (int) $subscriber->get('id');
    }

    /**
     * Remap legacy queue.subscriber_id (often modUser.id) to sxSubscriber.id.
     *
     * Prefer an existing subscriber PK for the newsletter. Otherwise treat the
     * stored value as user_id. For 0 / guests, fall back to email_to.
     *
     * @param int $stored
     * @param int $newsletterId
     * @param string $emailTo
     * @param callable $findById function (int $id, int $newsletterId): ?object
     * @param callable $findByUserId function (int $userId, int $newsletterId): ?object
     * @param callable $findByEmail function (string $email, int $newsletterId): ?object
     * @return int Resolved sxSubscriber.id, or unchanged $stored when unknown (>0), or 0
     */
    public static function remapStoredSubscriberId(
        $stored,
        $newsletterId,
        $emailTo,
        $findById,
        $findByUserId,
        $findByEmail
    ) {
        $stored = (int) $stored;
        $newsletterId = (int) $newsletterId;

        if ($stored > 0) {
            $byId = call_user_func($findById, $stored, $newsletterId);
            if ($byId !== null) {
                return (int) $byId->get('id');
            }

            $byUser = call_user_func($findByUserId, $stored, $newsletterId);
            if ($byUser !== null) {
                return (int) $byUser->get('id');
            }

            return $stored;
        }

        $email = trim((string) $emailTo);
        if ($email === '') {
            return 0;
        }

        $byEmail = call_user_func($findByEmail, $email, $newsletterId);
        if ($byEmail !== null) {
            return (int) $byEmail->get('id');
        }

        return 0;
    }
}
