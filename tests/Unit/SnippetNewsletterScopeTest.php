<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2)
    . '/core/components/sendex/model/sendex/sxsubscribeajaxresponse.class.php';

class SnippetNewsletterScopeTest extends TestCase
{
    public function testMatchesNewsletterWhenHiddenFieldMissing()
    {
        $this->assertTrue(sxSubscribeAjaxResponse::matchesNewsletter(1, array(
            'sx_action' => 'subscribe',
        )));
    }

    public function testMatchesNewsletterWhenHiddenFieldMatches()
    {
        $this->assertTrue(sxSubscribeAjaxResponse::matchesNewsletter(5, array(
            'newsletter_id' => '5',
            'sx_action' => 'subscribe',
        )));
    }

    public function testSkipsWhenHiddenFieldTargetsAnotherNewsletter()
    {
        $this->assertFalse(sxSubscribeAjaxResponse::matchesNewsletter(1, array(
            'newsletter_id' => '2',
            'sx_action' => 'subscribe',
        )));
    }

    public function testMatchesRequestRequiresWidgetKeyWhenSet()
    {
        $this->assertTrue(sxSubscribeAjaxResponse::matchesRequest(1, 'instant', array(
            'newsletter_id' => '1',
            'sendex_widget_key' => 'instant',
        )));
        $this->assertFalse(sxSubscribeAjaxResponse::matchesRequest(1, 'instant', array(
            'newsletter_id' => '1',
            'sendex_widget_key' => 'confirm',
        )));
        $this->assertFalse(sxSubscribeAjaxResponse::matchesRequest(1, 'instant', array(
            'newsletter_id' => '1',
            'sx_action' => 'subscribe',
        )));
    }

    public function testEmailLinkWithoutWidgetKeyHandledByDefaultInstanceOnly()
    {
        $request = array(
            'newsletter_id' => '1',
            'sx_action' => 'unsubscribe',
            'code' => 'abc',
        );

        $this->assertTrue(sxSubscribeAjaxResponse::matchesRequest(1, '', $request));
        $this->assertFalse(sxSubscribeAjaxResponse::matchesRequest(1, 'unsubscribe', $request));
    }

    public function testMatchesRequestUsesNewsletterIdNotSubscriberId()
    {
        $request = array(
            'newsletter_id' => '5',
            'sx_action' => 'subscribe',
        );

        $this->assertTrue(sxSubscribeAjaxResponse::matchesRequest(5, '', $request));
        $this->assertFalse(sxSubscribeAjaxResponse::matchesRequest(42, '', $request));
    }
}
