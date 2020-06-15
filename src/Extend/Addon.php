<?php

namespace Statamic\Extend;

use Statamic\Facades\File;
use Statamic\Facades\Path;
use Statamic\Facades\URL;
use Statamic\Facades\YAML;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Updater\Changelog;

final class Addon
{
    /**
     * The identifier.
     * Typically a composer package name. eg. statamic/bloodhound.
     *
     * @var string
     */
    protected $id;

    /**
     * The marketplace product ID of the addon.
     *
     * @var int
     */
    protected $marketplaceProductId;

    /**
     * The marketplace variant ID of the addon.
     *
     * @var int
     */
    protected $marketplaceVariantId;

    /**
     * The marketplace slug of the addon.
     *
     * @var int
     */
    protected $marketplaceSlug;

    /**
     * The addon's namespace. eg. "Statamic\Addons\Bloodhound".
     *
     * @var string
     */
    protected $namespace;

    /**
     * The directory the package is located within. eg. "/path/to/vendor/statamic/bloodhound".
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
     * The name of the addon. eg. "Bloodhound Search".
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
     * The marketing URL.
     *
     * @var string
     */
    protected $url;

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
     * @param string $id
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
     * @param array $package
     * @return self
     */
    public static function makeFromPackage(array $package)
    {
        $instance = self::make($package['id']);

        $keys = [
            'id', 'slug', 'marketplaceProductId', 'marketplaceVariantId', 'marketplaceSlug', 'name', 'namespace', 'directory',
            'autoload', 'description', 'package', 'version', 'url', 'developer', 'developerUrl', 'isCommercial',
        ];

        foreach (Arr::only($package, $keys) as $key => $value) {
            $method = Str::camel($key);
            $instance->$method($value);
        }

        return $instance;
    }

    /**
     * The ID (in a vendor/package format)
     * eg. statamic/bloodhound.
     *
     * @return string
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * The composer package string
     * eg. statamic/bloodhound.
     *
     * @return string
     */
    public function package()
    {
        return $this->id();
    }

    /**
     * The composer package name string
     * eg. in statamic/blodhound, it's bloodhound.
     *
     * @return string
     */
    public function packageName()
    {
        return explode('/', $this->package())[1];
    }

    /**
     * The composer vendor name string
     * eg. in statamic/blodhound, it's statamic.
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
     * @param int $id
     * @return int
     */
    public function marketplaceProductId($id = null)
    {
        return $id
            ? $this->marketplaceProductId = $id
            : $this->marketplaceProductId;
    }

    /**
     * The marketplace variant ID of the addon.
     *
     * @param int $id
     * @return int
     */
    public function marketplaceVariantId($id = null)
    {
        return $id
            ? $this->marketplaceVariantId = $id
            : $this->marketplaceVariantId;
    }

    /**
     * The marketplace slug of the addon.
     *
     * @param string $slug
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
     * @param string $slug
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
     * @param string $name
     * @return stirng|self
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
     * @param string $path
     * @return bool
     */
    public function hasFile($path)
    {
        if (! $this->directory()) {
            throw new \Exception('Cannot check files without a directory specified.');
        }

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
        if (! $this->directory()) {
            throw new \Exception('Cannot get files without a directory specified.');
        }

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
        if (! $this->directory()) {
            throw new \Exception('Cannot write files without a directory specified.');
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
     * Get the license key as provided by the user.
     *
     * @return string|null
     */
    public function licenseKey()
    {
        return array_get($this->config(), 'license_key');
    }

    /**
     * Get addon changelog.
     *
     * @return Changelog|null
     */
    public function changelog()
    {
        return Changelog::product($this->marketplaceSlug());
    }

    /**
     * Handle method calls.
     * Typically will get or set property values.
     *
     * @param string $method
     * @param array $args
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
}
