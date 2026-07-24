<?php

use PHPUnit\Framework\TestCase;

/**
 * Regression coverage map for critical bug-fixes (#72).
 */
class RegressionCoverageContractTest extends TestCase
{
    public function testCriticalBugFixesHaveFocusedUnitTests()
    {
        $base = dirname(__DIR__) . '/Unit/';
        $required = array(
            52 => 'QueueLinkTest.php',
            55 => 'QueueClaimTest.php',
            58 => 'NewsletterConfirmEmailTest.php',
            61 => 'SubscriberCodeTest.php',
        );

        foreach ($required as $issue => $file) {
            $this->assertFileExists(
                $base . $file,
                'Issue #' . $issue . ' needs focused test ' . $file
            );
        }
    }

    public function testConfirmEmailRestoresHashOnFailure()
    {
        $source = file_get_contents(
            dirname(__DIR__) . '/Unit/NewsletterConfirmEmailTest.php'
        );
        $this->assertStringContainsString('testPluginCancelKeepsHashForRetry', $source);
        $this->assertStringContainsString('testSaveFailureKeepsHashForRetry', $source);
    }

    public function testSubscriberCodeStaysStableOnSave()
    {
        $source = file_get_contents(
            dirname(__DIR__) . '/Unit/SubscriberCodeTest.php'
        );
        $this->assertStringContainsString('testSaveKeepsExistingCode', $source);
    }

    public function testQueueSubscriberIdUsesPrimaryKey()
    {
        $source = file_get_contents(
            dirname(__DIR__) . '/Unit/QueueLinkTest.php'
        );
        $this->assertStringContainsString('testSubscriberIdFromSubscriberUsesPrimaryKey', $source);
    }
}
