<?php

/**
 * Newsletter mgr grid query: cheap COUNT in prepareQueryBeforeCount (#73).
 * Row extras (subscriber total, template name) are added in prepareRow — no JOIN/GROUP BY/subquery (#114).
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
     * Enrich a newsletter row for the mgr grid (subscriber count + template label).
     *
     * @param modX $modx
     * @param array $array newsletter row from sxNewsletter::toArray()
     * @return array
     */
    public static function enrichRow($modx, array $array)
    {
        $newsletterId = isset($array['id']) ? (int) $array['id'] : 0;
        $array['subscribers'] = $newsletterId > 0
            ? (int) $modx->getCount('sxSubscriber', array('newsletter_id' => $newsletterId))
            : 0;

        $array['templatename'] = '';
        $templateId = isset($array['template']) ? (int) $array['template'] : 0;
        if ($templateId > 0) {
            $template = $modx->getObject('modTemplate', $templateId);
            if ($template) {
                $array['templatename'] = (string) $template->get('templatename');
            }
        }

        return $array;
    }
}
