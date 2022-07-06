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

    public function visibleTo($item)
    {
        return $item instanceof Form
            && Statamic::pro()
            && version_compare(Statamic::version(), '3.3.16', '>=');
    }

    public function visibleToBulk($items)
    {
        return $this->visibleTo($items->first());
    }

    public function run($items, $values)
    {
        collect($items)
            ->each(function ($item) {
                /** @var \Statamic\Forms\Form $item */
                if ($item instanceof Form) {
                    $itemBlueprintContents = $item->blueprint()->contents();

                    $itemTitleAndHandle = $this->generateTitleAndHandle($item);

                    $form = FormAPI::make()
                        ->handle($itemTitleAndHandle['handle'])
                        ->title($itemTitleAndHandle['title'])
                        ->honeypot($item->honeypot())
                        ->store($item->store())
                        ->email($item->email());

                    $form->save();

                    $form->blueprint()->setContents($itemBlueprintContents)->save();
                }
            });
    }

    /**
     * This method has been copied from the Duplicate Entry code in Statamic v2.
     * It's been updated to also deal with entry titles.
     */
    protected function generateTitleAndHandle(Form $form, $attempt = 1)
    {
        $title = $form->title();
        $handle = $form->handle();

        if ($attempt == 1) {
            $title = $title . __(' (Duplicated)');
        }

        if ($attempt !== 1) {
            if (! Str::contains($title, __(' (Duplicated)'))) {
                $title .= __(' (Duplicated)');
            }

            $title .= ' (' . $attempt . ')';
        }

        $handle .= '-' . $attempt;

        // If the slug we've just built already exists, we'll try again, recursively.
        if (FormAPI::find($handle)) {
            $generate = $this->generateTitleAndHandle($form, $attempt + 1);

            $title = $generate['title'];
            $handle = $generate['handle'];
        }

        return [
            'title' => $title,
            'handle' => $handle,
        ];
    }
}
