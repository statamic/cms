<?php

namespace Statamic\Query\Scopes\Filters;

use Statamic\Facades\Entry;
use Statamic\Facades\Form;
use Statamic\Query\Scopes\Filter;

use function Statamic\trans as __;

class SubmissionEntry extends Filter
{
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
        $query->where('entry', $values['entry']);
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
        $ids = $this->form()
            ->querySubmissions()
            ->whereNotNull('entry')
            ->pluck('entry')
            ->unique()
            ->values();

        return Entry::query()
            ->whereIn('id', $ids->all())
            ->get(['id', 'title'])
            ->mapWithKeys(fn ($entry) => [$entry->id() => $entry->value('title')]);
    }
}
