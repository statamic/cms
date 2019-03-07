<?php

namespace Statamic\Fields\Fieldtypes;

use Statamic\API\Blueprint;

class Blueprints extends Relationship
{
    protected function toItemArray($id, $site = null)
    {
        if ($blueprint = Blueprint::find($id)) {
            return [
                'title' => $blueprint->title(),
                'id' => $blueprint->handle(),
            ];
        }

        return $this->invalidItemArray($id);
    }
}
