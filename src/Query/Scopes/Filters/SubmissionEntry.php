<?php

namespace Statamic\Query\Scopes\Filters;

use Statamic\Facades\Entry;
use Statamic\Facades\Form;
use Statamic\Query\Scopes\Filter;

use function Statamic\trans as __;

class SubmissionEntry extends Filter
{
    private $options;

    public static function title()
    {
        return __('Entry');
    }

    public function fieldItems()
    {
        return [
            'entry' => [
                'display' => __('Entry'),
                'type' => 'select',
                'options' => $this->options()->all(),
            ],
        ];
    }

    public function autoApply()
    {
        if ($entry = $this->context['entry'] ?? null) {
            return ['entry' => $entry];
        }

        return [];
    }

    public function apply($query, $values)
    {
        $query->where('_entry', $values['entry']);
    }

    public function badge($values)
    {
        $title = Entry::find($values['entry'])?->value('title');

        return __('Entry').': '.($title ?? $values['entry']);
    }

    public function visibleTo($key)
    {
        return $key === 'form-submissions' && $this->form()?->hasUniqueInstances();
    }

    private function form()
    {
        return Form::find($this->context['form'] ?? null);
    }

    private function options()
    {
        return $this->options ??= $this->queryOptions();
    }

    private function queryOptions()
    {
        if ($entry = $this->context['entry'] ?? null) {
            $ids = [$entry];
        } else {
            $ids = $this->form()
                ->querySubmissions()
                ->whereNotNull('_entry')
                ->pluck('_entry')
                ->unique()
                ->values()
                ->all();
        }

        return Entry::query()
            ->whereIn('id', $ids)
            ->get(['id', 'title'])
            ->mapWithKeys(fn ($entry) => [$entry->id() => $entry->value('title')]);
    }
}
