<?php

namespace Statamic\Http\Controllers\CP\Preferences;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Preference;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Jobs\ReportThemeUsage;
use Statamic\Statamic;

trait ManagesPreferences
{
    protected function view(string $title, string $actionUrl, array $preferences)
    {
        $blueprint = $this->blueprint();

        $fields = $blueprint->fields()->addValues($preferences)->preProcess();

        return Inertia::render('preferences/Edit', [
            'title' => $title,
            'actionUrl' => $actionUrl,
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
            'saveAsOptions' => $this->getSaveAsOptions()->values()->all(),
        ]);
    }

    protected function updatePreferences(Request $request, $item)
    {
        $previousTheme = $item->getPreference('theme');

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

        $this->trackTheme($previousTheme, $item->getPreference('theme'));

        $this->success(__('Saved'));
    }

    private function blueprint()
    {
        return Blueprint::makeFromTabs(Preference::tabs());
    }

    private function getSaveAsOptions()
    {
        $canSaveAs = Statamic::pro() && User::current()->isSuper();

        $options = collect();

        if (! $canSaveAs) {
            return $options;
        }

        $options->put('default', [
            'label' => __('Default'),
            'url' => cp_route('preferences.default.update'),
            'icon' => 'light/earth',
        ]);

        Role::all()->each(function ($role) use (&$options) {
            $options->put($role->handle(), [
                'label' => $role->title(),
                'url' => cp_route('preferences.role.update', $role->handle()),
                'icon' => 'light/shield-key',
            ]);
        });

        $options->put('user', [
            'label' => __('My Preferences'),
            'url' => cp_route('preferences.user.update'),
            'icon' => 'light/user',
        ]);

        $options->forget($this->ignoreSaveAsOption());

        return $options;
    }

    private function trackTheme($oldTheme, $newTheme)
    {
        try {
            ReportThemeUsage::dispatch($oldTheme, $newTheme);
        } catch (\Throwable $e) {
            Log::error('Failed to report theme usage: '.$e->getMessage());

            return;
        }
    }
}
