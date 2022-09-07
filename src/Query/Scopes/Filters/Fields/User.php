<?php

namespace Statamic\Query\Scopes\Filters\Fields;

use Statamic\Facades\User as UserFacade;
use Statamic\Support\Arr;

class User extends FieldtypeFilter
{
    public function fieldItems()
    {
        return [
            'value' => [
                'type' => 'select',
                'placeholder' => __('Select User'),
                'options' => array_merge([
                    'me' => 'Me',
                ], UserFacade::all()
                    ->mapWithKeys(function ($user) {
                        return [$user->id() => $user->name()];
                    })
                    ->all()
                ),
            ],
        ];
    }

    public function apply($query, $handle, $values)
    {
        $value = $values['value'];

        if ($value === 'me') {
            $value = UserFacade::current()->id();
        }

        $query->where($handle, '=', $value);
    }

    public function badge($values)
    {
        $field = $this->fieldtype->field()->display();
        $operator = __('Is');
        $value = $values['value'];

        if ($value !== 'me') {
            $value = UserFacade::current()->name();
        }

        return $field.' '.strtolower($operator).' '.$value;
    }
}
