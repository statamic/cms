<?php

namespace Statamic\Query\Scopes\Filters;

use Statamic\Query\Scopes\Filter;

use function Statamic\trans as __;

class SubmissionStatus extends Filter
{
    protected $pinned = true;

    public static function title()
    {
        return __('Status');
    }

    public function fieldItems()
    {
        return [
            'status' => [
                'type' => 'radio',
                'options' => $this->options()->all(),
            ],
        ];
    }

    public function autoApply()
    {
        return ['status' => 'finalized'];
    }

    public function apply($query, $values)
    {
        match ($values['status']) {
            'partial' => $query->where('partial', true),
            default => $query->where('partial', '!=', true),
        };
    }

    public function badge($values)
    {
        return $this->options()->get($values['status']);
    }

    public function visibleTo($key)
    {
        return $key === 'form-submissions';
    }

    protected function options()
    {
        return collect([
            'finalized' => __('Finalized'),
            'partial' => __('Partial'),
        ]);
    }
}
