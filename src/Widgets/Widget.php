<?php

namespace Statamic\Widgets;

use Statamic\Extend\HasAliases;
use Statamic\Extend\HasHandle;
use Statamic\Extend\HasTitle;
use Statamic\Extend\RegistersItself;
use Statamic\Facades\Blueprint;
use Statamic\Support\Str;

abstract class Widget
{
    use HasAliases, HasHandle, HasTitle, RegistersItself {
        handle as protected traitHandle;
    }

    protected static $binding = 'widgets';

    protected $config;

    public function config($key = null, $default = null)
    {
        if (is_null($key)) {
            return collect($this->config);
        }

        return $this->config[$key] ?? $default;
    }

    public function setConfig($config)
    {
        $this->config = $config ?? [];
    }

    public static function handle()
    {
        return Str::removeRight(static::traitHandle(), '_widget');
    }

    public static function icon(): string
    {
        return 'code-block';
    }

    public function blueprint(): \Statamic\Fields\Blueprint
    {
        return Blueprint::make()->setContents([
            'tabs' => [
                'main' => [
                    'sections' => [
                        ['fields' => $this->commonBlueprintFields()],
                    ],
                ],
            ],
        ]);
    }

    protected function commonBlueprintFields(): array
    {
        return [
            [
                'handle' => 'width',
                'field' => [
                    'type' => 'select',
                    'display' => __('Width'),
                    'options' => [
                        'sm' => __('Small'),
                        'md' => __('Medium'),
                        'lg' => __('Large'),
                        'full' => __('Full'),
                    ],
                    'default' => 'md',
                    'clearable' => false,
                    'localizable' => false,
                ],
            ],
            [
                'handle' => 'classes',
                'field' => [
                    'type' => 'text',
                    'display' => __('Classes'),
                    'instructions' => __('statamic::messages.widget_classes_instructions'),
                ],
            ],
        ];
    }

    public function component()
    {
        //
    }

    public function html()
    {
        //
    }
}
