<?php

use PHPUnit\Framework\TestCase;

class SnippetSubscribeConfirmContractTest extends TestCase
{
    public function testSnippetUsesSubscribeGuestAndConfirmHelper()
    {
        $source = file_get_contents(
            dirname(__DIR__, 2) . '/core/components/sendex/elements/snippets/snippet.sendex.php'
        );

        $this->assertStringContainsString('sxSubscribeConfirm::isRequired', $source);
        $this->assertStringContainsString('subscribeGuest(', $source);
        $this->assertStringNotContainsString('->checkEmail(', $source);
    }
}
