<?php

namespace Statamic\Actions;

use Illuminate\Support\Collection;
use Statamic\Contracts\Assets\Asset;
use Statamic\Facades;
use Statamic\Facades\Blink;
use Statamic\Fields\Blueprint;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Fieldtypes\Replicator;

use function Statamic\trans as __;

class ReplaceAsset extends Action
{
    protected $icon = 'replace';

    public static function title()
    {
        return __('Replace');
    }

    public function visibleTo($item)
    {
        return $item instanceof Asset;
    }

    public function visibleToBulk($items)
    {
        return false;
    }

    public function authorize($user, $asset)
    {
        return $user->can('replace', $asset);
    }

    public function buttonText()
    {
        /** @translation */
        return 'Replace Asset';
    }

    public function confirmationText()
    {
        /** @translation */
        return 'statamic::messages.asset_replace_confirmation';
    }

    public function run($assets, $values)
    {
        $originalAsset = $assets->first();

        $newAsset = Facades\Asset::find($this->context['container'].'::'.$values['new_asset']);

        $newAsset->replace($originalAsset, $values['delete_original']);

        return [
            'ids' => [$newAsset->id()],
        ];
    }

    protected function fieldItems()
    {
        return [
            'new_asset' => [
                'display' => __('New Asset'),
                'type' => 'assets',
                'container' => $this->context['container'],
                'folder' => $this->context['folder'] ?? null,
                'max_files' => 1,
                'validate' => 'required',
                'mode' => 'list',
                'restrict' => $this->containerRestrictsFolders(),
                'allow_uploads' => true,
                'show_filename' => true,
            ],
            'delete_original' => [
                'display' => __('Delete Original Asset'),
                'type' => 'toggle',
                'default' => true,
            ],
        ];
    }

    private function blueprints(): Collection
    {
        return collect([
            ...Facades\Collection::all()->flatMap->entryBlueprints()->values(),
            ...Facades\Taxonomy::all()->flatMap->termBlueprints()->values(),
            ...Facades\GlobalSet::all()->map->blueprint()->values(),
            ...Facades\AssetContainer::all()->map->blueprint()->values(),
            ...Facades\Nav::all()->map->blueprint()->values(),
            ...Facades\Form::all()->map->blueprint()->values(),
            Facades\User::blueprint(),
            ...Facades\Blueprint::in('')->values(),
        ])->filter();
    }

    // Replacing an asset updates every reference to it, so if any field keeps its
    // assets within a folder, picking one from elsewhere would break that field.
    private function containerRestrictsFolders(): bool
    {
        return Blink::once(
            'replace-asset-restricts-folders.'.$this->context['container'],
            fn () => $this->blueprints()->contains(fn (Blueprint $blueprint) => $this->fieldsRestrictFolders($blueprint->fields()))
        );
    }

    private function fieldRestrictsFolders(Field $field): bool
    {
        if ($field->type() !== 'assets' || ! ($field->get('restrict') || $field->get('dynamic'))) {
            return false;
        }

        if (is_null($container = $field->get('container'))) {
            $containers = Facades\AssetContainer::all();
            $container = $containers->count() === 1 ? $containers->first()->handle() : null;
        }

        return $container === $this->context['container'];
    }

    private function fieldsRestrictFolders(Fields $fields): bool
    {
        return $fields->all()->contains(
            fn (Field $field) => $this->fieldRestrictsFolders($field) || $this->fieldsRestrictFolders($this->nestedFields($field))
        );
    }

    private function nestedFields(Field $field): Fields
    {
        $fieldtype = $field->fieldtype();

        return new Fields($fieldtype instanceof Replicator
            ? $fieldtype->flattenedSetsConfig()->flatMap(fn ($set) => $set['fields'] ?? [])->all()
            : $field->get('fields', []));
    }
}
