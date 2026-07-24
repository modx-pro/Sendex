<?php

/**
 * Resolve the newsletter that owns an unsubscribe code (#56).
 */
class sxUnsubscribeResolve
{
    /**
     * @param object $xpdo
     * @param string $code
     * @return int newsletter id or 0
     */
    public static function newsletterIdFromCode($xpdo, $code)
    {
        $code = is_string($code) ? trim($code) : '';
        if ($code === '') {
            return 0;
        }

        /** @var object|null $subscriber */
        $subscriber = $xpdo->getObject('sxSubscriber', array('code' => $code));
        if (!$subscriber) {
            return 0;
        }

        return (int) $subscriber->get('newsletter_id');
    }

    /**
     * Prefer the newsletter that owns $code; keep $fallback when it already matches.
     *
     * @param object $xpdo
     * @param string $code
     * @param object|null $fallbackNewsletter sxNewsletter
     * @return object|null
     */
    public static function forCode($xpdo, $code, $fallbackNewsletter = null)
    {
        $newsletterId = self::newsletterIdFromCode($xpdo, $code);
        if ($newsletterId <= 0) {
            return $fallbackNewsletter;
        }

        if (
            $fallbackNewsletter !== null
            && (int) $fallbackNewsletter->get('id') === $newsletterId
        ) {
            return $fallbackNewsletter;
        }

        return $xpdo->getObject('sxNewsletter', $newsletterId);
    }
}
