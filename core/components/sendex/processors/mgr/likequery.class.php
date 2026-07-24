<?php

/**
 * Shared LIKE helpers for mgr getlist processors.
 */
class SendexLikeQuery
{
    /**
     * Trim query and wrap for LIKE, escaping % and _.
     *
     * @param mixed $query
     *
     * @return string|null LIKE value, or null if empty after trim
     */
    public static function prepare($query)
    {
        $query = trim((string) $query);
        if ($query === '') {
            return null;
        }

        return '%' . str_replace(array('%', '_'), array('\\%', '\\_'), $query) . '%';
    }
}
