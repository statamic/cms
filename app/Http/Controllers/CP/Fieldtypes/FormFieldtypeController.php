<?php

namespace Statamic\Http\Controllers\CP\Fieldtypes;

use Statamic\API\Form;
use Statamic\Forms\Fieldtype;

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

    protected function fieldtype()
    {
        return new Fieldtype;
    }
}
