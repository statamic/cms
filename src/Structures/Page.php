<?php

namespace Statamic\Structures;

use Statamic\Facades\URL;
use Statamic\Facades\Site;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry as EntryAPI;
use Statamic\Contracts\Entries\Entry;
use Statamic\Contracts\Routing\UrlBuilder;
use Illuminate\Contracts\Support\Responsable;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Data\HasAugmentedInstance;
use Statamic\Facades\Blink;

class Page implements Entry, Augmentable, Responsable
{
    use HasAugmentedInstance;

    protected $tree;
    protected $reference;
    protected $route;
    protected $parent;
    protected $children;
    protected $isRoot = false;
    protected $url;
    protected $title;
    protected $depth;

    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    public function url()
    {
        if ($this->url) {
            return $this->url;
        }

        if ($this->reference && $this->referenceExists()) {
            return URL::makeRelative($this->absoluteUrl());
        }
    }

    public function setDepth($depth)
    {
        $this->depth = $depth;

        return $this;
    }

    public function depth()
    {
        return $this->depth;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function title()
    {
        if ($this->title) {
            return $this->title;
        }

        return optional($this->entry())->value('title');
    }

    public function setEntry($reference): self
    {
        if ($reference === null) {
            return $this;
        }

        if (! is_string($reference)) {
            throw_unless($id = $reference->id(), new \Exception('Cannot set an entry without an ID'));
            Blink::store('structure-page-entries')->put($id, $reference);
            $reference = $id;
        }

        $this->reference = $reference;

        return $this;
    }

    public function entry(): ?Entry
    {
        if (! $this->reference) {
            return null;
        }

        return Blink::store('structure-page-entries')->once($this->reference, function () {
            return EntryAPI::find($this->reference);
        });
    }

    public function reference()
    {
        return $this->reference;
    }

    public function referenceExists()
    {
        return $this->entry() !== null;
    }

    public function parent(): ?Page
    {
        return $this->parent;
    }

    public function setParent(?Page $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function setRoute(?string $route): self
    {
        $this->route = $route;

        return $this;
    }

    public function route(): ?string
    {
        return $this->route;
    }

    public function slug()
    {
        return optional($this->entry())->slug();
    }

    public function uri()
    {
        if (! $this->reference) {
            return optional($this->parent)->uri();
        }

        $uris = app(UriCache::class);

        if ($cached = $uris[$this->reference] ?? null) {
            return $cached;
        }

        if ($cached = $uris[$this->reference] ?? null) {
            return $cached;
        }

        if (! $this->structure() instanceof CollectionStructure) {
            return $uris[$this->reference] = $this->entry()->uri();
        }

        return $uris[$this->reference] = app(UrlBuilder::class)
            ->content($this)
            ->merge([
                'parent_uri' => $this->parent && !$this->parent->isRoot() ? $this->parent->uri() : '',
                'slug' => $this->isRoot() ? '' : $this->slug(),
                'depth' => $this->depth,
                'is_root' => $this->isRoot(),
            ])
            ->build($this->route);
    }

    public function absoluteUrl()
    {
        if ($this->url) {
            return $this->url;
        }

        if ($this->reference && $this->referenceExists()) {
            return vsprintf('%s/%s', [
                rtrim($this->site()->absoluteUrl(), '/'),
                ltrim($this->uri(), '/')
            ]);
        }
    }

    public function isRoot()
    {
        return $this->isRoot;
    }

    public function setTree($tree)
    {
        $this->tree = $tree;

        return $this;
    }

    public function setRoot(bool $isRoot)
    {
        $this->isRoot = $isRoot;

        return $this;
    }

    public function setChildren(array $children): self
    {
        $this->children = $children;

        return $this;
    }

    public function pages()
    {
        $pages = (new Pages)
            ->setTree($this->tree)
            ->setPages($this->children ?? [])
            ->setParent($this)
            ->setDepth($this->depth + 1)
            ->prependParent(false);

        if ($this->route) {
            $pages->setRoute($this->route);
        }

        return $pages;
    }

    public function flattenedPages()
    {
        return $this->pages()->flattenedPages();
    }

    public function newAugmentedInstance()
    {
        return new AugmentedPage($this);
    }

    public function editUrl()
    {
        return optional($this->entry())->editUrl();
    }

    public function id()
    {
        return optional($this->entry())->id();
    }

    public function in($site)
    {
        if ($this->reference && $this->referenceExists()) {
            if (! $entry = $this->entry()->in($site)) {
                return null;
            }

            return $this->setEntry($entry->id());
        }

        return $this;
    }

    public function site()
    {
        if ($this->reference && $this->referenceExists()) {
            return $this->entry()->site();
        }

        return Site::current(); // TODO: Get it from the tree instead.
    }

    public function toResponse($request)
    {
        if ($this->reference && $this->referenceExists()) {
            return (new \Statamic\Http\Responses\DataResponse($this))->toResponse($request);
        }

        throw new \LogicException('A page without a reference to an entry cannot be rendered.');
    }

    public function structure()
    {
        return $this->tree->structure();
    }

    public function routeData()
    {
        return $this->entry()->routeData();
    }

    public function blueprint()
    {
        return optional($this->entry())->blueprint();
    }

    public function collection()
    {
        return Collection::findByMount($this);
    }
}
