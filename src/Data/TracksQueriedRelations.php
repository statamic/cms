<?php

namespace Statamic\Data;

trait TracksQueriedRelations
{
    protected $selectedQueryRelations = [];

    public function selectedQueryRelations($relations)
    {
        $this->selectedQueryRelations = $relations;

        return $this;
    }
}
