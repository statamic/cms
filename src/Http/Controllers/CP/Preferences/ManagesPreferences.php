<?php

namespace Statamic\Http\Controllers\CP\Preferences;

use Illuminate\Http\Request;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Preference;
use Statamic\Facades\User;
use Statamic\Statamic;

trait ManagesPreferences
{
    protected function view(string $title, string $actionUrl, array $preferences)
    {
        $blueprint = $this->blueprint();

        $fields = $blueprint->fields()->addValues($preferences)->preProcess();

        return view('statamic::preferences.edit', [
            'title' => $title,
            'actionUrl' => $actionUrl,
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
            'showBreadcrumb' => Statamic::pro() && User::current()->can('manage preferences'),
        ]);
    }

    protected function updatePreferences(Request $request, $item)
    {
        $fields = $this->blueprint()->fields()->addValues($request->all())->process();

        $fields->all()->each(function ($field) use ($item) {
            if ($field->value() === $field->defaultValue()) {
                $item->removePreference($field->handle());
            } else {
                $item->setPreference($field->handle(), $field->value());
            }
        });

        $item->save();

        $this->success(__('Saved'));
    }

    private function blueprint()
    {
        return Blueprint::makeFromSections(Preference::sections());
    }
}
