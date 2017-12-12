<?php

namespace Statamic\Extend;

use Statamic\API\Arr;
use Statamic\API\URL;
use Statamic\API\Str;
use Statamic\API\Path;
use Statamic\API\File;
use Statamic\API\YAML;

final class Addon
{
    /**
     * The identifier.
     * Typically the class name without the namespace. eg. "Bloodhound"
     *
     * @var string
     */
    protected $id;

    /**
     * The addon's namespace. eg. "Statamic\Addons\Bloodhound"
     *
     * @var string
     */
    protected $namespace;

    /**
     * The directory the package is located within. eg. "/path/to/vendor/statamic/bloodhound"
     *
     * @var string
     */
    protected $directory;

    /**
     * The autoloaded directory, relative to the addon root. eg. "src" or ""
     *
     * @var string
     */
    protected $autoload;

    /**
     * The name of the addon. eg. "Bloodhound Search"
     *
     * @var string
     */
    protected $name;

    /**
     * The addon description.
     *
     * @var string
     */
    protected $description;

    /**
     * The Composer package name. eg. "statamic/bloodhound"
     *
     * @var string
     */
    protected $package;

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
     * The developer's URL
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
        $this->id = Str::studly($id);
    }

    /**
     * Create an addon instance.
     *
     * @return self
     */
    public static function create($name)
    {
        return new self($name);
    }

    /**
     * Create an addon instance from package details.
     *
     * @param array $package
     * @return self
     */
    public static function createFromPackage(array $package)
    {
        $instance = self::create($package['id']);

        $keys = [
            'id', 'name', 'namespace', 'directory', 'autoload', 'description',
            'package', 'version', 'url', 'developer', 'developerUrl', 'isCommercial',
        ];

        foreach (Arr::only($package, $keys) as $key => $value) {
            $method = Str::camel($key);
            $instance->$method($value);
        }

        return $instance;
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

    public function toComposerJson()
    {
        return json_encode($this->toComposerJsonArray(), JSON_PRETTY_PRINT);
    }

    public function toComposerJsonArray()
    {
        return [
            'name' => $this->package,
            'description' => $this->description,
            'version' => $this->version,
            'type' => 'statamic-addon',
            'autoload' => [
                'psr-4' => [
                    $this->namespace.'\\' => $this->autoload,
                ]
            ],
            'extra' => [
                'statamic' => [
                    'name' => $this->name,
                    'description' => $this->description,
                    'developer' => $this->developer,
                    'developer-url' => $this->developerUrl,
                ],
                'laravel' => [
                    'providers' => [
                        $this->namespace.'\\'.$this->id.'ServiceProvider'
                    ]
                ],
            ]
        ];
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
