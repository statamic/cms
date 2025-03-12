<?php

namespace Statamic\Forms;

use Statamic\Facades\Form;
use Statamic\Facades\Scope;
use Statamic\Facades\User;
use Statamic\Widgets\Widget as BaseWidget;

class Widget extends BaseWidget
{
    protected static $handle = 'form';

    public function html()
    {
        $form = Form::find($handle = $this->config('form'));

        if (! $form) {
            return "Error: Form [$handle] doesn't exist.";
        }

        if (! User::current()->can('view', $form)) {
            return;
        }

        [$sortColumn, $sortDirection] = $this->parseSort();

        $blueprint = $form->blueprint();
        $columns = $blueprint
            ->columns()
            ->only($this->config('fields', []))
            ->map(fn ($column) => $column->sortable(false)->visible(true))
            ->values();

        return view('statamic::forms.widget', [
            'form' => $form,
            'filters' => Scope::filters('form-submissions', [
                'form' => $form->handle(),
            ]),
            'title' => $this->config('title', $form->title()),
            'limit' => $this->config('limit', 5),
            'sortColumn' => $sortColumn,
            'sortDirection' => $sortDirection,
            'columns' => $columns,
        ]);
    }

    /**
     * Parse sort column and direction, similar to how sorting works on collection tag.
     *
     * @param  \Statamic\Entries\Collection  $collection
     * @return array
     */
    protected function parseSort()
    {
        $default = 'date:desc';
        $sort = $this->config('order_by') ?? $this->config('sort') ?? $default;
        $exploded = explode(':', $sort);
        $column = $exploded[0];
        $direction = $exploded[1] ?? 'asc';

        return [$column, $direction];
    }
}
