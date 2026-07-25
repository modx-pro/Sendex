<?php

/**
 * PDO statement stub for bulk INSERT tests (#70).
 */
class FakePdoStatement
{
    /** @var FakeModX */
    private $modx;

    /** @var FakePdoConnection */
    private $connection;

    /** @var string */
    private $sql;

    /**
     * @param FakeModX $modx
     * @param FakePdoConnection $connection
     * @param string $sql
     */
    public function __construct(FakeModX $modx, FakePdoConnection $connection, $sql)
    {
        $this->modx = $modx;
        $this->connection = $connection;
        $this->sql = $sql;
    }

    /**
     * @param array $values
     * @return bool
     */
    public function execute(array $values = array())
    {
        $this->connection->executeCalls++;
        $this->connection->executions[] = array(
            'sql'    => $this->sql,
            'values' => $values,
        );

        if (!$this->connection->executeResult) {
            return false;
        }

        $rowCount = (int) (count($values) / 4);
        for ($i = 0; $i < $rowCount; $i++) {
            $offset = $i * 4;
            $subscriber = new sxSubscriber($this->modx);
            $subscriber->fromArray(array(
                'id'            => count($this->modx->subscribers) + 1,
                'newsletter_id' => (int) $values[$offset],
                'user_id'       => (int) $values[$offset + 1],
                'email'         => (string) $values[$offset + 2],
                'code'          => (string) $values[$offset + 3],
            ));
            $this->modx->subscribers[] = $subscriber;
        }

        return true;
    }
}
