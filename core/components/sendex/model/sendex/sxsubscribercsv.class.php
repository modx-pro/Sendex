<?php

/**
 * Builds subscriber CSV payloads without touching the filesystem.
 */
class sxSubscriberCsv
{
    public const ALLOWED_FIELDS = array(
        'id',
        'user_id',
        'email',
        'username',
        'fullname',
        'phone',
        'mobilephone',
    );

    public const PROFILE_FIELDS = array('fullname', 'phone', 'mobilephone');

    /**
     * @param string $optionCsv Comma-separated setting value
     * @return string[]
     */
    public static function resolveFields($optionCsv)
    {
        $requested = array_filter(array_map('trim', explode(',', (string) $optionCsv)));
        return array_values(array_intersect($requested, self::ALLOWED_FIELDS));
    }

    /**
     * @param string[] $fields
     * @return bool
     */
    public static function needsUserJoin(array $fields)
    {
        return in_array('username', $fields, true);
    }

    /**
     * @param string[] $fields
     * @return bool
     */
    public static function needsProfileJoin(array $fields)
    {
        return (bool) array_intersect($fields, self::PROFILE_FIELDS);
    }

    /**
     * @param string[] $fields
     * @param string $subscriberAlias
     * @return string[]
     */
    public static function selectColumns(array $fields, $subscriberAlias = 'sxSubscriber')
    {
        $columns = array();
        foreach ($fields as $field) {
            if (in_array($field, self::PROFILE_FIELDS, true)) {
                $columns[] = 'Profile.' . $field;
            } elseif ($field === 'username') {
                $columns[] = 'User.' . $field;
            } else {
                $columns[] = $subscriberAlias . '.' . $field;
            }
        }

        return $columns;
    }

    /**
     * @param array[] $rows Associative rows in export field order
     * @return string
     */
    public static function encode(array $rows)
    {
        $fp = fopen('php://temp', 'r+');
        if ($fp === false) {
            return '';
        }

        foreach ($rows as $row) {
            $cells = array();
            foreach (array_values($row) as $value) {
                $cells[] = self::neutralizeCsvCell($value);
            }
            fputcsv($fp, $cells, ',', '"', '\\');
        }
        rewind($fp);
        $csv = stream_get_contents($fp);
        fclose($fp);

        return $csv === false ? '' : $csv;
    }

    /**
     * Prefix formula-like values so Excel/LibreOffice do not execute them.
     *
     * @param mixed $value
     * @return string
     */
    public static function neutralizeCsvCell($value)
    {
        $value = (string) $value;
        if ($value !== '' && preg_match('/^[=+\-@\t\r]/', $value) === 1) {
            return "'" . $value;
        }

        return $value;
    }
}
