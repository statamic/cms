<?php

namespace Statamic\Query;

class OrderBy
{
    public $sort;
    public $direction;

    /**
     * Instantiate order by object.
     *
     * @param  string  $sort
     * @param  string  $direction
     */
    public function __construct(string $sort, string $direction)
    {
        $this->sort = $sort;
        $this->direction = $direction;
    }

    /**
     * Instantiate order by object.
     *
     * @param  string  $orderBy
     * @return static
     */
    public static function parse(string $orderBy)
    {
        $parts = explode(':', $orderBy);
        $lastPart = last($parts);

        if (in_array($lastPart, ['asc', 'desc'])) {
            $direction = $lastPart;
            $sort = implode('->', array_slice($parts, 0, -1));
        } else {
            $direction = 'asc';
            $sort = str_replace(':', '->', $orderBy);
        }

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
