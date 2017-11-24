<?php

namespace Statamic\Addons\Suggest\Modes;

use Statamic\API\Form;

class FormMode extends AbstractMode
{
    public function suggestions()
    {
        $suggestions = [];

        foreach (Form::getAllFormsets() as $form) {
            $suggestions[] = [
                'value' => $form['name'],
                'text'  => $form['title']
            ];
        }

        return $suggestions;
    }
}
