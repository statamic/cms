<?php

namespace Statamic\Stache\Listeners;

use Statamic\API\Str;
use Statamic\API\Path;
use Statamic\Stache\Stache;
use Statamic\Contracts\Data\Data;
use Statamic\Events\Data\PageDeleted;
use Statamic\Events\Data\TermDeleted;
use Statamic\Events\Data\UserDeleted;
use Statamic\Data\Pages\PageStructure;
use Statamic\Events\Data\EntryDeleted;
use Statamic\Contracts\Data\Pages\Page;
use Statamic\Contracts\Data\Users\User;
use Statamic\Events\Data\UserGroupDeleted;
use Statamic\Contracts\Data\Entries\Entry;
use Statamic\Contracts\Data\Taxonomies\Term;
use Statamic\Contracts\Assets\AssetContainer;
use Statamic\Events\Data\AssetContainerSaved;
use Statamic\Contracts\Permissions\UserGroup;
use Statamic\Contracts\Data\Globals\GlobalSet;
use Statamic\Events\Data\AssetContainerDeleted;

class UpdateItem
{
    /**
     * @var Stache
     */
    protected $stache;

    /**
     * @var Data
     */
    protected $data;

    /**
     * Create a new listener
     *
     * @param Stache $stache
     */
    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
    }

    /**
     * Register the listeners for the subscriber
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen('content.saved', self::class.'@updateSavedContent');

        $events->listen(
            ['user.saved', 'usergroup.saved'],
            self::class.'@updateSavedItem'
        );

        $events->listen(AssetContainerSaved::class, self::class.'@updateAssetContainer');

        $events->listen(EntryDeleted::class, self::class.'@removeDeletedEntry');
        $events->listen(PageDeleted::class, self::class.'@removeDeletedPages');
        $events->listen(UserDeleted::class, self::class.'@removeDeletedUser');
        $events->listen(UserGroupDeleted::class, self::class.'@removeDeletedUserGroup');
        $events->listen(AssetContainerDeleted::class, self::class.'@removeDeletedAssetContainer');
    }

    /**
     * Update a saved content item
     *
     * @param  Data $data       The data object being updated.
     * @param  array $original  The original attributes for the object.
     * @return voif
     */
    public function updateSavedContent($data, $original)
    {
        $this->updateSavedItem($data, $original);
    }

    /**
     * Update a saved item
     *
     * @param Data|mixed $data
     * @param array $original
     * @return void
     */
    public function updateSavedItem($data, $original = null)
    {
        if ($data instanceof Term) {
            return;
        }

        $this->data = $data;

        if (! $repo = $this->repo()) {
            \Log::error("Uncaught data type encountered when updating the Stache.");
            return;
        }

        $id = $data->id();

        $path = ($data instanceof UserGroup) ? 'site/settings/users/groups.yaml' : $data->path();

        $this->stache->repo($repo)
            ->load()
            ->setPath($id, $path)
            ->setItem($id, $data);

        if ($this->stache->driver($this->driverKey($repo))->isRoutable()) {
            $this->stache->repo($repo)->setUri($id, $data->uri());
        }

        $this->stache->updated($this->repo());

        if ($repo === 'pages') {
            $this->updateSavedItem($data->structure());
            $this->updateChildPages($data, $original);
        }
    }

    /**
     * Update child pages after a parent has been updated.
     *
     * @param  Page $parent     The parent page.
     * @param  array $original  The original attributes for the parent page.
     * @return void
     */
    private function updateChildPages($parent, $original)
    {
        // If there's no path, it's likely a new page so there will be no children.
        if (! isset($original['attributes']['path'])) {
            return;
        }

        $oldUriPrefix = $original['attributes']['uri']  . '/';
        $newUriPrefix = $parent->uri() . '/';
        $oldPathPrefix = Path::directory($original['attributes']['path']);
        $newPathPrefix = Path::directory($parent->path());

        // If there have been no changes to the parent's URI or path, we don't need to do anything.
        if ($oldUriPrefix == $newUriPrefix && $oldPathPrefix == $newPathPrefix) {
            return;
        }

        // Get the child pages by the parent's old URI.
        // We cannot just do $parent->children() since the URI would be different at this point.
        $children = \Statamic\API\Page::all()->filter(function ($page) use ($oldUriPrefix) {
            return Str::startsWith($page->uri(), $oldUriPrefix);
        });

        // Determine the appropriate new URIs and paths to be used for the children.
        $children = $children->map(function ($child) use ($oldUriPrefix, $newUriPrefix, $oldPathPrefix, $newPathPrefix) {
            return [
                'page' => $child,
                'newUri' => substr_replace($child->uri(), $newUriPrefix, 0, strlen($oldUriPrefix)),
                'newPath' => substr_replace($child->path(), $newPathPrefix, 0, strlen($oldPathPrefix)),
            ];
        });

        // Update the URIs and paths of the child pages, then re-insert them into the Stache.
        $children->each(function ($item) {
            $page = $item['page'];
            $id = $page->id();

            $page->path($item['newPath']);
            $page->uri($item['newUri']);
            $page->syncOriginal();

            $this->stache->repo('pages')
                ->setPath($id, $item['newPath'])
                ->setItem($id, $page)
                ->setUri($id, $item['newUri']);

            $this->stache->repo('pagestructure')
                ->setPath($id, $item['newPath'])
                ->setItem($id, $page->structure());
        });
    }

    public function updateAssetContainer(AssetContainerSaved $event)
    {
        $this->updateSavedItem($event->container);
    }

    /**
     * Remove a deleted entry
     *
     * @param EntryDeleted $event
     */
    public function removeDeletedEntry(EntryDeleted $event)
    {
        // Get the collection from the path. There's only ever going to be one path.
        $collection = explode('/', $event->paths[0])[1];

        $key = 'entries::'.$collection;

        $this->stache->repo($key)->removeItem($event->id);

        $this->stache->updated($key);
    }

    /**
     * Remove a deleted page(s)
     *
     * @param PageDeleted $event
     */
    public function removeDeletedPages(PageDeleted $event)
    {
        $pages = $this->stache->repo('pages');
        $structure = $this->stache->repo('pagestructure');

        // When a page is deleted, so are any child pages.
        foreach ($event->paths as $path) {
            $id = $pages->getIdByPath($path);

            $pages->removeItem($id);
            $structure->removeItem($id);
        }

        $this->stache->updated('pages');
        $this->stache->updated('pagestructure');
    }

    /**
     * Remove a deleted user
     *
     * @param UserDeleted $event
     */
    public function removeDeletedUser(UserDeleted $event)
    {
        $this->stache->repo('users')->removeItem($event->id);
        $this->stache->updated('users');
    }

    /**
     * Remove a deleted user group
     *
     * @param UserGroupDeleted $event
     */
    public function removeDeletedUserGroup(UserGroupDeleted $event)
    {
        $this->stache->repo('usergroups')->removeItem($event->id);
        $this->stache->updated('usergroups');
    }

    /**
     * Remove a deleted asset container
     *
     * @param AssetContainerDeleted $event
     */
    public function removeDeletedAssetContainer(AssetContainerDeleted $event)
    {
        $this->stache->repo('assetcontainers')->removeItem($event->id);
        $this->stache->updated('assetcontainers');
    }

    /**
     * Get the appropriate Stache repo key
     *
     * @return string
     */
    protected function repo()
    {
        if ($this->data instanceof Page) {
            return 'pages';
        } elseif ($this->data instanceof PageStructure) {
            return 'pagestructure';
        } elseif ($this->data instanceof Entry) {
            return 'entries::' . $this->data->collectionName();
        } elseif ($this->data instanceof Term) {
            return 'terms::' . $this->data->taxonomyName();
        } elseif ($this->data instanceof GlobalSet) {
            return 'globals';
        } elseif ($this->data instanceof User) {
            return 'users';
        } elseif ($this->data instanceof UserGroup) {
            return 'usergroups';
        } elseif ($this->data instanceof AssetContainer) {
            return 'assetcontainers';
        }
    }

    /**
     * Get the driver key
     *
     * @param string $key
     * @return string
     */
    protected function driverKey($key)
    {
        return (Str::contains($key, '::')) ? explode('::', $key)[0] : $key;
    }
}
