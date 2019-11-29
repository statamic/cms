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
    public function index(Request $request)
    {
        $containers = AssetContainer::all()->filter(function ($container) {
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
                'delete_url' => $container->deleteUrl()
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
        $container = AssetContainer::find($container);

        $this->authorize('edit', $container, 'You are not authorized to edit asset containers.');

        $values = $container->toArray();

        $fields = ($blueprint = $this->formBlueprint())
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
        $container = AssetContainer::find($container);

        $this->authorize('update', $container, 'You are not authorized to edit asset containers.');

        $fields = $this->formBlueprint()->fields()->addValues($request->all());

        $fields->validate();

        $values = $fields->process()->values();

        $container
            ->title($values['title'])
            ->disk($values['disk'])
            ->blueprint($values['blueprint'])
            ->allowDownloading($values['allow_downloading'])
            ->allowRenaming($values['allow_renaming'])
            ->allowMoving($values['allow_moving'])
            ->allowUploads($values['allow_uploads'])
            ->createFolders($values['create_folders']);

        $container->save();

        return $container->toArray();
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
            ->blueprint($values['blueprint'])
            ->allowUploads($values['allow_uploads'])
            ->createFolders($values['create_folders']);

        $container->save();

        session()->flash('success', 'Asset container created');

        return ['redirect' => $container->showUrl()];
    }

    public function destroy($container)
    {
        $container = AssetContainer::find($container);

        // TODO: auth

        $container->delete();

        return [
            'message' => 'Container deleted',
            'redirect' => cp_route('asset-containers.index')
        ];
    }

    private function disks()
    {
        return collect(config('filesystems.disks'))->keys();
    }

    protected function formBlueprint()
    {
        return Blueprint::makeFromFields([
            'title' => [
                'type' => 'text',
                'display' => __('Title'),
                'validate' => 'required',
                'width' => 50,
            ],
            'handle' => [
                'type' => 'slug',
                'display' => __('Slug'),
                'validate' => 'required|alpha_dash',
                'width' => 50,
            ],
            'disk' => [
                'type' => 'select',
                'display' => __('Disk'),
                'instructions' => __('statamic::messages.asset_container_disk_instructions'),
                'options' => $this->disks()->all(),
                'width' => 50,
                'validate' => 'required',
            ],
            'blueprint' => [
                'type' => 'blueprints',
                'display' => __('Blueprints'),
                'instructions' => __('statamic::messages.asset_container_blueprint_instructions'),
                'max_items' => 1,
                'width' => 50,
            ],
            'allow_uploads' => [
                'type' => 'toggle',
                'display' => __('Allow Uploads'),
                'instructions' => __('statamic::messages.asset_container_blueprint_instructions'),
                'default' => true,
                'width' => 50,
            ],
            'create_folders' => [
                'type' => 'toggle',
                'display' => __('Create Folders'),
                'instructions' => __('statamic::messages.asset_container_create_folder_instructions'),
                'default' => true,
                'width' => 50,
            ],
            'allow_renaming' => [
                'type' => 'toggle',
                'display' => __('Allow Renaming'),
                'instructions' => __('statamic::messages.asset_container_rename_instructions'),
                'default' => true,
                'width' => 50,
            ],
            'allow_moving' => [
                'type' => 'toggle',
                'display' => __('Allow Moving'),
                'instructions' => __('statamic::messages.asset_container_move_instructions'),
                'default' => true,
                'width' => 50,
            ],
            'allow_downloading' => [
                'type' => 'toggle',
                'display' => __('Allow Downloading'),
                'instructions' => __('statamic::messages.asset_container_quick_download_instructions'),
                'default' => true,
                'width' => 50,
            ],
        ]);
    }
}
