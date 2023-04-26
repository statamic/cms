<?php

namespace Statamic\Http\Controllers\CP\Assets;

use Illuminate\Http\Request;
use Statamic\Contracts\Assets\AssetContainer as AssetContainerContract;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Blueprint;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;

class AssetContainersController extends CpController
{
    public function show($container)
    {
        return redirect()->cpRoute('assets.browse.show', $container->handle());
    }

    public function index(Request $request)
    {
        $containers = AssetContainer::all()->sortBy->title()->filter(function ($container) {
            return User::current()->can('view', $container);
        })->map(function ($container) {
            return [
                'id' => $container->handle(),
                'title' => $container->title(),
                'allow_downloading' => $container->allowDownloading(),
                'allow_moving' => $container->allowMoving(),
                'allow_renaming' => $container->allowRenaming(),
                'allow_uploads' => $container->allowUploads(),
                'create_folders' => $container->createFolders(),
                'edit_url' => $container->editUrl(),
                'delete_url' => $container->deleteUrl(),
                'blueprint_url' => cp_route('asset-containers.blueprint.edit', $container->handle()),
                'can_edit' => User::current()->can('edit', $container),
                'can_delete' => User::current()->can('delete', $container),
            ];
        })->values();

        if ($request->wantsJson()) {
            return $containers;
        }

        return view('statamic::assets.containers.index', [
            'containers' => $containers->all(),
            'columns' => ['title'],
            'visibleColumns' => ['title'],
        ]);
    }

    public function edit($container)
    {
        $this->authorize('edit', $container, 'You are not authorized to edit asset containers.');

        $values = [
            'title' => $container->title(),
            'handle' => $container->handle(),
            'disk' => $container->diskHandle(),
            'allow_uploads' => $container->allowUploads(),
            'allow_downloading' => $container->allowDownloading(),
            'allow_renaming' => $container->allowRenaming(),
            'allow_moving' => $container->allowMoving(),
            'create_folders' => $container->createFolders(),
            'source_preset' => $container->sourcePreset(),
            'warm_intelligent' => $intelligent = $container->warmsPresetsIntelligently(),
            'warm_presets' => $intelligent ? [] : $container->warmPresets(),
        ];

        $fields = ($blueprint = $this->formBlueprint($container))
            ->fields()
            ->addValues($values)
            ->preProcess();

        return view('statamic::assets.containers.edit', [
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
            'container' => $container,
        ]);
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
            ->allowDownloading($values['allow_downloading'])
            ->allowRenaming($values['allow_renaming'])
            ->allowMoving($values['allow_moving'])
            ->allowUploads($values['allow_uploads'])
            ->createFolders($values['create_folders'])
            ->sourcePreset($values['source_preset'])
            ->warmPresets($values['warm_intelligent'] ? null : $values['warm_presets']);

        $container->save();

        session()->flash('success', __('Asset container updated'));

        return ['redirect' => $container->showUrl()];
    }

    public function create()
    {
        $this->authorize('create', AssetContainerContract::class, 'You are not authorized to create asset containers.');

        $fields = ($blueprint = $this->formBlueprint())
            ->fields()
            ->preProcess();

        $values = $fields->values()->merge([
            'disk' => $this->disks()->first(),
        ]);

        return view('statamic::assets.containers.create', [
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $values,
            'meta' => $fields->meta(),
        ]);
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
            ->allowUploads($values['allow_uploads'])
            ->createFolders($values['create_folders'])
            ->sourcePreset($values['source_preset'])
            ->warmPresets($values['warm_intelligent'] ? null : $values['warm_presets']);

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
                'validate' => 'required|alpha_dash',
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
                        'type' => 'html',
                        'display' => __('Blueprint'),
                        'instructions' => __('statamic::messages.asset_container_blueprint_instructions'),
                        'html' => $container ? ''.
                            '<div class="text-xs">'.
                            '   <a href="'.cp_route('asset-containers.blueprint.edit', $container->handle()).'" class="text-blue">'.__('Edit').'</a>'.
                            '</div>' : '<div class="text-xs text-grey">'.__('Editable once created').'</div>',
                    ],
                ],
            ];
        }

        $fields = array_merge($fields, [
            'settings' => [
                'display' => __('Settings'),
                'fields' => [
                    'allow_uploads' => [
                        'type' => 'toggle',
                        'display' => __('Allow Uploads'),
                        'instructions' => __('statamic::messages.asset_container_allow_uploads_instructions'),
                        'default' => true,
                    ],
                    'create_folders' => [
                        'type' => 'toggle',
                        'display' => __('Create Folders'),
                        'instructions' => __('statamic::messages.asset_container_create_folder_instructions'),
                        'default' => true,
                    ],
                    'allow_renaming' => [
                        'type' => 'toggle',
                        'display' => __('Allow Renaming'),
                        'instructions' => __('statamic::messages.asset_container_rename_instructions'),
                        'default' => true,
                    ],
                    'allow_moving' => [
                        'type' => 'toggle',
                        'display' => __('Allow Moving'),
                        'instructions' => __('statamic::messages.asset_container_move_instructions'),
                        'default' => true,
                    ],
                    'allow_downloading' => [
                        'type' => 'toggle',
                        'display' => __('Allow Downloading'),
                        'instructions' => __('statamic::messages.asset_container_quick_download_instructions'),
                        'default' => true,
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
                ],
            ],
        ]);

        return Blueprint::makeFromSections($fields);
    }

    private function expandedGlidePresetOptions()
    {
        return collect(config('statamic.assets.image_manipulation.presets'))
            ->mapWithKeys(function ($params, $handle) {
                return [$handle => $this->expandedGlidePresetLabel($handle, $params)];
            });
    }

    private function expandedGlidePresetLabel($handle, $params)
    {
        $separator = '<span class="hidden-outside text-grey-50">-</span>';

        $params = collect($params)
            ->map(function ($value, $param) {
                return sprintf('<code class="hidden-outside">%s: %s</code>', $param, $value);
            })
            ->implode(' ');

        return "{$handle} {$separator} {$params}";
    }
}
