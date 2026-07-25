<?php

use PHPUnit\Framework\TestCase;

/**
 * Contract tests for mgr grid selection mixin (#68 / #60).
 */
class MgrGridSelectionContractTest extends TestCase
{
    public function testUtilsExposeSelectionHelpers()
    {
        $source = file_get_contents(
            dirname(__DIR__, 2) . '/assets/components/sendex/js/mgr/misc/utils.js'
        );

        $this->assertStringContainsString('Sendex.utils.getSelectedIds', $source);
        $this->assertStringContainsString('Sendex.utils.requireSelectedIds', $source);
        $this->assertStringContainsString('Sendex.grid.SelectionMixin', $source);
        $this->assertStringContainsString('return ids.length > 0 ? ids : null', $source);
        $this->assertStringContainsString('grid.menu.record', $source);
    }

    public function testGridsUseSelectionMixinAndDropLocalCopyPaste()
    {
        $base = dirname(__DIR__, 2) . '/assets/components/sendex/js/mgr/widgets/';
        $files = array(
            'newsletters.grid.js',
            'queues.grid.js',
        );

        foreach ($files as $file) {
            $source = file_get_contents($base . $file);
            $this->assertStringContainsString('Sendex.grid.SelectionMixin', $source, $file);
            $this->assertStringNotContainsString('_getSelectedIds', $source, $file);
            $this->assertStringContainsString('confirmWithSelection', $source, $file);
            $this->assertStringNotContainsString(
                '}, Sendex.grid.SelectionMixin);',
                $source,
                $file . ' must close Ext.extend(Ext.apply(...)) with ));'
            );
            $this->assertGreaterThan(
                0,
                preg_match_all('/}, Sendex\.grid\.SelectionMixin\)\);/', $source),
                $file
            );
        }
    }

    public function testBatchProcessorsRejectEmptyIds()
    {
        $base = dirname(__DIR__, 2) . '/core/components/sendex/processors/mgr/';
        $files = array(
            'newsletter/remove.class.php',
            'newsletter/enable.class.php',
            'newsletter/disable.class.php',
            'newsletter/subscriber/remove.class.php',
            'queue/remove.class.php',
            'queue/send.class.php',
        );

        foreach ($files as $file) {
            $source = file_get_contents($base . $file);
            $this->assertStringContainsString('requireIds(', $source, $file);
        }
    }

    public function testSelectionEmptyLexiconExists()
    {
        $en = file_get_contents(
            dirname(__DIR__, 2) . '/core/components/sendex/lexicon/en/default.inc.php'
        );
        $ru = file_get_contents(
            dirname(__DIR__, 2) . '/core/components/sendex/lexicon/ru/default.inc.php'
        );

        $this->assertStringContainsString('sendex_selection_err_ns', $en);
        $this->assertStringContainsString('sendex_selection_err_ns', $ru);
    }

    public function testNewsletterGridRenderersEscapeImageAndCloseSpanTags()
    {
        $source = file_get_contents(
            dirname(__DIR__, 2) . '/assets/components/sendex/js/mgr/widgets/newsletters.grid.js'
        );

        $this->assertStringContainsString("'</span>'", $source);
        $this->assertStringContainsString('_escapeHtmlAttr: function(value)', $source);
        $this->assertStringContainsString(".replace(/\"/g, '&quot;')", $source);
        $this->assertStringContainsString(
            "return '<img src=\"' + this._escapeHtmlAttr(val) + '\" alt=\"\" height=\"50\" />';",
            $source
        );
    }
}
