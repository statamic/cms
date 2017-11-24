<?php

namespace Statamic\Data\Pages;

use Statamic\API\Config;
use Statamic\API\Entry;
use Statamic\API\Fieldset;
use Statamic\API\File;
use Statamic\API\Folder;
use Statamic\API\Path;
use Statamic\API\Str;
use Statamic\API\URL;
use Statamic\Data\Content\Content;
use Statamic\Data\Services\PagesService;
use League\Flysystem\FileNotFoundException;
use Statamic\API\PageFolder as PageFolderAPI;
use Statamic\Contracts\Data\Pages\Page as PageContract;

class Page extends Content implements PageContract
{
    /**
     * Get or set the URI
     *
     * This is the "identifying URL" for lack of a better description.
     * For instance, where `/fr/blog/my-post` would be a URL, `/blog/my-post` would be the URI.
     *
     * @param string|null $uri
     * @return mixed
     */
    public function uri($uri = null)
    {
        if (is_null($uri)) {
            return ($this->isDefaultLocale()) ? $this->defaultUri() : $this->localizedUri();
        }

        $this->attributes['uri'] = Str::ensureLeft($uri, '/');
    }

    /**
     * Get the URI for the default locale
     *
     * @return string
     */
    protected function defaultUri()
    {
        return $this->attributes['uri'];
    }

    /**
     * Get the URI localized to the current locale
     *
     * @return string
     */
    protected function localizedUri()
    {
        if ($this->isDefaultLocale()) {
            return $this->uri();
        }

        return app(PagesService::class)
            ->localizedUris($this->locale())
            ->get($this->id(), $this->defaultUri());
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
            return $this->getSlug();
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
        if ($slug = $this->get('slug')) {
            return $slug;
        }

        return URL::slug($this->uri());
    }

    /**
     * Set the slug
     *
     * @param $slug
     */
    protected function setSlug($slug)
    {
        if ($this->isDefaultLocale()) {
            // If this content belongs to the default locale, we want
            // to update the slug in the url property. It is not
            // stored in the front matter.
            $uri = URL::replaceSlug($this->uri(), $slug);
            $this->uri($uri);

            // The path relies on the slug. We'll update it now.
            $this->attributes['path'] = $this->buildPath();
        } else {
            // If this is *not* the default locale, we want to store the slug
            // in the front-matter and leave the property as-is. Also, we
            // only need to store the slug if it's different from the
            // default locale slug.
            if ($slug !== $this->getSlug()) {
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
            $this->attributes['path'] = $path;
            return $this;
        }

        // Return the pre-built path if one exists and the page hasn't been modified.
        if (isset($this->attributes['path']) && $this->attributes === $this->original['attributes']) {
            return $this->attributes['path'];
        }

        return $this->attributes['path'] = $this->buildPath();
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

        $attr['default_published'] = $defaultPublished = $this->original['attributes']['published'];
        $attr['published'] = array_get($this->original, 'data.published', $defaultPublished);

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

        $attr['default_published'] = $this->original['attributes']['published'];
        $attr['locale'] = $locale;

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
        $builder = app('Statamic\Contracts\Data\Content\PathBuilder')
            ->page()
            ->uri(array_get($data, 'uri', $this->uri()))
            ->extension(array_get($data, 'data_type', $this->dataType() ?: Config::get('system.default_extension')))
            ->published(array_get($data, 'published', $this->published()))
            ->defaultPublished(array_get($data, 'default_published', $this->in(default_locale())->published()))
            ->order(array_get($data, 'order', $this->order()))
            ->locale(array_get($data, 'locale', $this->locale()));

        $parent_path = array_get(
            $data,
            'parent_path',
            isset($this->attributes['parent_path']) ? $this->attributes['parent_path'] : false
        );

        if ($parent_path) {
            $builder->parentPath($parent_path);
        }

        return $builder->get();
    }

    /**
     * Perform any necessary operations after a save has been completed
     *
     * @return void
     */
    protected function completeSave()
    {
        // If a page has been renamed, its child pages will need to be moved too.
        if ($this->originalPath() !== $this->path()) {
            Folder::disk('content')->rename(
                Path::directory($this->originalPath()),
                Path::directory($this->path())
            );
        }
    }

    /**
     * Get the paths of files to be deleted
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getPathsForDeletion()
    {
        // Deleting a default locale means all child pages should be deleted with
        // it. For the default locale, this is as simple as deleting the folder.
        if ($this->isDefaultLocale()) {
            $folder = Path::directory($this->path());
            return collect(Folder::disk('content')->getFilesRecursively($folder));
        }

        // For localized versions, we only want to delete that specific
        // locale and leave children and the default versions alone.
        return collect([$this->path()]);
    }

    /**
     * Perform any necessary operations after a delete has been completed
     *
     * @return void
     */
    protected function completeDelete()
    {
        // If pages were deleted, their files will have been moved, but
        // the empty folder will remain in tact. We'll just clean that up.
        if (Folder::disk('content')->isEmpty($dir = Path::directory($this->path()))) {
            Folder::disk('content')->delete($dir);
        }
    }

    /**
     * Add supplemental data to the attributes
     *
     * Some data on the page is dynamic and only available through methods.
     * When we want to use these when preparing for use in a template for
     * example, we will need these available in the front-matter.
     */
    public function supplement()
    {
        parent::supplement();

        $this->supplements['is_page'] = true;

        // If the file isn't found, it's probably temporary content created during a sneak peek.
        try {
            $this->supplements['last_modified'] = File::disk('content')->lastModified($this->path());
        } catch (FileNotFoundException $e) {
            $this->supplements['last_modified'] = time();
        }
    }

    /**
     * The URL to edit it in the CP
     *
     * @return mixed
     */
    public function editUrl()
    {
        return cp_route('page.edit', Str::removeLeft($this->uri(), '/'));
    }

    /**
     * Get data from the cascade (folder.yaml files)
     *
     * @return array
     */
    protected function cascadingData()
    {
        $path = Path::directory(Path::clean(substr($this->path(), 5)));

        $segments = explode('/', $path);

        $data = [];

        while (count($segments)) {
            $path = join('/', $segments);

            if ($folder = PageFolderAPI::whereHandle($path)) {
                $data = array_merge($folder->data(), $data);
            }

            array_pop($segments);
        }

        return $data;
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
     * Determine whether this page has entries
     *
     * @return bool|null
     */
    public function hasEntries()
    {
        return $this->hasWithCascade('mount');
    }

    /**
     * Get the entries mounted to this page
     *
     * @return \Statamic\Data\Entries\EntryCollection
     */
    public function entries()
    {
        if (! $this->hasEntries()) {
            return collect_entries();
        }

        return Entry::whereCollection($this->entriesCollection());
    }

    /**
     * Get the name of the entry collection mounted to this page
     *
     * @return string
     */
    public function entriesCollection()
    {
        return $this->getWithCascade('mount');
    }

    /**
     * Get this page's child pages
     *
     * @param null|int $depth
     * @return \Statamic\Data\Pages\PageCollection
     */
    public function children($depth = null)
    {
        $parent_uri = $this->defaultUri();

        // Get all the children
        $children = \Statamic\API\Page::all()->filter(function ($page) use ($parent_uri) {
            return Str::startsWith($page->uri(), Str::ensureRight($parent_uri, '/'));
        });

        // Remove pages that don't match the depth
        if ($depth) {
            $parent_slashes = substr_count($parent_uri, '/');

            if ($parent_uri === '/') {
                $parent_slashes = 0;
            }

            $children = $children->filter(function ($page) use ($parent_uri, $parent_slashes, $depth) {
                return $depth >= substr_count($page->uri(), '/') - $parent_slashes;
            });
        }

        // Remove the homepage, if we are the home page.
        if ($parent_uri == '/') {
            $children = $children->forget($this->id());
        }

        return $children;
    }

    /**
     * Get the parent page
     *
     * @return Page|null
     */
    public function parent()
    {
        if ($this->uri() === '/') {
            return null;
        }

        return \Statamic\API\Page::whereUri(
            URL::parent($this->uri())
        );
    }

    /**
     * Get the folder of the file relative to content path
     *
     * @return string
     */
    public function folder()
    {
        $dir = Path::directory($this->path());

        return preg_replace('#^pages/#', '', $dir);
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
     * Get the associated page structure object
     *
     * @return PageStructure
     */
    public function structure()
    {
        return new PageStructure($this->id());
    }

    /**
     * @inheritdoc
     */
    public function published($published = null)
    {
        if (is_null($published)) {
            return parent::published();
        }

        parent::published($published);

        // The path relies on the published state. We'll update it now.
        // But, only if it's initialized and using the default locale.
        if ($this->original && $this->isDefaultLocale()) {
            $this->attributes['path'] = $this->buildPath();
        }
    }
}
