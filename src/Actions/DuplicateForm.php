<?php

namespace Statamic\Actions;

use Illuminate\Support\Str;
use Statamic\Contracts\Forms\Form;
use Statamic\Facades\Form as FormAPI;
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

    public function visibleToBulk($items)
    {
        return $this->visibleTo($items->first());
    }

    public function run($items, $values)
    {
        collect($items)
            ->each(function ($item) use ($values) {
                /** @var \Statamic\Forms\Form $item */
                $itemBlueprintContents = $item->blueprint()->contents();

                $form = FormAPI::make()
                ->handle($values['handle'])
                ->title($values['title'])
                    ->honeypot($item->honeypot())
                    ->store($item->store())
                    ->email($item->email());

                $form->save();

                $form->blueprint()->setContents($itemBlueprintContents)->save();
            });
    }
}
