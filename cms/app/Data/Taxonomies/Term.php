<?php

namespace Statamic\Data\Taxonomies;

use Statamic\API\Data;
use Statamic\API\Config;
use Statamic\API\Entry;
use Statamic\API\Fieldset;
use Statamic\API\File;
use Statamic\API\Path;
use Statamic\API\Str;
use Statamic\API\YAML;
use Statamic\API\Taxonomy as TaxonomyAPI;
use Statamic\Contracts\Data\Taxonomies\Term as TermContract;
use Statamic\Data\Content\Content;
use Statamic\Data\Content\ContentCollection;
use Statamic\Data\Content\HasLocalizedSlugsInData;
use Statamic\Contracts\Data\Taxonomies\Taxonomy as TaxonomyContract;
use Statamic\Data\Services\TermsService;

class Term extends Content implements TermContract
{
    /**
     * The content that is associated to this term.
     *
     * @var ContentCollection
     */
    private $collection;

    /**
     * Get or set the ID
     *
     * @param mixed $id
     * @return string
     */
    public function id($id = null)
    {
        if ($id && $id !== true) {
            throw new \Exception('A taxonomy term ID cannot be set directly.');
        }

        return $this->taxonomyName() . '/' . $this->defaultSlug();
    }

    /**
     * Get or set the slug
     *
     * @param string|null $slug
     * @return mixed
     */
    public function slug($slug = null)
    {
        if (is_null($slug)) {
            return \Statamic\API\Term::normalizeSlug($this->getSlug());
        }

        $this->setSlug($slug);
    }

    /**
     * Get the slug
     *
     * @return string
     */
    protected function getSlug()
    {
        // Remove any hidden/draft indicators.
        $slug = $this->defaultSlug();

        // For localized versions, the slug is contained within the taxonomy.
        if (! $this->isDefaultLocale()) {
            if ($localizedSlug = $this->taxonomy()->getLocalizedSlug($this->locale(), $slug)) {
                return $localizedSlug;
            }
        }

        return $slug;
    }

    /**
     * Get the slug in the default locale
     *
     * @return string
     */
    protected function defaultSlug()
    {
        return ltrim($this->attributes['slug'], '_');
    }

    /**
     * Set the slug
     *
     * @param $slug
     */
    protected function setSlug($slug)
    {
        if ($this->isDefaultLocale()) {
            // If this content belongs to the default locale, we want to update
            // the slug property. It is not stored in the front matter.
            $this->attributes['slug'] = $slug;
        } else {
            // If this is not the default locale, we want to store the slug in the
            // front-matter and leave the property as-is. Also, we only need to
            // store the slug if it's different from the default locale slug.
            if ($slug !== $this->get('slug')) {
                $this->set('slug', $slug);
            }
        }
    }

    /**
     * Get or set the path
     *
     * @param string|null $path
     * @return string
     */
    public function path($path = null)
    {
        if (! is_null($path)) {
            dd('todo: set a terms path in term@path'); // @todo
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
        return $this->path();
    }

    /**
     * Get the path before the object was modified.
     *
     * @return string
     */
    public function originalPath()
    {
        return $this->buildPath($this->original['attributes']);
    }

    /**
     * Get the path to a localized version before the object was modified.
     *
     * @param string $locale
     * @return string
     */
    public function originalLocalizedPath($locale)
    {
        return $this->originalPath();
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
            ->term()
            ->slug(array_get($data, 'slug', $this->attributes['slug']))
            ->taxonomy(array_get($data, 'taxonomy', $this->taxonomyName()))
            ->published(array_get($data, 'published', $this->published()))
            ->order(array_get($data, 'order', $this->order()))
            ->extension(array_get($data, 'data_type', $this->dataType()))
            ->locale(array_get($data, 'locale', $this->locale()))
            ->get();
    }

    /**
     * Get or set the associated taxonomy
     *
     * @param TaxonomyContract|string|null $taxonomy
     * @return TaxonomyContract
     */
    public function taxonomy($taxonomy = null)
    {
        if (is_null($taxonomy)) {
            return TaxonomyAPI::whereHandle($this->attributes['taxonomy']);
        }

        // If we've been passed an actual collection, we just need the name of it.
        if ($taxonomy instanceof TaxonomyContract) {
            $taxonomy = $taxonomy->basename();
        }

        $this->attributes['taxonomy'] = $taxonomy;
    }

    /**
     * Get or set the name of the associated taxonomy
     *
     * @param string|null $name
     * @return string
     */
    public function taxonomyName($name = null)
    {
        if (is_null($name)) {
            return $this->attributes['taxonomy'];
        }

        $this->attributes['taxonomy'] = $name;
    }

    /**
     * Get or set the content that is related to this term
     *
     * @param ContentCollection|null $collection
     * @return ContentCollection
     */
    public function collection(ContentCollection $collection = null)
    {
        if (! is_null($collection)) {
            return $this->collection = $collection;
        }

        // If a collection has been set explicitly, use that instead of fetching dynamically.
        if ($this->collection) {
            return $this->collection;
        }

        // If there's no ID, we're probably dealing with a temporary term, like from
        // within a Sneak Peek. In that case, don't bother. There's no content.
        if (! $this->id()) {
            return collect_content();
        }

        return $this->collection = app(TermsService::class)->collection($this);
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
            throw new \Exception('Cannot set the URL on an entry directly.');
        }

        $routes = array_get(Config::getRoutes(), 'taxonomies', []);

        if (! $route = array_get($routes, $this->taxonomyName())) {
            return false;
        }

        return app('Statamic\Contracts\Data\Content\UrlBuilder')->content($this)->build($route);
    }

    /**
     * Get the URL localized to the current locale
     *
     * @return string
     */
    public function localizedUrl()
    {
        $routes = array_get(Config::getRoutes(), 'taxonomies', []);

        if (! $route = array_get($routes, $this->taxonomyName())) {
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
        $params = [$this->taxonomyName(), $this->defaultSlug()];

        if (! $this->isDefaultLocale()) {
            $params[] = 'locale='.$this->locale();
        }

        return cp_route('term.edit', $params);
    }

    /**
     * Get data from the cascade (folder.yaml files)
     *
     * @return array
     */
    protected function cascadingData()
    {
        return $this->taxonomy()->data();
    }

    /**
     * Get or set the template
     *
     * @param string|null $template
     * @return mixed
     */
    public function template($template = null)
    {
        return [
            $this->getWithCascade('template'), // gets `template` from the entry, and falls back to what's in folder.yaml
            $this->taxonomyName(),
            Config::get('theming.default_taxonomy_template'),
            Config::get('theming.default_page_template')
        ];
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

        $dir = preg_replace('#^taxonomies/#', '', $dir);

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
     * Get the number of content objects that related to this taxonomy
     *
     * @return int
     */
    public function count()
    {
        return $this->collection()->count();
    }

    /**
     * Add supplemental data to the attributes
     *
     * Some data on the taxonomy is dynamic and only available through methods.
     * When we want to use these when preparing for use in a template for
     * example, we will need these available in the front-matter.
     */
    public function supplement()
    {
        parent::supplement();

        $this->supplements['default_slug'] = $this->defaultSlug();
        $this->supplements['title'] = $this->title();
        $this->supplements['taxonomy_group'] = $this->taxonomyName(); // @todo: remove
        $this->supplements['taxonomy'] = $this->taxonomyName();
        $this->supplements['count'] = $this->count();
        $this->supplements['relation_count'] = $this->count();
        $this->supplements['is_term'] = true;
        $this->supplements['results'] = $this->count();
    }

    public function title()
    {
        if ($title = $this->getWithDefaultLocale('title')) {
            return $title;
        }

        $value = app('stache')->taxonomies->getTitle($this->id());

        return $value ?: $this->defaultSlug();
    }

    /**
     * Get the value as a string
     *
     * @return string
     * @throws \Statamic\Exceptions\ModifierException
     */
    public function __toString()
    {
        return $this->title();
    }

    /**
     * Write the files to disk
     *
     * @return void
     */
    protected function writeFiles()
    {
        $taxonomy = $this->taxonomy();

        // Create an array to store the eventual file contents. We'll start
        // with the default locale and nest additional ones beneath it.
        $data = $defaultData = $this->defaultData();

        unset($data['id'], $data['slug']);

        // Append additional localized data to the bottom of the array.
        foreach (collect($this->locales())->splice(1) as $locale) {
            $localized = $this->removeLocalizedDataIdenticalToDefault(
                $this->in($locale)->data(),
                $defaultData
            );

            unset($localized['id']);

            // If the slug was localized, remove it from the data and instead place
            // it in the configuration file. It's easier to manage by hand that way.
            $taxonomy->localizeSlug($locale, $this->slug(), array_get($localized, 'slug'));
            unset($localized['slug']);

            $data[$locale] = $localized;
        }

        // Get the before and after paths so we can rename if necessary.
        $path = $this->path();
        $original_path = $this->originalPath();

        File::disk('content')->put($path, YAML::dump($data));

        if ($path !== $original_path) {
            File::disk('content')->delete($original_path);
        }

        // If any slugs were added to the taxonomy, saving it will write the changes
        // to disk. We do that here so that there aren't multiple write operations.
        $taxonomy->save();
    }

    /**
     * Remove any localized data keys that are the identical to the default locale's data.
     *
     * @param array $localized
     * @param array $default
     * @return array
     */
    protected function removeLocalizedDataIdenticalToDefault($localized, $default)
    {
        foreach ($localized as $key => $value) {
            if ($key === 'title' && $value === $this->title()) {
                unset($localized['title']);
                continue;
            }

            if ($key === 'slug' && $value === $this->slug()) {
                unset($localized['slug']);
                continue;
            }

            if ($value === array_get($default, $key)) {
                unset($localized[$key]);
            }
        }

        return $localized;
    }

    /**
     * Perform any necessary operations after a delete has been completed
     *
     * @return void
     */
    protected function completeDelete()
    {
        $stache = app('stache')->taxonomies;

        // Fetch the data/content IDs associated to this term.
        $associations = $stache->getAssociations($this);

        // Remove the term from the Stache.
        $stache->removeTerm($this->taxonomyName(), $this->slug());

        // Iterate over any associated content and remove references to this term.
        $associations->map(function ($id) {
            $data = Data::find($id);
            return ($data->has($this->taxonomyName())) ? $data : null;
        })->filter()->each(function ($data) {
            $this->removeFromData($data);
        });
    }

    /**
     * Remove the reference of this term from a given data object
     *
     * @param \Statamic\Contracts\Data\Data $data
     * @return void
     */
    private function removeFromData(\Statamic\Contracts\Data\Data $data)
    {
        $taxonomy = $this->taxonomyName();

        $terms = $data->get($taxonomy);

        // Get rid of the term we're removing. We can treat it as an array.
        // If it started as a string, there would only be one term anyway, so it'll end up being removed.
        $terms = collect($terms)->reject(function ($term) {
            return \Statamic\API\Term::normalizeSlug($term) === $this->slug();
        })->values();

        // If the removal of this term results in an empty field, we can simply remove it.
        if ($terms->isEmpty()) {
            $data->remove($taxonomy);
        } else {
            $data->set($taxonomy, $terms->all());
        }

        $data->save();
    }
}
