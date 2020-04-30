<?php

namespace Statamic\Actions;

use Illuminate\Contracts\Support\Arrayable;
use Statamic\Extend\HasFields;
use Statamic\Extend\HasHandle;
use Statamic\Extend\HasTitle;
use Statamic\Extend\RegistersItself;

abstract class Action implements Arrayable
{
    use HasHandle, HasTitle, HasFields, RegistersItself;

    protected static $binding = 'actions';

    protected $confirm = true;
    protected $dangerous = false;
    protected $fields = [];
    protected $context = [];

    public function visibleTo($item)
    {
        return true;
    }

    public function visibleToBulk($items)
    {
        $allowedOnItems = $items->filter(function ($item) {
            return $this->visibleTo($item);
        });

        return $items->count() === $allowedOnItems->count();
    }

    public function authorize($user, $item)
    {
        return true;
    }

    public function authorizeBulk($user, $items)
    {
        $authorizedOnItems = $items->filter(function ($item) use ($user) {
            return $this->authorize($user, $item);
        });

        return $items->count() === $authorizedOnItems->count();
    }

    public function context($context)
    {
        $this->context = $context;

        return $this;
    }

    protected function fieldItems()
    {
        return $this->fields;
    }

    public function run($items, $values)
    {
        //
    }

    public function redirect($items, $values)
    {
        return false;
    }

    public function download($items, $values)
    {
        return false;
    }

    public function buttonText()
    {
        /** @translation */
        return 'Run action|Run action on :count items';
    }

    public function confirmationText()
    {
        /** @translation */
        return 'Are you sure you want to run this action?|Are you sure you want to run this action on :count items?';
    }

    public function toArray()
    {
        return [
            'handle' => $this->handle(),
            'title' => $this->title(),
            'confirm' => $this->confirm,
            'buttonText' => $this->buttonText(),
            'confirmationText' => $this->confirmationText(),
            'dangerous' => $this->dangerous,
            'fields' => $this->fields()->toPublishArray(),
            'values' => $this->fields()->values(),
            'meta' => $this->fields()->meta(),
            'context' => $this->context,
        ];
    }
}
