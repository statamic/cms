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
        return ['status' => 'complete'];
    }

    public function apply($query, $values)
    {
        match ($values['status']) {
            'incomplete' => $query->where('incomplete', true),
            'spam' => $query->where('spam', true),
            default => $query->where('incomplete', '!=', true)->where('spam', '!=', true),
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
            'complete' => __('Complete'),
            'incomplete' => __('Incomplete'),
            'spam' => __('Spam'),
        ]);
    }
}
