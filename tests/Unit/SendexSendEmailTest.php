<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/core/components/sendex/model/sendex/sendex.class.php';

class SendexSendEmailTest extends TestCase
{
    /** @var FakeModX */
    private $modx;

    protected function setUp(): void
    {
        $this->modx = new class extends FakeModX {
            /** @var object */
            public $lexicon;

            public function __construct()
            {
                parent::__construct();
                $this->lexicon = new class {
                    public function load($topic)
                    {
                    }
                };
            }

            public function addPackage($name, $path)
            {
            }
        };

        $this->modx->options['core_path'] = dirname(__DIR__, 2) . '/core/';
        $this->modx->options['assets_url'] = '/assets/';
    }

    public function testSendEmailReturnsTrueOnSuccess()
    {
        $sendex = new Sendex($this->modx);
        $result = $sendex->sendEmail('user@example.com', array(
            'email_subject' => 'Hello',
            'link' => 'https://example.com/confirm',
            'email_body' => '<p>Body</p>',
        ));

        $this->assertTrue($result);
    }

    public function testSendEmailReturnsMailerErrorOnFailure()
    {
        /** @var FakeMail $mail */
        $mail = $this->modx->getService('mail');
        $mail->sendResult = false;
        $mail->mailer->ErrorInfo = 'SMTP down';

        $sendex = new Sendex($this->modx);
        $result = $sendex->sendEmail('user@example.com', array(
            'email_subject' => 'Hello',
            'link' => 'https://example.com/confirm',
            'email_body' => '<p>Body</p>',
        ));

        $this->assertSame('SMTP down', $result);
    }
}
