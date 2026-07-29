<?php

use PHPUnit\Framework\TestCase;

/**
 * Contract: mgr CSS must not force Font Awesome 5 Free (#111).
 * Icons inherit the font from MODX mgr (2.3+/3.x) or bundled FA4 (&lt;2.3).
 */
class MgrCssIconsContractTest extends TestCase
{
    public function testMainCssDoesNotHardcodeFontAwesome5Free()
    {
        $source = file_get_contents(
            dirname(__DIR__, 2) . '/assets/components/sendex/css/mgr/main.css'
        );

        $this->assertStringNotContainsString('Font Awesome 5 Free', $source);
        $this->assertStringNotContainsString('font-weight: 900', $source);
        $this->assertStringContainsString(
            'ul.sendex-row-actions .btn > .icon',
            $source
        );
        $this->assertStringContainsString('line-height: 1rem', $source);
    }
}
