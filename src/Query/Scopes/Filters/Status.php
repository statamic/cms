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
                'placeholder' => __('Status'),
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
            $status = __('published');
        } elseif ($values['status'] === 'scheduled') {
            $status = __('scheduled');
        } elseif ($values['status'] === 'draft') {
            $status = __('draft');
        }

        $title = optional($this->collection())->title();

        return collect([$status, strtolower($title)])
            ->filter()
            ->implode(' ');
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
