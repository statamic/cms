<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\Scope;
use Statamic\Facades\User;

class UserLocale extends Relationship
{
    protected $canEdit = false;
    protected $canCreate = false;
    protected $statusIcons = false;
    protected $icon = 'translate';

    protected function toItemArray($id)
    {
        if ($locale = User::locales()->get($id)) {
            return [
                'id' => $locale['locale'],
                'title' => "{$locale['name']} ({$locale['locale']})",
            ];
        }

        return $this->invalidItemArray($id);
    }

    public function preProcessIndex($data)
    {
        $locale = $this->field->parent()->preferredLocale();

        return parent::preProcessIndex($locale);
    }

    public function preProcess($data)
    {
        $locale = $this->field->parent()->preferredLocale();

        return parent::preProcess($locale);
    }

    public function process($data)
    {
        $this->field->parent()->setPreferredLocale($data[0] ?? config('app.locale'));

        return null;
    }

    public function getIndexItems($request)
    {
        return User::locales()->map(function ($locale) {
            return [
                'id' => $locale['locale'],
                'title' => "{$locale['name']} ({$locale['locale']})",
            ];
        })->values();
    }

    protected function augmentValue($value)
    {
        $locale = $this->field->parent()->preferredLocale();

        return User::locales()->get($locale);
    }

    public function getSelectionFilters()
    {
        return Scope::filters('user-locale-fieldtype', []);
    }
}
