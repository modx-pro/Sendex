<?php

class FakeQuery
{
    /** @var string */
    public $class;

    /** @var array */
    public $where = array();

    /** @var int|null */
    public $limit;

    /** @var array<int,array{0:string,1:string,2?:string}> */
    public $joins = array();

    /** @var array<int,string|array> */
    public $selects = array();

    /** @var string|null */
    public $groupby;

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

    /**
     * @param string $class
     * @param string $alias
     * @param string $on
     *
     * @return self
     */
    public function leftJoin($class, $alias, $on = '')
    {
        $this->joins[] = array($class, $alias, $on);

        return $this;
    }

    /**
     * @param mixed $columns
     *
     * @return self
     */
    public function select($columns)
    {
        $this->selects[] = $columns;

        return $this;
    }

    /**
     * @param string $expression
     *
     * @return self
     */
    public function groupby($expression)
    {
        $this->groupby = $expression;

        return $this;
    }
}
