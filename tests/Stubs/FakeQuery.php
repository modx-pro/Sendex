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
