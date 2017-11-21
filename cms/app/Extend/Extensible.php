<?php

namespace Statamic\Extend;

use Statamic\API\URL;
use Statamic\API\File;
use Statamic\API\Path;
use Statamic\API\YAML;
use Statamic\API\Email;
use Statamic\API\Str;
use ReflectionException;
use Statamic\Config\Addons;
use Statamic\Extend\Contextual\Store;
use Statamic\Extend\Contextual\ContextualJs;
use Statamic\Extend\Contextual\ContextualCss;
use Statamic\Exceptions\ApiNotFoundException;
use Statamic\Extend\Contextual\ContextualBlink;
use Statamic\Extend\Contextual\ContextualCache;
use Statamic\Extend\Contextual\ContextualFlash;
use Statamic\Extend\Contextual\ContextualImage;
use Statamic\Extend\Contextual\ContextualCookie;
use Statamic\Extend\Contextual\ContextualStorage;
use Statamic\Extend\Contextual\ContextualSession;
use Statamic\Extend\Contextual\ContextualResource;

trait Extensible
{
    /**
     * Magic method for properties so we can keep ->blink etc working as a property
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        switch ($key) {
            case 'addon_name':
                return $this->getAddonClassName();
            case 'addon_type':
                return $this->getAddonType();
            case 'blink':
                return $this->getContextualStore('blink', new ContextualBlink($this));
            case 'cache':
                return $this->getContextualStore('cache', new ContextualCache($this));
            case 'session':
                return $this->getContextualStore('session', new ContextualSession($this));
            case 'flash':
                return $this->getContextualStore('flash', new ContextualFlash($this));
            case 'storage':
                return $this->getContextualStore('storage', new ContextualStorage($this));
            case 'cookie':
                return $this->getContextualStore('cookie', new ContextualCookie($this));
            case 'resource':
                return $this->getContextualStore('resource', new ContextualResource($this));
            case 'css':
                return $this->getContextualStore('css', new ContextualCss($this));
            case 'js':
                return $this->getContextualStore('js', new ContextualJs($this));
            case 'img':
                return $this->getContextualStore('img', new ContextualImage($this));
            case 'request':
                // Before Statamic 2.6, you could access the request by $this->request inside controllers.
                // In 2.6, we removed that and added this here so it would work for backwards compatibility.
                if ($this instanceof \Statamic\Extend\Controller) {
                    \Log::notice('$this->request is deprecated. You should typehint the Request object in your controller methods.');
                    return request();
                }
        }

        throw new \ErrorException(
            sprintf('Undefined property: %s::$%s', static::class, $key)
        );
    }

    private function getContextualStore($key, $class)
    {
        return app(Store::class)
            ->getOrPut($this->getAddonClassName(), collect())
            ->getOrPut($key, $class);
    }

    /**
     * Load the addon's bootstrap file, if available.
     * Useful for an addon to use a composer autoloader, for example.
     */
    public function bootstrap()
    {
        $path = $this->getDirectory() . '/bootstrap.php';

        if (File::exists($path)) {
            require_once $path;
        }
    }

    /**
     * Initialize the addon without needing to re-construct the class
     */
    protected function init()
    {

    }

    /**
     * Get the name of the addon, uncustomized by meta.yaml
     *
     * @return string
     */
    public function getAddonClassName()
    {
        return explode('\\', get_called_class())[2];
    }

    public function getClassNameWithoutSuffix()
    {
        $suffixes = [
            'API', 'Command', 'Controller', 'Fieldtype', 'Filter', 'Listener',
            'Modifier', 'ServiceProvider', 'Tags', 'Tasks', 'Widget'
        ];

        $original = $class = last(explode('\\', get_called_class()));

        foreach ($suffixes as $suffix) {
            $class = Str::removeRight($class, $suffix);

            if ($class !== $original) {
                return $class;
            }
        }

        return $class;
    }

    /**
     * Whether the addon is bundled with Statamic.
     *
     * (ie. located inside the statamic directory)
     *
     * @return bool
     */
    public function isFirstParty()
    {
        $reflection = new \ReflectionClass($this);

        return Str::contains(
            Path::tidy($reflection->getFileName()),
            'statamic/bundles/'
        );
    }

    /**
     * Whether the addon is third party.
     *
     * (ie. located outside of the statamic directory)
     *
     * @return bool
     */
    public function isThirdParty()
    {
        return ! $this->isFirstParty();
    }

    /**
     * Get the name of the addon, which might be customized in meta.yaml
     *
     * @return mixed|string
     */
    public function getAddonName()
    {
        if ($name = array_get($this->getMeta(), 'name')) {
            return $name;
        }

        return $this->getAddonClassName();
    }

    /**
     * Gets the type of plugin
     *
     * @return string
     */
    public function getAddonType()
    {
        $class_bits = explode('\\', get_called_class());
        $class = last($class_bits);
        $split = preg_split('/(?<=[a-z])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][a-z])/', $class);

        return end($split);
    }

    /**
     * Get the meta information
     *
     * @return array
     */
    public function getMeta()
    {
        $file = $this->getDirectory() . '/meta.yaml';

        if (! File::exists($file)) {
            return [];
        }

        return YAML::parse(File::get($file));
    }

    /**
     * Emit a namespaced event
     *
     * @param string $event  Name of the event
     * @param mixed  $payload  Data to send with the event
     * @return mixed
     */
    public function emitEvent($event, $payload)
    {
        return event($this->addon_name . '.' . $event, $payload);
    }

    /**
     * Access the API class of a $addon
     *
     * @param string|null $addon Name of the addon
     * @return mixed The API class for the addon, if it exists
     * @throws \Statamic\Exceptions\ApiNotFoundException
     */
    public function api($addon = null)
    {
        $addon = $addon ?: $this->getAddonClassName();

        try {
            return addon($addon);
        } catch (ReflectionException $e) {
            throw new ApiNotFoundException("No such class [{$addon}API]");
        }
    }

    /**
     * Retrieves a config variable, or the whole array
     *
     * @param null|string|array $keys Keys of parameter to return
     * @param mixed $default  Default value to return if not set
     * @return mixed
     */
    public function getConfig($keys = null, $default = null)
    {
        $config = app(Addons::class)->get(Str::snake($this->addon_name)) ?: [];

        if (is_null($keys)) {
            return $config;
        }

        if (! is_array($keys)) {
            $keys = [$keys];
        }

        foreach ($keys as $key) {
            if (isset($config[$key])) {
                return $config[$key];
            }
        }

        return $default;
    }

    /**
     * Same as $this->getConfig(), but treats as a boolean
     *
     * @param string|array $keys
     * @param null         $default
     * @return bool
     */
    public function getConfigBool($keys, $default = null)
    {
        return bool($this->getConfig($keys, $default));
    }

    /**
     * Same as $this->getConfig(), but treats as an integer
     *
     * @param string|array $keys
     * @param null         $default
     * @return bool
     */
    public function getConfigInt($keys, $default = null)
    {
        return int($this->getConfig($keys, $default));
    }

    /**
     * Get this addon's root directory.
     *
     * eg. /path/to/site/addons/SomeAddon or /path/to/statamic/bundles/SomeAddon
     *
     * @return string
     */
    public function getDirectory()
    {
        $path = $this->getAddonClassName();

        return $this->isFirstParty() ? bundles_path($path) : addons_path($path);
    }

    /**
     * Create an email and automatically set the path to the views
     *
     * @return \Statamic\Email\Builder
     */
    public function email()
    {
        $email = Email::create();

        $email->in($this->getDirectory() . '/resources/views');

        return $email;
    }

    /**
     * Generate an action URL
     *
     * @param string $url
     * @param bool   $relative
     * @return string
     */
    public function actionUrl($url, $relative = true)
    {
        return $this->eventUrl($url, $relative);
    }

    /**
     * Generate an event url
     *
     * @param string $url
     * @param bool $relative
     * @return string
     */
    public function eventUrl($url, $relative = true)
    {
        $url = URL::tidy(
            URL::prependSiteUrl(EVENT_ROUTE . '/' . $this->getAddonClassName() . '/' . $url)
        );

        if ($relative) {
            $url = URL::makeRelative($url);
        }

        return $url;
    }

    protected function trans($key, $params = [])
    {
        return trans('addons.'.$this->getAddonClassName().'::'.$key, $params);
    }

    protected function transChoice($key, $number)
    {
        return trans_choice('addons.'.$this->getAddonClassName().'::'.$key, $number);
    }

    /**
     * Render a Blade view from within the addon's views directory
     *
     * @param string $view  Name of the view
     * @param array  $data  Data to pass into the view
     * @return \Illuminate\View\View
     */
    public function view($view, $data = [])
    {
        $directory = $this->getDirectory() . '/resources/views';

        $namespace = $this->getAddonClassName();

        app('view')->getFinder()->addNamespace($namespace, $directory);

        return view($namespace.'::'.$view, $data);
    }
}
