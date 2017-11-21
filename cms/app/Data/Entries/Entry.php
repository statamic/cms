<?php

namespace Statamic\Data\Entries;

use Carbon\Carbon;
use Statamic\API\File;
use Statamic\API\Path;
use Statamic\API\Config;
use Statamic\API\Fieldset;
use Statamic\Data\Content\Content;
use League\Flysystem\FileNotFoundException;
use Statamic\API\Collection as CollectionAPI;
use Statamic\Data\Content\HasLocalizedSlugsInData;
use Statamic\Exceptions\InvalidEntryTypeException;
use Statamic\Contracts\Data\Entries\Entry as EntryContract;

class Entry extends Content implements EntryContract
{
    /**
     * Allows localized slugs to be placed in front matter
     *
     * Used by entries and terms
     */
    use HasLocalizedSlugsInData;

    /**
     * Get or set the path
     *
     * @param string|null $path
     * @return string
     */
    public function path($path = null)
    {
        if (! is_null($path)) {
            dd('todo: set an entrys path in entry@path'); // @todo
        }

        if (isset($this->attributes['path'])) {
            return $this->attributes['path'];
        }

        return $this->buildPath();
    }

    /**
     * Get the path to a localized version
     *
     * @param string $locale
     * @return string
     */
    public function localizedPath($locale)
    {
        return $this->buildPath(compact('locale'));
    }

    /**
     * Get the path before the object was modified.
     *
     * @return string
     */
    public function originalPath()
    {
        $attr = $this->original['attributes'];

        $attr['order'] = array_get($this->original, 'attributes.order', false);

        return $this->buildPath($attr);
    }

    /**
     * Get the path to a localized version before the object was modified.
     *
     * @param string $locale
     * @return string
     */
    public function originalLocalizedPath($locale)
    {
        $attr = $this->original['attributes'];

        $attr['locale'] = $locale;

        $attr['order'] = array_get($this->original, 'attributes.order', false);

        return $this->buildPath($attr);
    }

    /**
     * Dynamically build the file path
     *
     * @param array $data Overrides for any arguments.
     * @return string
     */
    private function buildPath($data = [])
    {
        return app('Statamic\Contracts\Data\Content\PathBuilder')
            ->entry()
            ->slug(array_get($data, 'slug', $this->attributes['slug']))
            ->collection(array_get($data, 'collection', $this->collectionName()))
            ->published(array_get($data, 'published', $this->published()))
            ->order(array_get($data, 'order', $this->order()))
            ->extension(array_get($data, 'data_type', $this->dataType()))
            ->locale(array_get($data, 'locale', $this->locale()))
            ->get();
    }

    /**
     * Get or set the associated collection
     *
     * @param Collection|string|null $collection
     * @return Collection
     */
    public function collection($collection = null)
    {
        if (is_null($collection)) {
            return CollectionAPI::whereHandle($this->attributes['collection']);
        }

        // If we've been passed an actual collection, we just need the name of it.
        if ($collection instanceof Collection) {
            $collection = $collection->basename();
        }

        $this->attributes['collection'] = $collection;
    }

    /**
     * Get or set the name of the associated collection
     *
     * @param string|null $name
     * @return string
     */
    public function collectionName($name = null)
    {
        if (is_null($name)) {
            return $this->attributes['collection'];
        }

        $this->attributes['collection'] = $name;
    }

    /**
     * Get or set the URI
     *
     * This is the "identifying URL" for lack of a better description.
     * For instance, where `/fr/blog/my-post` would be a URL, `/blog/my-post` would be the URI.
     *
     * @param string|null $uri
     * @return mixed
     * @throws \Exception
     */
    public function uri($uri = null)
    {
        if ($uri) {
            throw new \Exception('Cannot set the URI on an entry directly.');
        }

        $routes = array_get(Config::getRoutes(), 'collections', []);

        if (! $route = array_get($routes, $this->collectionName())) {
            return false;
        }

        return app('Statamic\Contracts\Data\Content\UrlBuilder')->content($this)->build($route);
    }

    /**
     * The URL to edit it in the CP
     *
     * @return mixed
     */
    public function editUrl()
    {
        $slug = $this->in(default_locale())->slug();

        return cp_route('entry.edit', $this->collectionName() . '/' . $slug);
    }

    /**
     * Get the order type (date, number, alphabetical)
     *
     * @return string
     */
    public function orderType()
    {
        return $this->collection()->order();
    }

    /**
     * Get the entry's date
     *
     * @return \Carbon\Carbon
     * @throws \Statamic\Exceptions\InvalidEntryTypeException
     */
    public function date()
    {
        if ($this->orderType() !== 'date') {
            throw new InvalidEntryTypeException(
                sprintf('Cannot get the date on an non-date based entry: [%s]', $this->path())
            );
        }

        if (substr_count($this->order(), '-') < 1) {
            throw new InvalidEntryTypeException(
                sprintf('Entry date not present in a date-based entry: [%s]', $this->path())
            );
        }

        return (strlen($this->order()) == 15)
            ? Carbon::createFromFormat('Y-m-d-Hi', $this->order())
            : Carbon::createFromFormat('Y-m-d', $this->order())->startOfDay();
    }

    /**
     * Does the entry have a timestamp?
     *
     * @return bool
     */
    public function hasTime()
    {
        return $this->orderType() === 'date' && strlen($this->order()) === 15;
    }

    /**
     * Get data from the cascade (folder.yaml files)
     *
     * @return array
     */
    protected function cascadingData()
    {
        if ($collection = $this->collection()) {
            return $collection->data();
        }

        return [];
    }

    /**
     * Get or set the template
     *
     * @param string|null $template
     * @return mixed
     */
    public function template($template = null)
    {
        if (is_null($template)) {
            return [
                $this->getWithCascade('template'), // gets `template` from the entry, and falls back to what's in folder.yaml
                Config::get('theming.default_entry_template'),
                Config::get('theming.default_page_template')
            ];
        }

        $this->set('template', $template);
    }

    /**
     * Get or set the layout
     *
     * @param string|null $layout
     * @return mixed
     */
    public function layout($layout = null)
    {
        if (is_null($layout)) {
            // First, check the front-matter
            if ($layout = $this->getWithCascade('layout')) {
                return $layout;
            }

            // Lastly, return a default
            return Config::get('theming.default_layout');
        }

        $this->set('layout', $layout);
    }

    /**
     * Get the folder of the file relative to content path
     *
     * @return string
     */
    public function folder()
    {
        $dir = Path::directory($this->path());

        $dir = preg_replace('#^collections/#', '', $dir);

        return (str_contains($dir, '/')) ? explode('/', $dir)[0] : $dir;
    }

    /**
     * Get the fieldset
     *
     * @return string|null
     */
    protected function getFieldset()
    {
        // First check the front matter
        if ($fieldset = $this->getWithCascade('fieldset')) {
            return Fieldset::get($fieldset);
        }

        // Then the default content fieldset
        $fieldset = Config::get('theming.default_' . $this->contentType() . '_fieldset');
        $path = settings_path('fieldsets/'.$fieldset.'.yaml');
        if (File::exists($path)) {
            return Fieldset::get($fieldset);
        }

        // Finally the default fieldset
        return Fieldset::get(Config::get('theming.default_fieldset'));
    }

    /**
     * Add supplemental data to the attributes
     *
     * Some data on the entry is dynamic and only available through methods.
     * When we want to use these when preparing for use in a template for
     * example, we will need these available in the front-matter.
     */
    public function supplement()
    {
        parent::supplement();

        if ($this->orderType() === 'date') {
            $this->supplements['date'] = $this->date();
            $this->supplements['datestring'] = $this->date()->__toString();
            $this->supplements['datestamp'] = $this->date()->timestamp;
            $this->supplements['timestamp'] = $this->date()->timestamp;
            $this->supplements['has_timestamp'] = $this->hasTime();
        }

        $this->supplements['order_type'] = $this->orderType();
        $this->supplements['collection'] = $this->collectionName();
        $this->supplements['is_entry'] = true;

        // If the file isn't found, it's probably temporary content created during a sneak peek.
        try {
            $this->supplements['last_modified'] = File::disk('content')->lastModified($this->path());
        } catch (FileNotFoundException $e) {
            $this->supplements['last_modified'] = time();
        }
    }
}
