<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/core/components/sendex/model/sendex/sxsubscribercsv.class.php';

class SubscriberCsvTest extends TestCase
{
    public function testResolveFieldsKeepsOnlyAllowed(): void
    {
        $fields = sxSubscriberCsv::resolveFields('email, username, hack, phone');
        $this->assertSame(array('email', 'username', 'phone'), $fields);
    }

    public function testResolveFieldsEmptyWhenNoneValid(): void
    {
        $this->assertSame(array(), sxSubscriberCsv::resolveFields('foo,bar'));
    }

    public function testJoinFlags(): void
    {
        $this->assertTrue(sxSubscriberCsv::needsUserJoin(array('username')));
        $this->assertFalse(sxSubscriberCsv::needsUserJoin(array('email')));
        $this->assertTrue(sxSubscriberCsv::needsProfileJoin(array('email', 'phone')));
        $this->assertFalse(sxSubscriberCsv::needsProfileJoin(array('email')));
    }

    public function testSelectColumns(): void
    {
        $columns = sxSubscriberCsv::selectColumns(array('email', 'username', 'fullname'));
        $this->assertSame(
            array('sxSubscriber.email', 'User.username', 'Profile.fullname'),
            $columns
        );
    }

    public function testEncodeCsv(): void
    {
        $csv = sxSubscriberCsv::encode(array(
            array('email' => 'a@example.com', 'name' => 'Ann'),
            array('email' => 'b@example.com', 'name' => "Be,e"),
        ));
        $this->assertStringContainsString('a@example.com', $csv);
        $this->assertStringContainsString('b@example.com', $csv);
        $this->assertStringContainsString('"Be,e"', $csv);
    }

    public function testEncodeNeutralizesFormulaCells(): void
    {
        $this->assertSame("'=1+1", sxSubscriberCsv::neutralizeCsvCell('=1+1'));
        $this->assertSame("'+1234", sxSubscriberCsv::neutralizeCsvCell('+1234'));
        $this->assertSame('safe@example.com', sxSubscriberCsv::neutralizeCsvCell('safe@example.com'));

        $csv = sxSubscriberCsv::encode(array(
            array('email' => '=1+1', 'name' => 'Ann'),
        ));
        $this->assertStringContainsString("'=1+1", $csv);
    }

    public function testExportProcessorContract(): void
    {
        $processorFile = dirname(__DIR__, 2)
            . '/core/components/sendex/processors/mgr/newsletter/subscriber/export.class.php';
        $source = file_get_contents($processorFile);
        $this->assertNotFalse($source);
        $this->assertStringContainsString("permission = 'edit_document'", $source);
        $this->assertStringNotContainsString('subscribers.csv', $source);
        $this->assertStringNotContainsString('assets_url', $source);
        $this->assertStringNotContainsString('fopen(', $source);
    }
}
