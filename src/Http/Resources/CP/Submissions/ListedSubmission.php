<?php

namespace Statamic\Http\Resources\CP\Submissions;

use Illuminate\Http\Resources\Json\JsonResource;
use Statamic\Facades\Action;
use Statamic\Facades\User;

class ListedSubmission extends JsonResource
{
    protected $blueprint;
    protected $columns;

    public function blueprint($blueprint)
    {
        $this->blueprint = $blueprint;

        return $this;
    }

    public function columns($columns)
    {
        $this->columns = $columns;

        return $this;
    }

    public function toArray($request)
    {
        $form = $this->resource->form();

        return [
            'id' => $this->resource->id(),
            $this->merge($this->values([
                'datestamp' => $this->resource->date()->format($form->dateFormat()),
            ])),
            'url' => cp_route('forms.submissions.show', [$form->handle(), $this->resource->id()]),
            'deleteable' => User::current()->can('delete', $this->resource),
            'actions' => Action::for($this->resource),
        ];
    }

    protected function values($extra = [])
    {
        return $this->columns->mapWithKeys(function ($column) use ($extra) {
            $key = $column->field;
            $value = $extra[$key] ?? $this->resource->get($key);

            if (! $field = $this->blueprint->field($key)) {
                return [$key => $value];
            }

            $value = $field
                ->setValue($value)
                ->setParent($this->resource)
                ->preProcessIndex()
                ->value();

            return [$key => $value];
        });
    }
}
