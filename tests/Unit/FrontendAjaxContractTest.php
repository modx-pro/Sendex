<?php

use PHPUnit\Framework\TestCase;

class FrontendAjaxContractTest extends TestCase
{
    public function testSnippetUsesJsonAjaxResponse()
    {
        $snippet = file_get_contents(
            dirname(__DIR__, 2) . '/core/components/sendex/elements/snippets/snippet.sendex.php'
        );

        $this->assertStringContainsString('sxSubscribeAjaxResponse::isRequest', $snippet);
        $this->assertStringContainsString('sxSubscribeAjaxResponse::send', $snippet);
        $this->assertStringContainsString('regClientScript', $snippet);
        $this->assertStringNotContainsString('exit($output)', $snippet);
    }

    public function testDefaultChunksExposeAjaxHooks()
    {
        $base = dirname(__DIR__, 2) . '/core/components/sendex/elements/chunks/';
        $files = array(
            'chunk.subscribe.guest.tpl',
            'chunk.subscribe.auth.tpl',
            'chunk.unsubscribe.tpl',
        );

        foreach ($files as $file) {
            $source = file_get_contents($base . $file);
            $this->assertStringContainsString('data-sendex-widget', $source, $file);
            $this->assertStringContainsString('data-sendex-form', $source, $file);
            $this->assertStringContainsString('data-sendex-message', $source, $file);
        }
    }

    public function testFrontendJsUsesFetchAndJson()
    {
        $source = file_get_contents(
            dirname(__DIR__, 2) . '/assets/components/sendex/js/web/sendex.js'
        );

        $this->assertStringContainsString('fetch(', $source);
        $this->assertStringContainsString('response.json()', $source);
        $this->assertStringContainsString('data-sendex-form', $source);
        $this->assertStringContainsString('XMLHttpRequest', $source);
    }
}
