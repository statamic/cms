<?php

namespace Statamic\Http\Controllers;

use Statamic\Extend\Management\AddonRepository;

class FieldtypesController extends CpController
{
    /**
     * @var AddonRepository
     */
    private $addonRepo;

    public function __construct(AddonRepository $addonRepo)
    {
        $this->addonRepo = $addonRepo;
    }

    public function index()
    {
        $fieldtypes = [];

        foreach ($this->getAllFieldtypes() as $fieldtype) {
            $config = [];

            foreach ($fieldtype->getConfigFieldset()->fieldtypes() as $item) {
                $c = $item->getFieldConfig();

                // Go through each fieldtype in *its* config fieldset and process the values. SO META.
                foreach ($item->getConfigFieldset()->fieldtypes() as $field) {
                    if (! in_array($field->getName(), array_keys($c))) {
                        continue;
                    }

                    $c[$field->getName()] = $field->preProcess($c[$field->getName()]);
                }

                $c['display'] = trans("fieldtypes/{$fieldtype->getHandle()}.{$c['name']}");
                $c['instructions'] = markdown(trans("fieldtypes/{$fieldtype->getHandle()}.{$c['name']}_instruct"));

                $config[] = $c;
            }

            $fieldtypes[] = [
                'label' => $fieldtype->getFieldtypeName(),
                'name' => $fieldtype->getHandle(),
                'canBeValidated' => $fieldtype->canBeValidated(),
                'canBeLocalized' => $fieldtype->canBeLocalized(),
                'canHaveDefault' => $fieldtype->canHaveDefault(),
                'config' => $config,
            ];
        }

        $hidden = ['replicator_sets', 'fields', 'asset_container', 'asset_folder', 'user_password',
            'locale_settings', 'redactor_settings', 'relate'];
        foreach ($fieldtypes as $key => $fieldtype) {
            if (in_array($fieldtype['name'], $hidden)) {
                unset($fieldtypes[$key]);
            }
        }

        return array_values($fieldtypes);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    private function getAllFieldtypes()
    {
        return $this->addonRepo->fieldtypes()->classes()->map(function ($class) {
            return app($class);
        })->sortBy(function ($fieldtype) {
            return $fieldtype->getAddonName();
        })->values();
    }
}
