<?php

namespace Statamic\Forms\Metrics;

class TotalMetric extends AbstractMetric
{
    public function result()
    {
        return $this->submissions()->count();
    }
}
