<?php

namespace Statamic\Actions;

use Statamic\Contracts\Forms\Form;
use Statamic\Facades\Form as Forms;
use Statamic\Statamic;

class DuplicateForm extends Action
{
    public static function title()
    {
        return __('Duplicate');
    }

    public function confirmationText()
    {
        return null;
    }

    protected function fieldItems()
    {
        return [
            'title' => [
                'type' => 'text',
                'instructions' => __('statamic::messages.form_configure_title_instructions'),
                'validate' => 'required',
            ],
            'handle' => [
                'type' => 'slug',
                'instructions' => __('statamic::messages.form_configure_handle_instructions'),
                'separator' => '_',
                'validate' => 'required|alpha_dash|unique_form_handle',
            ],
        ];
    }

    public function visibleTo($item)
    {
        return $item instanceof Form && Statamic::pro();
    }

    public function run($items, $values)
    {
        $items->each(function (Form $original) use ($values) {
            $originalBlueprintContents = $original->blueprint()->contents();

            $form = Forms::make()
                ->handle($values['handle'])
                ->title($values['title'])
                ->honeypot($original->honeypot())
                ->store($original->store())
                ->email($original->email());

            $form->save();

            $form->blueprint()->setContents($originalBlueprintContents)->save();
        });
    }

    public function authorize($user, $item)
    {
        return $user->can('create', Form::class);
    }
}
