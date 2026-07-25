<?php

use PHPUnit\Framework\TestCase;

class NewsletterUpdateProcessorTest extends TestCase
{
    public function testUpdatePreservesActiveWhenPropertyOmitted()
    {
        $source = file_get_contents(
            dirname(__DIR__, 2) . '/core/components/sendex/processors/mgr/newsletter/update.class.php'
        );

        $this->assertStringContainsString("array_key_exists('active', \$this->getProperties())", $source);
        $this->assertStringNotContainsString(
            "\$active = \$this->getProperty('active');\n        \$this->setProperty('active', !empty(\$active)",
            $source
        );
    }
}
