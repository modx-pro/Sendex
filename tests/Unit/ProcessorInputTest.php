<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/core/components/sendex/model/sendex/sxprocessorinput.class.php';

class ProcessorInputTest extends TestCase
{
    public function testParseIdsReturnsUniquePositiveIntegers()
    {
        $this->assertSame(array(1, 2, 10), sxProcessorInput::parseIds('1, 2,10,2'));
        $this->assertSame(array(1, 2, 10), sxProcessorInput::parseIds(array(1, 2, 10, 2)));
        $this->assertSame(array(1, 2, 10), sxProcessorInput::parseIds(array('1', ' 2', '10', '2')));
    }

    public function testParseIdsRejectsEmptyAndZero()
    {
        $this->assertSame(array(), sxProcessorInput::parseIds(''));
        $this->assertSame(array(), sxProcessorInput::parseIds(null));
        $this->assertSame(array(), sxProcessorInput::parseIds('0'));
        $this->assertSame(array(), sxProcessorInput::parseIds(', ,0'));
        $this->assertSame(array(), sxProcessorInput::parseIds(array()));
        $this->assertSame(array(), sxProcessorInput::parseIds(array(0, '', '0')));
    }
}
