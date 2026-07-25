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
     * List SELECT/JOIN/GROUP BY — run from prepareQueryAfterCount only.
     *
     * @param object $modx
     * @param object $query
     * @param string $classKey
     * @return object
     */
    public static function applyListSelects($modx, $query, $classKey = 'sxNewsletter')
    {
        $query->leftJoin('modTemplate', 'Template');
        $query->leftJoin('sxSubscriber', 'Subscribers');
        $query->select($modx->getSelectColumns($classKey, $classKey));
        $query->select($modx->getSelectColumns('modTemplate', 'Template', '', array('templatename')));
        $query->select('COUNT(`Subscribers`.`id`) as `subscribers`');
        $query->groupby($classKey . '.id');

        return $query;
    }
}
