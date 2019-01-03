<?php

namespace Statamic\Forms;

use Statamic\API;
use Statamic\Fields\Fieldtypes\Relationship;

class Fieldtype extends Relationship
{
    public function fieldsetContents()
    {
        return [];
    }

    protected function toItemArray($id)
    {
        if ($form = API\Form::find($id)) {
            return [
                'title' => $form->title(),
                'id' => $form->handle(),
            ];
        }

        return $this->invalidItemArray($id);
    }
}
