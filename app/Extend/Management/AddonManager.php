<?php

namespace Statamic\Extend\Management;

use Statamic\API\File;
use Statamic\API\Helper;

class AddonManager
{
    /**
     * @var ComposerManager
     */
    protected $composer;

    /**
     * @var array
     */
    protected $packages;

    /**
     * @var AddonRepository
     */
    private $addonRepository;

    /**
     * Create an AddonManager instance
     *
     * @param ComposerManager $composer
     */
    public function __construct(ComposerManager $composer, AddonRepository $addonRepository)
    {
        $this->composer = $composer;
        $this->addonRepository = $addonRepository;
    }

    /**
     * Get the ComposerManager instance
     *
     * @return ComposerManager
     */
    public function composer()
    {
        return $this->composer;
    }

    /**
     * Update dependencies for all addons, one addon, or multiple addons.
     *
     * @param string|array|null $packages
     * @return mixed
     */
    public function updateDependencies($packages = null)
    {
        $packages = (is_null($packages))
            ? $this->packages()
            : Helper::ensureArray($packages);

        $this->updateComposerJson($packages);

        if (! empty($packages)) {
            $this->composer->update($packages);
        }
    }

    /**
     * Add specified packages to the `require` array in composer.json
     *
     * @param array $packages
     */
    private function updateComposerJson($packages)
    {
        $contents = $this->composer->read();

        $original = $this->composer->readOriginal();

        $requires = array_get($original, 'require');

        foreach ($packages as $package) {
            $requires[$package] = '*@dev';
        }

        $contents['require'] = $requires;

        $this->composer->save($contents);
    }

    /**
     * Get all addons with composer.json files
     *
     * @return array  An array of package names
     */
    public function packages()
    {
        if ($this->packages) {
            return $this->packages;
        }

        $addons = $this->addonRepository->filename('composer.json')->files()->all();

        if (count($addons) === 0) {
            return [];
        }

        $packages = [];
        foreach ($addons as $path) {
            $json = json_decode(File::get($path), true);

            if ($name = array_get($json, 'name')) {
                $packages[] = $name;
            }
        }

        return $this->packages = $packages;
    }

    /**
     * Install an addon
     *
     * @param $addon
     * @return mixed
     */
    public function install($addon)
    {
        // TODO: Implement install() method.
    }

    /**
     * Uninstall an addon
     *
     * @param $addon
     * @return mixed
     */
    public function uninstall($addon)
    {
        // TODO: Implement uninstall() method.
    }
}
