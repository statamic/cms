<?php

namespace Statamic\Http\Resources\CP\Submissions;

use Illuminate\Http\Resources\Json\JsonResource;
use Statamic\Facades\Action;
use Statamic\Facades\User;

class ListedSubmission extends JsonResource
{
    protected $blueprint;

    public function blueprint($blueprint)
    {
        $this->blueprint = $blueprint;

        return $this;
    }

    public function toArray($request)
    {
        $form = $this->resource->form();

        return array_merge($this->resource->toArray(), [
            'datestring' => $this->resource->date()->format($form->dateFormat()),
            'datestamp' => $this->resource->date()->timestamp,
            'url' => cp_route('forms.submissions.show', [$form->handle(), $this->resource->id()]),
            'deleteable' => User::current()->can('delete', $this->resource),
            'actions' => Action::for($this->resource),
        ]);
    }
}
