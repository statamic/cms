<?php

namespace Statamic\Query;

class OrderBy
{
    public $sort;
    public $direction;

    /**
     * Instantiate order by object.
     *
     * @param string $sort
     * @param string $direction
     */
    public function __construct(string $sort, string $direction)
    {
        $this->sort = $sort;
        $this->direction = $direction;
    }

    /**
     * Instantiate order by object.
     *
     * @param string $orderBy
     * @return static
     */
    public static function parse(string $orderBy)
    {
        $sort = explode(':', $orderBy)[0];
        $direction = explode(':', $orderBy)[1] ?? 'asc';

        return new static($sort, $direction);
    }

    /**
     * Reverse order by direction.
     *
     * @return $this
     */
    public function reverse()
    {
        $this->direction = $this->direction === 'asc' ? 'desc' : 'asc';

        return $this;
    }

    /**
     * Convert order by to string, ie) 'title:desc'.
     *
     * @return string
     */
    public function toString()
    {
        return "{$this->sort}:{$this->direction}";
    }

    /**
     * Convert order by to string, ie) 'title:desc'.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
}
