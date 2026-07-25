<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/core/components/sendex/model/sendex/sxuserplaceholders.class.php';

class UserPlaceholdersTest extends TestCase
{
    public function testMergeWithoutProfileKeepsUserUnderBase(): void
    {
        $out = sxUserPlaceholders::mergeAuthenticated(
            array('id' => 1, 'username' => 'alice', 'email' => 'a@example.com'),
            null,
            array('id' => 9, 'name' => 'Newsletter')
        );

        $this->assertSame(9, $out['id']);
        $this->assertSame('alice', $out['username']);
        $this->assertSame('a@example.com', $out['email']);
        $this->assertSame('Newsletter', $out['name']);
    }

    public function testMergeWithProfileLetsProfileOverrideUserThenBaseWins(): void
    {
        $out = sxUserPlaceholders::mergeAuthenticated(
            array('id' => 1, 'email' => 'user@example.com', 'fullname' => ''),
            array('email' => 'profile@example.com', 'fullname' => 'Alice'),
            array('id' => 9, 'name' => 'Newsletter')
        );

        $this->assertSame(9, $out['id']);
        $this->assertSame('profile@example.com', $out['email']);
        $this->assertSame('Alice', $out['fullname']);
        $this->assertSame('Newsletter', $out['name']);
    }

    public function testSnippetNoLongerTouchesProfileProperty(): void
    {
        $snippet = file_get_contents(
            dirname(__DIR__, 2) . '/core/components/sendex/elements/snippets/snippet.sendex.php'
        );
        $this->assertNotFalse($snippet);
        $this->assertStringNotContainsString('->Profile->', $snippet);
        $this->assertStringContainsString('sxUserProfile::authenticatedPlaceholders', $snippet);
        $this->assertStringNotContainsString("getOne('Profile')", $snippet);
    }
}
