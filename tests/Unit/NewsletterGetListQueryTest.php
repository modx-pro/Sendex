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

    public function testEnrichRowAddsSubscriberCountAndTemplateName()
    {
        $this->modx->newsletters[] = new sxNewsletter($this->modx);
        $this->modx->newsletters[0]->fromArray(array(
            'id'       => 5,
            'name'     => 'Weekly',
            'template' => 2,
        ));

        $subscriber = new sxSubscriber($this->modx);
        $subscriber->fromArray(array('id' => 1, 'newsletter_id' => 5));
        $this->modx->subscribers[] = $subscriber;

        $subscriber = new sxSubscriber($this->modx);
        $subscriber->fromArray(array('id' => 2, 'newsletter_id' => 5));
        $this->modx->subscribers[] = $subscriber;

        $template = new class {
            /** @var array<string,string> */
            private $data = array('templatename' => 'Mail layout');

            /**
             * @param string $key
             * @return string|null
             */
            public function get($key)
            {
                return isset($this->data[$key]) ? $this->data[$key] : null;
            }
        };
        $this->modx->templates[2] = $template;

        $row = sxNewsletterListQuery::enrichRow($this->modx, array(
            'id'       => 5,
            'template' => 2,
        ));

        $this->assertSame(2, $row['subscribers']);
        $this->assertSame('Mail layout', $row['templatename']);
    }

    public function testGetListProcessorUsesFiltersOnlyBeforeCount()
    {
        $source = file_get_contents(
            dirname(__DIR__, 2) . '/core/components/sendex/processors/mgr/newsletter/getlist.class.php'
        );

        $this->assertStringContainsString('sxNewsletterListQuery::applyFilters', $source);
        $this->assertStringContainsString('sxNewsletterListQuery::enrichRow', $source);
        $this->assertStringNotContainsString('prepareQueryAfterCount', $source);
        $this->assertStringNotContainsString('groupby', $this->beforeCountBody($source));
    }

    public function testGetListProcessorAllowsViewSendexPermission()
    {
        $source = file_get_contents(
            dirname(__DIR__, 2) . '/core/components/sendex/processors/mgr/newsletter/getlist.class.php'
        );

        $this->assertStringContainsString("hasPermission('view_sendex')", $source);
        $this->assertStringContainsString("hasPermission('view_document')", $source);
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
