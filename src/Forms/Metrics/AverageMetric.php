<?php

namespace Statamic\Forms\Metrics;

class AverageMetric extends AbstractMetric
{
    public function result()
    {
        if (! $field = $this->get('field')) {
            throw new \Exception('Cannot get average metric without specifying a field.');
        }

        $value = $this->submissions()->avg(function ($submission) use ($field) {
            return $submission->get($field);
        });

        return number_format($value, $this->get('precision', 2));
    }
}
