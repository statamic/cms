<?php

namespace Statamic\Http\Controllers\CP\Assets;

use Illuminate\Http\Request;
use Statamic\Contracts\Assets\AssetContainer as AssetContainerContract;
use Statamic\CP\PublishForm;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Blueprint;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Rules\Handle;

class AssetContainersController extends CpController
{
    public function show($container)
    {
        return redirect()->cpRoute('assets.browse.show', $container->handle());
    }

    public function edit($container)
    {
        $this->authorize('edit', $container, 'You are not authorized to edit asset containers.');

        $values = [
            'title' => $container->title(),
            'handle' => $container->handle(),
            'disk' => $container->diskHandle(),
            'source_preset' => $container->sourcePreset(),
            'warm_intelligent' => $intelligent = $container->warmsPresetsIntelligently(),
            'warm_presets' => $intelligent ? [] : $container->warmPresets(),
            'warm_presets_per_path' => $intelligent ? [] : $container->warmPresetsPerPath(),
            'validation' => $container->validationRules(),
        ];

        return PublishForm::make($this->formBlueprint($container))
            ->title(__('Configure Asset Container'))
            ->values($values)
            ->asConfig()
            ->submittingTo(cp_route('asset-containers.update', $container->handle()));
    }

    public function update(Request $request, $container)
    {
        $this->authorize('update', $container, 'You are not authorized to edit asset containers.');

        $fields = $this->formBlueprint($container)->fields()->addValues($request->all());

        $fields->validate();

        $values = $fields->process()->values();

        $container
            ->title($values['title'])
            ->disk($values['disk'])
            ->sourcePreset($values['source_preset'])
            ->warmPresets($values['warm_intelligent'] ? null : $values['warm_presets'])
            ->warmPresetsPerPath($values['warm_intelligent'] ? null : $values['warm_presets_per_path'])
            ->validationRules($values['validation'] ?? null);

        $container->save();

        session()->flash('success', __('Asset container updated'));

        return ['redirect' => $container->showUrl()];
    }

    public function create()
    {
        $this->authorize('create', AssetContainerContract::class, 'You are not authorized to create asset containers.');

        return PublishForm::make($this->formBlueprint())
            ->title(__('Create Asset Container'))
            ->values(['disk' => $this->disks()->first()])
            ->asConfig()
            ->submittingTo(cp_route('asset-containers.store'), 'POST');
    }

    public function store(Request $request)
    {
        $this->authorize('create', AssetContainerContract::class, 'You are not authorized to create asset containers.');

        $fields = $this->formBlueprint()->fields()->addValues($request->all());

        $fields->validate();

        $values = $fields->process()->values();

        if (AssetContainer::find($values['handle'])) {
            throw new \Exception('Asset container already exists');
        }

        $container = AssetContainer::make($values['handle'])
            ->title($values['title'])
            ->disk($values['disk'])
            ->sourcePreset($values['source_preset'])
            ->warmPresets($values['warm_intelligent'] ? null : $values['warm_presets'])
            ->warmPresetsPerPath($values['warm_intelligent'] ? null : $values['warm_presets_per_path']);

        $container->save();

        session()->flash('success', __('Asset container created'));

        return ['redirect' => $container->showUrl()];
    }

    public function destroy($container)
    {
        $this->authorize('delete', $container, 'You are not authorized to delete asset containers.');

        $container->delete();

        return [
            'message' => 'Container deleted',
            'redirect' => cp_route('assets.index'),
        ];
    }

    private function disks()
    {
        return collect(config('filesystems.disks'))->keys();
    }

    protected function formBlueprint($container = null)
    {
        $fields = [
            'name' => [
                'display' => __('Name'),
                'fields' => [
                    'title' => [
                        'type' => 'text',
                        'display' => __('Title'),
                        'instructions' => __('statamic::messages.asset_container_title_instructions'),
                        'validate' => 'required',
                    ],
                ],
            ],
        ];

        if (! $container) {
            $fields['name']['fields']['handle'] = [
                'type' => 'slug',
                'display' => __('Handle'),
                'validate' => ['required', new Handle],
                'separator' => '_',
                'instructions' => __('statamic::messages.asset_container_handle_instructions'),
            ];
        }

        $fields = array_merge($fields, [
            'filesystem' => [
                'display' => __('File Driver'),
                'fields' => [
                    'disk' => [
                        'type' => 'select',
                        'display' => __('Disk'),
                        'instructions' => __('statamic::messages.asset_container_disk_instructions'),
                        'options' => $this->disks()->all(),
                        'validate' => 'required',
                    ],
                ],
            ],
        ]);

        if ($container) {
            $fields['fields'] = [
                'display' => __('Fields'),
                'fields' => [
                    'blueprint' => [
                        'display' => __('Blueprint'),
                        'instructions' => __('statamic::messages.asset_container_blueprint_instructions'),
                        'type' => 'blueprints',
                        'options' => [
                            [
                                'handle' => 'default',
                                'title' => __('Edit Blueprint'),
                                'edit_url' => cp_route('blueprints.asset-containers.edit', $container->handle()),
                            ],
                        ],
                    ],
                ],
            ];
        }

        $fields = array_merge($fields, [
            'settings' => [
                'display' => __('Settings'),
                'fields' => [
                    'validation' => [
                        'type' => 'taggable',
                        'display' => __('Validation Rules'),
                        'instructions' => __('statamic::messages.asset_container_validation_rules_instructions'),
                    ],
                ],
            ],
        ]);

        $fields = array_merge($fields, [
            'image_manipulation' => [
                'display' => __('Image Manipulation'),
                'fields' => [
                    'source_preset' => [
                        'type' => 'select',
                        'display' => __('Process Source Images'),
                        'instructions' => __('statamic::messages.asset_container_source_preset_instructions'),
                        'label_html' => true,
                        'options' => $this->expandedGlidePresetOptions(),
                        'clearable' => true,
                    ],
                    'warm_intelligent' => [
                        'type' => 'toggle',
                        'display' => __('Intelligently Warm Presets'),
                        'instructions' => __('statamic::messages.asset_container_warm_intelligent_instructions'),
                        'default' => true,
                    ],
                    'warm_presets' => [
                        'type' => 'select',
                        'display' => __('Warm Specific Presets'),
                        'instructions' => __('statamic::messages.asset_container_warm_presets_instructions'),
                        'multiple' => true,
                        'label_html' => true,
                        'options' => $this->expandedGlidePresetOptions(),
                        'if' => [
                            'warm_intelligent' => false,
                        ],
                    ],
                    'warm_presets_per_path' => [
                        'type' => 'grid',
                        'display' => __('Warm Presets Per Path'),
                        // TODO: Add translation string
                        'instructions' => __('Enter a path and hit enter to add paths. Select the presets to warm for each set of path(s).'),
                        'mode' => 'stacked',
                        'reorderable' => true,
                        'fullscreen' => false,
                        'if' => [
                            'warm_intelligent' => false,
                        ],
                        'fields' => [
                            'paths' => [
                                'handle' => 'paths',
                                'field' => [
                                    'type' => 'taggable',
                                    'display' => __('Paths'),
                                    'placeholder' => __('e.g. images/uploads'),
                                    'validate' => 'required',
                                ],
                            ],
                            'presets' => [
                                'handle' => 'presets',
                                'field' => [
                                    'type' => 'select',
                                    'label_html' => true,
                                    'options' => $this->expandedGlidePresetOptions(),
                                    'multiple' => true,
                                    'validate' => 'required',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        return Blueprint::make()->setContents(collect([
            'tabs' => [
                'main' => [
                    'sections' => collect($fields)->map(function ($section) {
                        return [
                            'display' => $section['display'],
                            'fields' => collect($section['fields'])->map(function ($field, $handle) {
                                return [
                                    'handle' => $handle,
                                    'field' => $field,
                                ];
                            })->values()->all(),
                        ];
                    })->values()->all(),
                ],
            ],
        ])->all());
    }

    private function expandedGlidePresetOptions()
    {
        return collect(config('statamic.assets.image_manipulation.presets'))
            ->mapWithKeys(function ($params, $handle) {
                return [$handle => $this->expandedGlidePresetLabel($handle, $params)];
            })->all();
    }

    private function expandedGlidePresetLabel($handle, $params)
    {
        $separator = '<span class="hidden-outside text-gray-500">-</span>';

        $params = collect($params)
            ->map(function ($value, $param) {
                return sprintf('<code class="hidden-outside">%s: %s</code>', $param, $value);
            })
            ->implode(' ');

        return "{$handle} {$separator} {$params}";
    }
}
