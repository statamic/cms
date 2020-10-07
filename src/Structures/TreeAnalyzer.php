<?php

namespace Statamic\Structures;

use Statamic\Support\Arr;

class TreeAnalyzer
{
    protected $added = [];
    protected $removed = [];
    protected $moved = [];

    public function analyze($old, $new)
    {
        if ($old === $new) {
            return $this;
        }

        $old = Arr::dot($old);
        $new = Arr::dot($new);

        $this->removed = array_values(array_diff($old, $new));
        $this->added = array_values(array_diff($new, $old));
        $this->moved = $this->analyzeMoved($old, $new);

        return $this;
    }

    public function hasChanged()
    {
        return (bool) $this->affected();
    }

    public function affected()
    {
        return array_merge($this->removed, $this->added, $this->moved);
    }

    public function added()
    {
        return $this->added;
    }

    public function removed()
    {
        return $this->removed;
    }

    public function moved()
    {
        return $this->moved;
    }

    private function analyzeMoved($old, $new)
    {
        $positions = [];

        foreach ($old as $pos => $id) {
            $positions[$id][] = $pos;
        }

        foreach ($new as $pos => $id) {
            $positions[$id][] = $pos;
        }

        return collect($positions)->filter(function ($positions) {
            return count($positions) > 1
                && $positions[0] !== $positions[1];
        })->keys()->all();
    }
}
