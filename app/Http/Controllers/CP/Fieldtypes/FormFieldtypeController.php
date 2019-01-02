<?php

namespace Statamic\Http\Controllers\CP\Fieldtypes;

use Statamic\API\Form;

class FormFieldtypeController extends RelationshipFieldtypeController
{
    protected function getIndexItems()
    {
        return Form::all()->map(function ($form) {
            return [
                'id' => $form->handle(),
                'title' => $form->title(),
            ];
        })->values();
    }

    protected function toItemArray($id)
    {
        if ($form = Form::find($id)) {
            return [
                'title' => $form->title(),
                'id' => $form->handle(),
            ];
        }

        return $this->invalidItemArray($id);
    }
}
