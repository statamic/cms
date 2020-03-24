<?php

namespace Statamic\Query\Scopes\Filters;

use Illuminate\Support\Carbon;
use Statamic\Facades\Collection;
use Statamic\Query\Scopes\Filter;

class Status extends Filter
{
    public $pinned = true;

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

    public function apply($query, $values)
    {
        $query->where('status', $values['status']);
    }

    public function badge($values)
    {
        if ($values['status'] === 'published') {
            return __('is published');
        } elseif ($values['status'] === 'scheduled') {
            return __('is scheduled');
        } elseif ($values['status'] === 'draft') {
            return __('is draft');
        }
    }

    public function visibleTo($key)
    {
        return $key === 'entries';
    }

    protected function options()
    {
        $options = collect([
            'published' => __('Published'),
            'scheduled' => __('Scheduled'),
            'draft' => __('Draft'),
        ]);

        if (! $this->collection()->dated()) {
            $options->forget('scheduled');
        }

        return $options;
    }

    protected function collection()
    {
        return Collection::findByHandle($this->context['collection'] ?? null);
    }
}
