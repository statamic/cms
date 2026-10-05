<?php

namespace Statamic\Widgets;

use Statamic\Facades\Blueprint;

use function Statamic\trans as __;

class Template extends Widget
{
    public static function icon(): string
    {
        return 'template-theme-design-layout';
    }

    public function blueprint(): \Statamic\Fields\Blueprint
    {
        return Blueprint::make()->setContents([
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'fields' => [
                                [
                                    'handle' => 'template',
                                    'field' => [
                                        'type' => 'text',
                                        'display' => __('Template'),
                                        'instructions' => __('statamic::messages.widget_template_template_instructions'),
                                        'validate' => 'required',
                                    ],
                                ],
                                ...$this->commonBlueprintFields(),
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * The HTML that should be shown in the widget.
     *
     * @return \Illuminate\View\View
     */
    public function html()
    {
        $data = $this->config()->except('type', 'template')->all();

        return view($this->config('template'), $data);
    }
}
