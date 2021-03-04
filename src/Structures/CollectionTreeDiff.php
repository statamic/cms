<?php

namespace Statamic\Structures;

class CollectionTreeDiff
{
    protected $added = [];
    protected $removed = [];
    protected $moved = [];
    protected $ancestryChanged = [];
    protected $expectsRoot = false;
    private $positions;

    public function analyze($old, $new, $expectsRoot = false)
    {
        if ($old === $new) {
            return $this;
        }

        $this->expectsRoot = $expectsRoot;
        $old = $this->prepare($old);
        $new = $this->prepare($new);
        $this->positions = $this->preparePositions($old, $new);
        $this->added = $new->keys()->diff($old->keys())->values()->all();
        $this->removed = $old->keys()->diff($new->keys())->values()->all();
        $this->moved = $this->analyzeMoved();
        $this->ancestryChanged = $this->analyzeAncestryChanges();

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
        return collect($arr)->map(function ($item, $i) use ($path) {
            $item['path'] = "$path";

            if ($this->expectsRoot && $path == '*' && $i == 0) {
                $item['path'] = '^';
            }

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
     * An item that has changed postitions at the same depth but kept same ancestors will not be included here.
     */
    public function ancestryChanged()
    {
        return $this->ancestryChanged;
    }

    private function preparePositions($old, $new)
    {
        $positions = [];

        foreach ($old as $id => $item) {
            $positions[$id][] = $item;
        }

        foreach ($new as $id => $item) {
            $positions[$id][] = $item;
        }

        return collect($positions)->filter(function ($positions) {
            return count($positions) > 1;
        });
    }

    private function analyzeMoved()
    {
        return $this->positions->filter(function ($item) {
            [$a, $b] = $item;

            return $a['path'].'.'.$a['index'] !== $b['path'].'.'.$b['index'];
        })->keys()->all();
    }

    private function analyzeAncestryChanges()
    {
        return $this->positions->filter(function ($item) {
            return $item[0]['path'] !== $item[1]['path'];
        })->keys()->all();
    }
}
