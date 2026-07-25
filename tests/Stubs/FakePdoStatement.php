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

    /** @var array */
    private $lastErrorInfo = array('00000', null, null);

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
            if (!empty($this->connection->failDelete)) {
                $this->affectedRows = 0;
                $this->lastErrorInfo = array('HY000', null, 'Simulated delete failure');
                return false;
            }
            $this->affectedRows = $this->deleteQueueRow($values);

            return true;
        }

        if (
            stripos($this->sql, 'UPDATE ') === 0
            && strpos($this->sql, 'claimed_at') !== false
            && strpos($this->sql, 'attempts = attempts + 1') !== false
        ) {
            if (!empty($this->connection->failClaimUpdate)) {
                $this->affectedRows = 0;
                $this->lastErrorInfo = array(
                    $this->connection->claimUpdateErrorCode,
                    null,
                    $this->connection->claimUpdateErrorMessage,
                );
                return false;
            }
            $this->affectedRows = $this->claimQueueRow($values);

            return true;
        }

        if (
            stripos($this->sql, 'UPDATE ') === 0
            && strpos($this->sql, 'SET claimed_at = NULL, expires_at = NULL') !== false
        ) {
            $this->affectedRows = $this->releaseClaimRow($values);

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
     * @return array
     */
    public function errorInfo()
    {
        return $this->lastErrorInfo;
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

    /**
     * @param array $values
     * @return int
     */
    private function claimQueueRow(array $values)
    {
        $id = isset($values[0]) ? (int) $values[0] : 0;
        if ($id <= 0) {
            return 0;
        }

        foreach ($this->modx->queues as $queue) {
            if ((int) $queue->get('id') !== $id) {
                continue;
            }
            $claimedAt = $queue->get('claimed_at');
            $expiresAt = $queue->get('expires_at');
            $hasClaim = $claimedAt !== null && $claimedAt !== '';
            $isExpired = $expiresAt !== null
                && $expiresAt !== ''
                && strtotime((string) $expiresAt) < time();
            if ($hasClaim && !$isExpired) {
                return 0;
            }

            $queue->set('claimed_at', date('Y-m-d H:i:s'));
            $queue->set('attempts', ((int) $queue->get('attempts')) + 1);
            $queue->set('expires_at', date('Y-m-d H:i:s', time() + 900));

            return 1;
        }

        return 0;
    }

    /**
     * @param array $values
     * @return int
     */
    private function releaseClaimRow(array $values)
    {
        $id = isset($values[0]) ? (int) $values[0] : 0;
        if ($id <= 0) {
            return 0;
        }

        foreach ($this->modx->queues as $queue) {
            if ((int) $queue->get('id') !== $id) {
                continue;
            }

            $queue->set('claimed_at', null);
            $queue->set('expires_at', null);

            return 1;
        }

        return 0;
    }
}
