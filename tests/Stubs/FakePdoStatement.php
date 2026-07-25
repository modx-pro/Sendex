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

    /** @var int */
    private $affectedRows = 0;

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
            $this->affectedRows = 0;
            return false;
        }

        if (stripos($this->sql, 'DELETE FROM') === 0) {
            $this->affectedRows = $this->deleteQueueRow($values);

            return true;
        }

        $rowCount = (int) (count($values) / 4);
        $this->affectedRows = $rowCount;
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

    /**
     * @return int
     */
    public function rowCount()
    {
        return $this->affectedRows;
    }

    /**
     * @param array $values
     * @return int
     */
    private function deleteQueueRow(array $values)
    {
        $id = isset($values[0]) ? (int) $values[0] : 0;
        if ($id <= 0) {
            return 0;
        }

        foreach ($this->modx->queues as $index => $queue) {
            if ((int) $queue->get('id') !== $id) {
                continue;
            }

            unset($this->modx->queues[$index]);
            $this->modx->queues = array_values($this->modx->queues);

            return 1;
        }

        return 0;
    }
}
