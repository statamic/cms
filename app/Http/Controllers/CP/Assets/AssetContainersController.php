<?php

namespace Statamic\Http\Controllers\CP\Assets;

use Statamic\API\Blueprint;
use Illuminate\Http\Request;
use Statamic\Fields\Validation;
use Statamic\API\AssetContainer;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Contracts\Assets\AssetContainer as AssetContainerContract;

class AssetContainersController extends CpController
{
    public function index(Request $request)
    {
        $containers = AssetContainer::all()->filter(function ($container) {
            return true; // TODO: auth.
            // return request()->user()->can('view', $container);
        })->map(function ($container) {
            return [
                'id' => $container->handle(),
                'title' => $container->title(),
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
            'containers' => $containers,
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

        $validation = (new Validation)->fields(
            $fields = $this->formBlueprint()->fields()->addValues($request->all())->process()
        );

        $request->validate($validation->rules());

        $values = $fields->values();

        $container
            ->title($values['title'])
            ->disk($values['disk'])
            ->blueprint($values['blueprint'])
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

        $values = array_merge($fields->values(), [
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

        $validation = (new Validation)->fields(
            $fields = $this->formBlueprint()->fields()->addValues($request->all())->process()
        );

        $request->validate($validation->rules());

        $values = $fields->values();

        $container = AssetContainer::make($values['handle'])
            ->title($values['title'])
            ->disk($values['disk'])
            ->blueprint($values['blueprint'])
            ->allowUploads($values['allow_uploads'])
            ->createFolders($values['create_folders']);

        $container->save();

        session()->flash('success', 'Container saved');

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
                'validate' => 'required',
                'width' => 50,
            ],
            'handle' => [
                'type' => 'slug',
                'validate' => 'required|alpha_dash',
                'width' => 50,
            ],
            'disk' => [
                'type' => 'select',
                'display' => __('Disk'),
                'instructions' => __('The filesystem disk this container will use.'),
                'options' => collect(config('filesystems.disks'))->keys()->all(),
            ],
            'blueprint' => [
                'type' => 'blueprints',
                'instructions' => __('The blueprint that assets in this container will use.'),
                'max_items' => 1,
            ],
            'allow_uploads' => [
                'type' => 'toggle',
                'instructions' => __('The ability to upload into this container.'),
                'default' => true,
                'width' => 50,
            ],
            'create_folders' => [
                'type' => 'toggle',
                'instructions' => __('The ability to create folders within this container.'),
                'default' => true,
                'width' => 50,
            ],
        ]);
    }
}
