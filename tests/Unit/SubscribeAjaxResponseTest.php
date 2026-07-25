<?php

use PHPUnit\Framework\TestCase;

class SubscribeAjaxResponseTest extends TestCase
{
    protected function setUp(): void
    {
        require_once dirname(__DIR__, 2)
            . '/core/components/sendex/model/sendex/sxsubscribeajaxresponse.class.php';
        unset($_SERVER['HTTP_X_REQUESTED_WITH'], $_REQUEST['ajax'], $_REQUEST['sendex_ajax']);
    }

    protected function tearDown(): void
    {
        unset($_SERVER['HTTP_X_REQUESTED_WITH'], $_REQUEST['ajax'], $_REQUEST['sendex_ajax']);
    }

    public function testDetectsXmlHttpRequestHeader()
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

        $this->assertTrue(sxSubscribeAjaxResponse::isRequest());
    }

    public function testDetectsAjaxRequestParam()
    {
        $_REQUEST['ajax'] = '1';

        $this->assertTrue(sxSubscribeAjaxResponse::isRequest());
    }

    public function testPayloadMapsSuccessAndMessage()
    {
        $payload = sxSubscribeAjaxResponse::payload(
            array('message' => 'OK', 'error' => 0),
            '<div>widget</div>'
        );

        $this->assertTrue($payload['success']);
        $this->assertSame('OK', $payload['message']);
        $this->assertSame('<div>widget</div>', $payload['html']);
    }

    public function testPayloadMarksValidationErrorsAsFailure()
    {
        $payload = sxSubscribeAjaxResponse::payload(
            array('message' => 'Bad email', 'error' => 1),
            '<div>widget</div>'
        );

        $this->assertFalse($payload['success']);
        $this->assertSame('Bad email', $payload['message']);
    }

    public function testParseEnabledRecognizesOffValues()
    {
        $this->assertFalse(sxSubscribeAjaxResponse::parseEnabled('0'));
        $this->assertTrue(sxSubscribeAjaxResponse::parseEnabled('1'));
    }
}
