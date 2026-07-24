<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/core/components/sendex/model/sendex/sxunsubscriberesolve.class.php';

class UnsubscribeResolveTest extends TestCase
{
    /** @var FakeModX */
    private $modx;

    protected function setUp(): void
    {
        $this->modx = new FakeModX();
    }

    public function testNewsletterIdFromCode()
    {
        $subscriber = new sxSubscriber($this->modx);
        $subscriber->fromArray(array(
            'id'            => 1,
            'newsletter_id' => 7,
            'user_id'       => 0,
            'email'         => 'a@example.com',
            'code'          => 'abc123',
        ));
        $this->modx->subscribers[] = $subscriber;

        $this->assertSame(7, sxUnsubscribeResolve::newsletterIdFromCode($this->modx, 'abc123'));
        $this->assertSame(0, sxUnsubscribeResolve::newsletterIdFromCode($this->modx, 'missing'));
        $this->assertSame(0, sxUnsubscribeResolve::newsletterIdFromCode($this->modx, ''));
    }

    public function testForCodeKeepsMatchingFallback()
    {
        $subscriber = new sxSubscriber($this->modx);
        $subscriber->fromArray(array(
            'id'            => 1,
            'newsletter_id' => 10,
            'code'          => 'same',
            'email'         => 'a@example.com',
        ));
        $this->modx->subscribers[] = $subscriber;

        $newsletter = new TestableNewsletter($this->modx);
        $newsletter->set('id', 10);

        $resolved = sxUnsubscribeResolve::forCode($this->modx, 'same', $newsletter);
        $this->assertSame($newsletter, $resolved);
    }

    public function testForCodeLoadsOwnerWhenSnippetIdDiffers()
    {
        $subscriber = new sxSubscriber($this->modx);
        $subscriber->fromArray(array(
            'id'            => 1,
            'newsletter_id' => 22,
            'code'          => 'cross',
            'email'         => 'a@example.com',
        ));
        $this->modx->subscribers[] = $subscriber;

        $owner = new TestableNewsletter($this->modx);
        $owner->set('id', 22);
        $this->modx->newsletters[22] = $owner;

        $snippetNewsletter = new TestableNewsletter($this->modx);
        $snippetNewsletter->set('id', 1);

        $resolved = sxUnsubscribeResolve::forCode($this->modx, 'cross', $snippetNewsletter);
        $this->assertSame($owner, $resolved);
        $this->assertSame(22, (int) $resolved->get('id'));
    }

    public function testTemplateIncludesNewsletterIdQueryParam()
    {
        $template = file_get_contents(
            dirname(__DIR__, 2) . '/core/components/sendex/elements/templates/template.sendex.tpl'
        );

        $this->assertStringContainsString('sx_action=`unsubscribe`', $template);
        $this->assertStringContainsString('newsletter_id=`[[+newsletter.id]]`', $template);
        $this->assertStringContainsString('code=`[[+subscriber.code]]`', $template);
    }

    public function testSnippetResolvesByCodeBeforeUnsubscribe()
    {
        $snippet = file_get_contents(
            dirname(__DIR__, 2) . '/core/components/sendex/elements/snippets/snippet.sendex.php'
        );

        $this->assertStringContainsString('sxUnsubscribeResolve::forCode', $snippet);
        $this->assertStringContainsString("case 'unsubscribe':", $snippet);
    }
}
