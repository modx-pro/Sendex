<?php

class FakeQuery
{
    /** @var string */
    public $class;

    /** @var array */
    public $where = array();

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
     * @param array $criteria
     *
     * @return self
     */
    public function where($criteria)
    {
        $this->where = array_merge($this->where, $criteria);

        return $this;
    }
}
