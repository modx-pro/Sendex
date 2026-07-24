<?php

class FakeQuery
{
    /** @var string */
    public $class;

    /** @var array */
    public $where = array();

    /** @var int|null */
    public $limit;

    /**
     * @param string $class
     * @param array|null $criteria
     */
    public function __construct($class, $criteria = null)
    {
        $this->class = $class;
        if (is_array($criteria)) {
            $this->where = $criteria;
        }
    }

    /**
     * @param int $limit
     *
     * @return self
     */
    public function limit($limit)
    {
        $this->limit = (int) $limit;

        return $this;
    }

    /**
     * @param array $criteria
     *
     * @return self
     */
    public function where($criteria)
    {
        foreach ($criteria as $key => $value) {
            if (is_int($key)) {
                $this->where[] = $value;
                continue;
            }
            $this->where[$key] = $value;
        }

        return $this;
    }
}
