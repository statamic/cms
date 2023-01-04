<?php

namespace Statamic\Http\Controllers\CP\Preferences;

use Illuminate\Http\Request;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Preference;
use Statamic\Facades\Role;
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
            'saveAsOptions' => $this->getSaveAsOptions()->values()->all(),
        ]);
    }

    protected function updatePreferences(Request $request, $item)
    {
        $fields = $this->blueprint()->fields()->addValues($request->all())->process();

        $fields->validate();

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

    private function getSaveAsOptions()
    {
        $canSaveAs = Statamic::pro() && User::current()->isSuper();

        $options = collect();

        if (! $canSaveAs) {
            return $options;
        }

        $options->put('default', [
            'label' => 'Save as Global Default Preferences',
            'url' => cp_route('preferences.default.update'),
        ]);

        Role::all()->each(function ($role) use (&$options) {
            $options->put($role->handle(), [
                'label' => 'Save as '.$role->title().' Role Preferences',
                'url' => cp_route('preferences.role.update', $role->handle()),
            ]);
        });

        $options->put('user', [
            'label' => 'Save as My Preferences',
            'url' => cp_route('preferences.user.update'),
        ]);

        $options->forget($this->ignoreSaveAsOption());

        return $options;
    }
}
