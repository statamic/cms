<?php

namespace Statamic\Http\Controllers\CP\Fields;

use Inertia\Inertia;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Form;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Nav;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;

class BlueprintController extends CpController
{
    use ManagesBlueprints;

    public function __construct()
    {
        $this->middleware(\Illuminate\Auth\Middleware\Authorize::class.':configure fields');
    }

    public function index()
    {
        $additional = Blueprint::getRenderableAdditionalNamespaces();

        return Inertia::render('blueprints/Index', [
            'collections' => $this->collections(),
            'taxonomies' => $this->taxonomies(),
            'navs' => $this->navs(),
            'assetContainers' => $this->assets(),
            'globals' => $this->globals(),
            'forms' => $this->forms(),
            'userBlueprint' => [
                'edit_url' => cp_route('blueprints.users.edit'),
            ],
            'groupBlueprint' => [
                'edit_url' => cp_route('blueprints.user-groups.edit'),
            ],
            'additional' => $additional,
        ]);
    }

    public function collections()
    {
        return Collection::all()->map(fn ($collection) => [
            'title' => $collection->title(),
            'handle' => $collection->handle(),
            'create_url' => cp_route('blueprints.collections.create', $collection),
            'blueprints' => $collection->entryBlueprints()->map(fn ($blueprint) => [
                'title' => $blueprint->title(),
                'handle' => $blueprint->handle(),
                'hidden' => $blueprint->hidden(),
                'edit_url' => cp_route('blueprints.collections.edit', [$collection, $blueprint]),
            ])->values()->all(),
        ])->values()->all();
    }

    public function taxonomies()
    {
        return Taxonomy::all()->map(fn ($taxonomy) => [
            'title' => $taxonomy->title(),
            'handle' => $taxonomy->handle(),
            'create_url' => cp_route('blueprints.taxonomies.create', $taxonomy),
            'blueprints' => $taxonomy->termBlueprints()->map(fn ($blueprint) => [
                'title' => $blueprint->title(),
                'handle' => $blueprint->handle(),
                'hidden' => $blueprint->hidden(),
                'edit_url' => cp_route('blueprints.taxonomies.edit', [$taxonomy, $blueprint]),
            ])->values()->all(),
        ])->values()->all();
    }

    public function navs()
    {
        return Nav::all()->map(fn ($nav) => [
            'title' => $nav->title(),
            'handle' => $nav->handle(),
            'edit_url' => cp_route('blueprints.navigation.edit', $nav->handle()),
        ])->values()->all();
    }

    public function assets()
    {
        return AssetContainer::all()->map(fn ($container) => [
            'title' => $container->title(),
            'handle' => $container->handle(),
            'edit_url' => cp_route('blueprints.asset-containers.edit', $container->handle()),
        ])->values()->all();
    }

    public function globals()
    {
        return GlobalSet::all()->map(fn ($set) => [
            'title' => $set->title(),
            'handle' => $set->handle(),
            'edit_url' => cp_route('blueprints.globals.edit', $set->handle()),
        ])->values()->all();
    }

    public function forms(): array
    {
        return User::current()->can('configure form fields')
            ? Form::all()->map(fn ($form) => [
                'title' => $form->title(),
                'handle' => $form->handle(),
                'edit_url' => cp_route('blueprints.forms.edit', $form->handle()),
            ])->values()->all()
            : [];
    }
}
