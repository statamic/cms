<?php

namespace Statamic\Http\Controllers;

use Statamic\API\URL;
use Statamic\API\Path;
use Statamic\API\YAML;
use Statamic\API\File;
use Statamic\API\Folder;
use Statamic\API\Helper;
use Statamic\API\Fieldset;
use Statamic\API\Str;
use Statamic\API\Cache;
use Statamic\API\Stache;
use Statamic\Extend\Addon;
use Illuminate\Http\Request;
use Statamic\Extend\Management\AddonRepository;

/**
 * Controller for the addon area
 */
class AddonsController extends CpController
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
        return view('addons.index', [
            'title' => 'Addons'
        ]);
    }

    public function get()
    {
        $addons = $this->addonRepo->thirdParty()->addons()->map(function ($addon) {
            return [
                'id'            => $addon->id(),
                'name'          => $addon->name(),
                'addon_url'     => $addon->url(),
                'version'       => $addon->version(),
                'developer'     => $addon->developer(),
                'developer_url' => $addon->developerUrl(),
                'description'   => $addon->description(),
                'settings_url'  => $addon->hasSettings() ? $addon->settingsUrl() : null,
                'installed'     => $addon->isInstalled()
            ];
        })->values();

        return [
            'columns' => ['name', 'version', 'developer', 'description', 'installed'],
            'items' => $addons,
            'pagination' => ['totalPages' => 1]
        ];
    }

    public function delete(Request $request)
    {
        $this->authorize('super');

        foreach (Helper::ensureArray($request->ids) as $id) {
            \Statamic\API\Addon::create($id)->delete();
        }

        return ['success' => true];
    }

    public function refresh()
    {
        \Artisan::call('update:addons');

        return back()->with('success', 'Addons refreshed.');
    }

    public function settings($addon)
    {
        $addon = new Addon(Str::studly($addon));

        if (! $addon->hasSettings()) {
            return redirect()->route('addons')->withErrors(['The requested addon does not have settings.']);
        }

        return view('addons.settings', [
            'title' => $addon->name() . ' ' . trans_choice('cp.settings', 2),
            'slug'  => $addon->slug(),
            'extra' => [
                'addon' => $addon->id()
            ],
            'content_data' => $this->getAddonData($addon),
            'content_type' => 'addon',
            'fieldset' => 'addon.'.$addon->slug().'.settings'
        ]);
    }

    private function getAddonData(Addon $addon)
    {
        $data = $addon->config();

        $fieldset = $addon->settingsFieldset();

        $data = $this->preProcessData($data, $fieldset);

        $data = $this->populateWithBlanks($fieldset, $data);

        return $data;
    }

    /**
     * Create the data array, populating it with blank values for all fields in
     * the fieldset, then overriding with the actual data where applicable.
     *
     * @param string $fieldset
     * @param array $data
     * @return array
     */
    private function populateWithBlanks($fieldset, $data)
    {
        // Get the fieldtypes
        $fieldtypes = collect($fieldset->fieldtypes())->keyBy(function($ft) {
            return $ft->getName();
        });

        // Build up the blanks
        $blanks = [];
        foreach ($fieldset->fields() as $name => $config) {
            $blanks[$name] = $fieldtypes->get($name)->blank();
        }

        return array_merge($blanks, $data);
    }

    private function preProcessData($data, $fieldset)
    {
        $fieldtypes = collect($fieldset->fieldtypes())->keyBy(function($fieldtype) {
            return $fieldtype->getFieldConfig('name');
        });

        foreach ($data as $field_name => $field_data) {
            if ($fieldtype = $fieldtypes->get($field_name)) {
                $data[$field_name] = $fieldtype->preProcess($field_data);
            }
        }

        return $data;
    }

    public function saveSettings(Request $request, $addon)
    {
        $addon = new Addon(Str::studly($addon));

        $data = $this->processFields($request->fields, $addon->settingsFieldset());

        $contents = YAML::dump($data);

        $file = settings_path('addons/' . $addon->handle() . '.yaml');
        File::put($file, $contents);

        Cache::clear();
        Stache::clear();

        $this->success('Settings updated');

        return ['success' => true, 'redirect' => route('addon.settings', $addon->slug())];
    }

    private function processFields($data, $fieldset)
    {
        foreach ($fieldset->fieldtypes() as $field) {
            if (! in_array($field->getName(), array_keys($data))) {
                continue;
            }

            $data[$field->getName()] = $field->process($data[$field->getName()]);
        }

        // Get rid of null fields
        $data = array_filter($data, function($value) {
            return !is_null($value);
        });

        return $data;
    }
}
