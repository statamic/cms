<?php

namespace Statamic\Structures;

class TreeAnalyzer
{
    protected $added = [];
    protected $removed = [];
    protected $moved = [];
    protected $relocated = [];

    public function analyze($old, $new)
    {
        if ($old === $new) {
            return $this;
        }

        $old = $this->prepare($old);
        $new = $this->prepare($new);

        $this->added = $new->keys()->diff($old->keys())->values()->all();
        $this->removed = $old->keys()->diff($new->keys())->values()->all();
        $this->moved = $this->analyzeMoved($old, $new);
        $this->relocated = $this->analyzeRelocated($old, $new);

        return $this;
    }

    private function prepare($arr)
    {
        return collect($this->flatten($this->addPaths($arr)));
    }

    private function flatten($arr)
    {
        return collect($arr)->mapWithKeys(function ($item, $i) {
            $results = [$item['entry'] => [
                'path' => $item['path'],
                'index' => $i,
            ]];

            if (isset($item['children'])) {
                $results = $results + $this->flatten($item['children']);
            }

            return $results;
        })->all();
    }

    private function addPaths($arr, $path = '*')
    {
        return collect($arr)->map(function ($item) use ($path) {
            $item['path'] = "$path";

            if (isset($item['children'])) {
                $item['children'] = $this->addPaths($item['children'], $path.'.'.$item['entry']);
            }

            return $item;
        })->all();
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

    /**
     * Items that have changed positions and their "order" would be affected.
     * An item will not be considered moved if their ancestor moved.
     */
    public function moved()
    {
        return $this->moved;
    }

    /**
     * Items that have changed positions and their "uri" would be affected.
     * An item that has changed postitions and kept the same ancestors will not be considered relocated.
     */
    public function relocated()
    {
        return $this->relocated;
    }

    private function analyzeMoved($old, $new)
    {
        $positions = [];

        foreach ($old as $id => $item) {
            $positions[$id][] = $item['path'].'.'.$item['index'];
        }

        foreach ($new as $id => $item) {
            $positions[$id][] = $item['path'].'.'.$item['index'];
        }

        return collect($positions)->filter(function ($positions) {
            return count($positions) > 1
                && $positions[0] !== $positions[1];
        })->keys()->all();
    }

    private function analyzeRelocated($old, $new)
    {
        $positions = [];

        foreach ($old as $id => $item) {
            $positions[$id][] = $item['path'];
        }

        foreach ($new as $id => $item) {
            $positions[$id][] = $item['path'];
        }

        return collect($positions)->filter(function ($positions) {
            return count($positions) > 1
                && $positions[0] !== $positions[1];
        })->keys()->all();
    }
}
