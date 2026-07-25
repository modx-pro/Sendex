<?php

use PHPUnit\Framework\TestCase;

/**
 * Structural contract for #62 split of sxNewsletter.
 */
class NewsletterSplitContractTest extends TestCase
{
    public function testNewsletterFacadeStaysUnderTwoHundredLines()
    {
        $path = dirname(__DIR__, 2) . '/core/components/sendex/model/sendex/sxnewsletter.class.php';
        $lines = count(file($path));

        $this->assertLessThanOrEqual(200, $lines, 'sxNewsletter should stay a thin facade');
    }

    public function testExtractedFilesStayUnderThreeHundredLines()
    {
        $base = dirname(__DIR__, 2) . '/core/components/sendex/model/sendex/';
        $files = array(
            'sxnewslettersubscription.class.php',
            'sxnewsletterqueuebuilder.class.php',
            'sxnewsletterqueueusers.class.php',
            'sxnewslettergroupsubscribe.class.php',
            'sxnewsletterlistquery.class.php',
            'sxqueuebodyrenderer.class.php',
            'sxnewslettermailer.class.php',
            'sxsendexevent.class.php',
        );

        foreach ($files as $file) {
            $lines = count(file($base . $file));
            $this->assertLessThanOrEqual(
                300,
                $lines,
                $file . ' should stay under ~300 lines'
            );
        }
    }

    public function testFacadeDelegatesToExtractedClasses()
    {
        $facade = file_get_contents(
            dirname(__DIR__, 2) . '/core/components/sendex/model/sendex/sxnewsletter.class.php'
        );

        $this->assertStringContainsString('sxNewsletterSubscription', $facade);
        $this->assertStringContainsString('sxNewsletterQueueBuilder', $facade);
        $this->assertStringContainsString('sxSendexEvent::invoke', $facade);
        $this->assertStringContainsString('function subscribe(', $facade);
        $this->assertStringContainsString('function subscribeGroup(', $facade);
        $this->assertStringContainsString('sxNewsletterGroupSubscribe', $facade);
        $this->assertStringContainsString('function addQueues(', $facade);
        $this->assertStringNotContainsString('function remove(', $facade);
    }
}
