<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/core/components/sendex/model/sendex/sxsubscribecsrf.class.php';

class SubscribeCsrfTest extends TestCase
{
    /** @var FakeModX */
    private $modx;

    protected function setUp(): void
    {
        $this->modx = new FakeModX();
        $_SESSION = array();
    }

    public function testIsRequiredUsesSnippetOverrideAndSetting()
    {
        $this->modx->options['sendex_csrf_protect'] = false;

        $this->assertTrue(sxSubscribeCsrf::isRequired($this->modx, array('csrfProtect' => '1')));
        $this->assertFalse(sxSubscribeCsrf::isRequired($this->modx, array('csrfProtect' => '0')));
        $this->assertFalse(sxSubscribeCsrf::isRequired($this->modx, array()));
    }

    public function testTokenAndValidation()
    {
        $token = sxSubscribeCsrf::token();

        $this->assertNotSame('', $token);
        $this->assertTrue(sxSubscribeCsrf::isValid($token));
        $this->assertFalse(sxSubscribeCsrf::isValid('wrong'));
    }
}
