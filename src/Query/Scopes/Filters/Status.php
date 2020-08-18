<?php

namespace Statamic\Query\Scopes\Filters;

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
            return __('Published');
        } elseif ($values['status'] === 'scheduled') {
            return __('Scheduled');
        } elseif ($values['status'] === 'expired') {
            return __('Expired');
        } elseif ($values['status'] === 'draft') {
            return __('Draft');
        }
    }

    public function visibleTo($key)
    {
        return in_array($key, ['entries', 'entries-fieldtype']);
    }

    protected function options()
    {
        $options = collect([
            'published' => __('Published'),
            'scheduled' => __('Scheduled'),
            'expired' => __('Expired'),
            'draft' => __('Draft'),
        ]);

        if (! $collection = $this->collection()) {
            return $options;
        }

        if ($collection->dated() && $collection->futureDateBehavior() === 'private') {
            $options->forget('expired');
        } elseif ($collection->dated() && $collection->pastDateBehavior() === 'private') {
            $options->forget('scheduled');
        } else {
            $options->forget('scheduled');
            $options->forget('expired');
        }

        return $options;
    }

    protected function collection()
    {
        return Collection::findByHandle($this->context['collection'] ?? null);
    }
}
