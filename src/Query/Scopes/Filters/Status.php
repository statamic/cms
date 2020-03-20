<?php

namespace Statamic\Query\Scopes\Filters;

use Illuminate\Support\Carbon;
use Statamic\Facades\Collection;
use Statamic\Query\Scopes\Filter;

class Status extends Filter
{
    public $pinned = true;

    public function fieldItems()
    {
        $options = collect([
            'published' => __('Published'),
            'scheduled' => __('Scheduled'),
            'draft' => __('Draft'),
        ]);

        if (! $this->collection()->dated()) {
            $options->forget('scheduled');
        }

        return [
            'status' => [
                'type' => 'radio',
                'options' => $options->all(),
            ],
        ];
    }

    public function apply($query, $values)
    {
        if ($values['status'] === 'published') {
            $query->where('published', true);
        } elseif ($values['status'] === 'scheduled') {
            $query->where('published', true)->where('date', '>', Carbon::now());
        } elseif ($values['status'] === 'draft') {
            $query->where('published', false);
        }
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

    protected function collection()
    {
        return Collection::findByHandle($this->context['collection'] ?? null);
    }
}
