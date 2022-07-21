<?php

namespace Statamic\Extend;

use Composer\Package\Version\VersionParser;
use Facades\Statamic\Licensing\LicenseManager;
use ReflectionClass;
use Statamic\Facades\File;
use Statamic\Facades\Path;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Updater\AddonChangelog;

final class Addon
{
    /**
     * The identifier.
     * Typically a composer package name. eg. statamic/trapper-keeper.
     *
     * @var string
     */
    protected $id;

    /**
     * The marketplace product ID of the addon.
     *
     * @var int
     */
    protected $marketplaceId;

    /**
     * The marketplace slug of the addon.
     *
     * @var int
     */
    protected $marketplaceSlug;

    /**
     * The marketplace slug of the addon's seller.
     *
     * @var int
     */
    protected $marketplaceSellerSlug;

    /**
     * The addon's namespace. eg. "Statamic\Addons\TrapperKeeper".
     *
     * @var string
     */
    protected $namespace;

    /**
     * The directory the package is located within. eg. "/path/to/vendor/statamic/trapper-keeper".
     *
     * @var string
     */
    protected $directory;

    /**
     * The autoloaded directory, relative to the addon root. eg. "src" or "".
     *
     * @var string
     */
    protected $autoload;

    /**
     * The service provider class.
     *
     * @var string
     */
    protected $provider;

    /**
     * The name of the addon. eg. "Trapper Keeper".
     *
     * @var string
     */
    protected $name;

    /**
     * The addon slug, if overridden.
     *
     * @var string
     */
    protected $slug;

    /**
     * The addon description.
     *
     * @var string
     */
    protected $description;

    /**
     * The addon's version.
     *
     * @var string
     */
    protected $version;

    /**
     * The latest version of the addon (via marketplace).
     *
     * @var string
     */
    protected $latestVersion;

    /**
     * The marketing URL.
     *
     * @var string
     */
    protected $url;

    /**
     * The marketplace URL.
     *
     * @var string
     */
    protected $marketplaceUrl;

    /**
     * The name of the developer.
     *
     * @var string
     */
    protected $developer;

    /**
     * The developer's URL.
     *
     * @var string
     */
    protected $developerUrl;

    /**
     * Whether the addon is commercial.
     *
     * @var bool
     */
    protected $isCommercial = false;

    /**
     * Available editions.
     *
     * @var array|null
     */
    protected $editions = [];

    /**
     * @param  string  $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Make an addon instance.
     *
     * @return self
     */
    public static function make($name)
    {
        return new self($name);
    }

    /**
     * Make an addon instance from package details.
     *
     * @param  array  $package
     * @return self
     */
    public static function makeFromPackage(array $package)
    {
        $instance = self::make($package['id']);

        $keys = [
            'id', 'slug', 'editions', 'marketplaceId', 'marketplaceSlug', 'marketplaceUrl', 'marketplaceSellerSlug', 'name', 'namespace',
            'autoload', 'provider', 'description', 'package', 'version', 'latestVersion', 'url', 'developer', 'developerUrl', 'isCommercial',
        ];

        foreach (Arr::only($package, $keys) as $key => $value) {
            $method = Str::camel($key);
            $instance->$method($value);
        }

        return $instance;
    }

    /**
     * The ID (in a vendor/package format)
     * eg. statamic/trapper-keeper.
     *
     * @return string
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * The composer package string
     * eg. statamic/trapper-keeper.
     *
     * @return string
     */
    public function package()
    {
        return $this->id();
    }

    /**
     * The composer package name string
     * eg. in statamic/trapper-keeper, it's trapper-keeper.
     *
     * @return string
     */
    public function packageName()
    {
        return explode('/', $this->package())[1];
    }

    /**
     * The composer vendor name string
     * eg. in statamic/trapper-keeper, it's statamic.
     *
     * @return string
     */
    public function vendorName()
    {
        return explode('/', $this->package())[0];
    }

    /**
     * The marketplace variant ID of the addon.
     *
     * @param  int  $id
     * @return int
     */
    public function marketplaceId($id = null)
    {
        return $id
            ? $this->marketplaceId = $id
            : $this->marketplaceId;
    }

    /**
     * The marketplace slug of the addon.
     *
     * @param  string  $slug
     * @return string
     */
    public function marketplaceSlug($slug = null)
    {
        return $slug
            ? $this->marketplaceSlug = $slug
            : $this->marketplaceSlug;
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
        return str_replace('-', '_', explode('/', $this->id)[1]);
    }

    /**
     * The slug of the addon.
     *
     * For referencing in URLs.
     *
     * @param  string  $slug
     * @return string
     */
    public function slug($slug = null)
    {
        return $slug
            ? $this->slug = $slug
            : ($this->slug ?? Str::slug(explode('/', $this->id)[1]));
    }

    /**
     * The name of addon.
     *
     * @param  string  $name
     * @return string|self
     */
    public function name($name = null)
    {
        if (is_null($name)) {
            return $this->name ?? $this->id;
        }

        $this->name = $name;

        return $this;
    }

    /**
     * Whether a given file exists in the addon's directory.
     *
     * @param  string  $path
     * @return bool
     */
    public function hasFile($path)
    {
        if (! $this->directory()) {
            throw new \Exception('Cannot check files without a provider specified.');
        }

        return File::exists(Path::assemble($this->directory(), $path));
    }

    /**
     * Get the contents of a given file in the addon's directory.
     *
     * @param  string  $path
     * @return string
     */
    public function getFile($path)
    {
        if (! $this->directory()) {
            throw new \Exception('Cannot get files without a provider specified.');
        }

        return File::get(Path::assemble($this->directory(), $path));
    }

    /**
     * Write the given contents to a file.
     *
     * @param  string  $path
     * @param  string  $contents
     */
    public function putFile($path, $contents)
    {
        if (! $this->directory()) {
            throw new \Exception('Cannot write files without a provider specified.');
        }

        File::put(
            Path::assemble($this->directory(), $path),
            $contents
        );
    }

    /**
     * Get the configuration values.
     *
     * @return array
     */
    public function config()
    {
        return config($this->handle());
    }

    /**
     * Get addon changelog.
     *
     * @return AddonChangelog|null
     */
    public function changelog()
    {
        return new AddonChangelog($this);
    }

    public function isLatestVersion()
    {
        if (! $this->latestVersion) {
            return true;
        }

        $versionParser = new VersionParser;

        $version = $versionParser->normalize($this->version);
        $latestVersion = $versionParser->normalize($this->latestVersion);

        return version_compare($version, $latestVersion, '=');
    }

    public function license()
    {
        return LicenseManager::addons()->get($this->package());
    }

    /**
     * Handle method calls.
     * Typically will get or set property values.
     *
     * @param  string  $method
     * @param  array  $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (! property_exists($this, $method)) {
            throw new \Exception(sprintf('Call to undefined method %s::%s', get_class($this), $method));
        }

        if (empty($args)) {
            return $this->$method;
        }

        $this->$method = $args[0];

        return $this;
    }

    /**
     * The directory the package is located within. eg. "/path/to/vendor/statamic/trapper-keeper/".
     *
     * @return string
     */
    public function directory()
    {
        if (! $this->provider) {
            return null;
        }

        if ($this->directory) {
            return $this->directory;
        }

        $reflector = new ReflectionClass($this->provider);

        $dir = Str::removeRight(dirname($reflector->getFileName()), rtrim($this->autoload, '/'));

        return $this->directory = Path::tidy(Str::ensureRight($dir, '/'));
    }

    public function existsOnMarketplace()
    {
        return $this->marketplaceSlug() !== null;
    }

    public function edition()
    {
        $configured = config('statamic.editions.addons.'.$this->package());

        if ($configured && ! $this->editions()->contains($configured)) {
            throw new \Exception("Invalid edition [$configured] for addon ".$this->package());
        }

        return $configured ?? $this->editions()->first();
    }

    public function editions($editions = null)
    {
        if (func_num_args() === 0) {
            return collect($this->editions);
        }

        $this->editions = $editions;

        return $this;
    }
}
