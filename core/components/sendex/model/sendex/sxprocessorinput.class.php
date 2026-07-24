<?php

/**
 * Normalized mgr processor input (#69).
 */
class sxProcessorInput
{
    /**
     * Parse comma-separated ids into unique positive integers.
     *
     * @param mixed $raw
     * @return int[]
     */
    public static function parseIds($raw)
    {
        if ($raw === null || $raw === '') {
            return array();
        }

        $ids = array();
        foreach (explode(',', (string) $raw) as $part) {
            $id = (int) trim($part);
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }
}
