<?php

namespace Statamic\Http\Controllers\CP\Forms\Concerns;

use Statamic\Contracts\Forms\Form;
use Statamic\Fields\Field;

trait QueriesFormSubmissionSearch
{
    protected function applySubmissionSearch($query, Form $form, ?string $search)
    {
        if (! $search) {
            return $query;
        }

        $query->where(function ($query) use ($form, $search) {
            $query->where('date', 'like', '%'.$search.'%');

            $form->blueprint()->fields()->all()
                ->filter(function (Field $field): bool {
                    return in_array($field->type(), ['text', 'textarea', 'integer']);
                })
                ->each(function (Field $field) use ($query, $search): void {
                    $query->orWhere($field->handle(), 'like', '%'.$search.'%');
                });
        });

        return $query;
    }
}
