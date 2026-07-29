<?php

/**
 * Newsletter mgr grid query: cheap COUNT in prepareQueryBeforeCount, aggregates after (#73).
 */
class sxNewsletterListQuery
{
    /**
     * Filters only — safe for getCount (no JOIN/GROUP BY).
     *
     * @param object $query xPDOQuery / test stub
     * @param array{query?:mixed,combo?:mixed} $properties
     * @return object
     */
    public static function applyFilters($query, array $properties)
    {
        $search = isset($properties['query']) ? (string) $properties['query'] : '';
        if ($search !== '') {
            $query->where(array(
                'name:LIKE'           => '%' . $search . '%',
                'OR:description:LIKE' => '%' . $search . '%',
            ));
        }

        if (!empty($properties['combo'])) {
            $query->where(array('active' => 1));
        }

        return $query;
    }

    /**
     * List SELECT/JOIN — run from prepareQueryAfterCount only.
     * Subscriber totals use a correlated subquery (no GROUP BY) so MySQL
     * ONLY_FULL_GROUP_BY does not break the grid after the first create (#114).
     *
     * @param object $modx
     * @param object $query
     * @param string $classKey
     * @return object
     */
    public static function applyListSelects($modx, $query, $classKey = 'sxNewsletter')
    {
        $query->leftJoin('modTemplate', 'Template');
        $query->select($modx->getSelectColumns($classKey, $classKey));
        $query->select($modx->getSelectColumns('modTemplate', 'Template', '', array('templatename')));

        $subscriberTable = $modx->getTableName('sxSubscriber');
        $query->select(sprintf(
            '(SELECT COUNT(*) FROM %s AS `sxSubCnt` WHERE `sxSubCnt`.`newsletter_id` = %s.id) AS `subscribers`',
            $subscriberTable,
            $classKey
        ));

        return $query;
    }
}
