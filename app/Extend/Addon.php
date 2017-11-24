<?php

namespace Statamic\Extend;

use Statamic\API\URL;
use Statamic\API\Str;
use Statamic\API\Path;
use Statamic\API\File;
use Statamic\API\Folder;
use Statamic\API\Fieldset;
use Statamic\API\YAML;
use Statamic\Config\Addons;

final class Addon
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var bool
     */
    private $isFirstParty = false;

    /**
     * @var Meta
     */
    private $meta;

    /**
     * @param string $id
     */
    public function __construct($id)
    {
        $this->id = Str::studly($id);
        $this->meta = $this->makeMeta();
    }

    /**
     * The ID (or un-prefixed namespace)
     *
     * eg. Statamic\Addons\Bacon, would be "Bacon"
     *
     * @return string
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * The handle of the addon.
     *
     * For referencing in YAML, etc.
     *
     * @return string
     */
    public function handle()
    {
        return Str::snake($this->id);
    }

    /**
     * The slug of the addon.
     *
     * For referencing in URLs.
     *
     * @return string
     */
    public function slug()
    {
        return Str::studlyToSlug($this->id);
    }

    /**
     * The name of the addon.
     *
     * @return string
     */
    public function name()
    {
        return $this->meta()->get('name', $this->id);
    }

    public function isFirstParty($firstParty = null)
    {
        if (is_null($firstParty)) {
            return $this->isFirstParty;
        }

        $this->isFirstParty = $firstParty;

        return $this;
    }

    /**
     * The path to the directory.
     *
     * @return string
     */
    public function directory()
    {
        return $this->isFirstParty() ? bundles_path($this->id) : addons_path($this->id);
    }

    /**
     * Whether a given file exists in the addon's directory.
     *
     * @param string $path
     * @return bool
     */
    public function hasFile($path)
    {
        return File::exists(Path::assemble($this->directory(), $path));
    }

    /**
     * Get the contents of a given file in the addon's directory.
     *
     * @param string $path
     * @return string
     */
    public function getFile($path)
    {
        return File::get(Path::assemble($this->directory(), $path));
    }

    /**
     * Write the given contents to a file.
     *
     * @param string $path
     * @param string $contents
     */
    public function putFile($path, $contents)
    {
        File::put(
            Path::assemble($this->directory(), $path),
            $contents
        );
    }

    /**
     * Whether the addon has a settings fieldset.
     *
     * @return bool
     */
    public function hasSettings()
    {
        return $this->hasFile('settings.yaml') || $this->isCommercial();
    }

    /**
     * The URL to the settings page in the control panel.
     *
     * @return string
     */
    public function settingsUrl()
    {
        return route('addon.settings', $this->slug());
    }

    /**
     * The fieldset for the settings page in the control panel.
     *
     * @return \Statamic\CP\Fieldset
     */
    public function settingsFieldset()
    {
        if (! $this->hasSettings()) {
            return;
        }

        $fieldset = Fieldset::create($this->id . '.settings');

        $fieldset->type('addon');

        $contents = [
            'fields' => []
        ];

        if ($this->isCommercial()) {
            $contents['fields']['license_key'] = [
                'type' => 'text'
            ];
        }

        if ($this->hasFile('settings.yaml')) {
            $contents = array_merge_recursive($contents, YAML::parse($this->getFile('settings.yaml')));
        }

        $fieldset->contents($contents);

        return $fieldset;
    }

    /**
     * Get the configuration values.
     *
     * @return array
     */
    public function config()
    {
        return app(Addons::class)->get($this->handle()) ?: [];
    }

    /**
     * Get the addon's marketing URL.
     *
     * @return string
     */
    public function url()
    {
        return $this->meta()->get('url');
    }

    /**
     * Get the version.
     *
     * @return string
     */
    public function version()
    {
        return $this->meta()->get('version');
    }

    /**
     * Get the developer's name.
     *
     * @return string
     */
    public function developer()
    {
        return $this->meta()->get('developer');
    }

    /**
     * Get the developer's URL.
     *
     * @return string
     */
    public function developerUrl()
    {
        return $this->meta()->get('developer_url');
    }

    /**
     * Get the description.
     *
     * @return string
     */
    public function description()
    {
        return $this->meta()->get('description');
    }

    /**
     * Whether this is a commercial addon.
     *
     * @return bool
     */
    public function isCommercial()
    {
        return $this->meta()->get('commercial', false);
    }

    /**
     * Get the license key as provided by the user.
     *
     * @return string|null
     */
    public function licenseKey()
    {
        return array_get($this->config(), 'license_key');
    }

    /**
     * Makes a meta object associated with this addon.
     *
     * @param array $data
     * @return Meta
     */
    public function makeMeta($data = [])
    {
        return (new Meta($this))->data($data);
    }

    /**
     * Access the meta object, and make sure it's loaded.
     *
     * @return Meta
     */
    public function meta()
    {
        if (! $this->meta->isLoaded()) {
            $this->meta->load();
        }

        return $this->meta;
    }

    public function isInstalled()
    {
        if (! $this->hasFile('composer.json')) {
            return true;
        }

        // Get this addon's package name
        $composer = json_decode($this->getFile('composer.json'), true);
        $packageName = $composer['name'];

        // Get the packages from statamic's composer lock file
        $contents = File::get(statamic_path('composer.lock'));
        $json = json_decode($contents, true);
        $packages = $json['packages'];

        // Check if it's in there.
        foreach ($packages as $package) {
            if ($package['name'] === $packageName) {
                return true;
            }
        }

        return false;
    }

    public function delete()
    {
        Folder::delete($this->directory());
    }
}
