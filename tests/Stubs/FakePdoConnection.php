<?php

/**
 * PDO stub for bulk INSERT tests (#70).
 */
class FakePdoConnection
{
    /** @var FakeModX */
    private $modx;

    /** @var int */
    public $prepareCalls = 0;

    /** @var int */
    public $executeCalls = 0;

    /** @var bool */
    public $executeResult = true;

    /** @var bool */
    public $failClaimUpdate = false;

    /** @var string */
    public $claimUpdateErrorCode = 'HY000';

    /** @var string */
    public $claimUpdateErrorMessage = 'Simulated claim update failure';

    /** @var bool */
    public $failDelete = false;

    /** @var array<int,array{sql:string,values:array}> */
    public $executions = array();

    /**
     * @param FakeModX $modx
     */
    public function __construct(FakeModX $modx)
    {
        $this->modx = $modx;
    }

    /**
     * @param string $sql
     * @return FakePdoStatement
     */
    public function prepare($sql)
    {
        $this->prepareCalls++;

        return new FakePdoStatement($this->modx, $this, $sql);
    }
}
