<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/core/components/sendex/model/sendex/sxnewsletterlistquery.class.php';

class NewsletterGetListQueryTest extends TestCase
{
    /** @var FakeModX */
    private $modx;

    protected function setUp(): void
    {
        $this->modx = new FakeModX();
    }

    public function testApplyFiltersDoesNotJoinOrGroup()
    {
        $query = new FakeQuery('sxNewsletter');

        sxNewsletterListQuery::applyFilters($query, array(
            'query' => 'weekly',
            'combo' => 1,
        ));

        $this->assertSame(array(), $query->joins);
        $this->assertNull($query->groupby);
        $this->assertArrayHasKey('name:LIKE', $query->where);
        $this->assertSame(1, $query->where['active']);
    }

    public function testApplyListSelectsAddsTemplateJoinAndSubscriberSubquery()
    {
        $query = new FakeQuery('sxNewsletter');

        sxNewsletterListQuery::applyListSelects($this->modx, $query, 'sxNewsletter');

        $this->assertCount(1, $query->joins);
        $this->assertSame('modTemplate', $query->joins[0][0]);
        $this->assertNull($query->groupby);
        $this->assertGreaterThanOrEqual(3, count($query->selects));
        $subscriberSelect = end($query->selects);
        $this->assertStringContainsString('SELECT COUNT(*)', $subscriberSelect);
        $this->assertStringContainsString('newsletter_id', $subscriberSelect);
        $this->assertStringNotContainsString('GROUP BY', strtoupper(implode(' ', $query->selects)));
    }

    public function testGetListProcessorUsesAfterCountForAggregates()
    {
        $source = file_get_contents(
            dirname(__DIR__, 2) . '/core/components/sendex/processors/mgr/newsletter/getlist.class.php'
        );

        $this->assertStringContainsString('prepareQueryAfterCount', $source);
        $this->assertStringContainsString('sxNewsletterListQuery::applyListSelects', $source);
        $this->assertStringContainsString('sxNewsletterListQuery::applyFilters', $source);
        $this->assertStringNotContainsString('groupby', $this->beforeCountBody($source));
    }

    /**
     * @param string $source
     * @return string
     */
    private function beforeCountBody($source)
    {
        if (!preg_match('/function prepareQueryBeforeCount\\([^)]*\\)\\s*\\{(.*?)\\n\\s*\\}/s', $source, $matches)) {
            return $source;
        }

        return $matches[1];
    }
}
