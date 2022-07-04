<?php

namespace Statamic\Structures;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Traits\ForwardsCalls;
use JsonSerializable;
use Statamic\Contracts\Auth\Protect\Protectable;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Data\Augmented;
use Statamic\Contracts\Entries\Entry;
use Statamic\Contracts\GraphQL\ResolvesValues as ResolvesValuesContract;
use Statamic\Contracts\Routing\UrlBuilder;
use Statamic\Contracts\Structures\Nav;
use Statamic\Data\ContainsSupplementalData;
use Statamic\Data\HasAugmentedInstance;
use Statamic\Data\TracksQueriedColumns;
use Statamic\Facades\Blink;
use Statamic\Facades\Collection;
use Statamic\Facades\Site;
use Statamic\Facades\URL;
use Statamic\GraphQL\ResolvesValues;

class Page implements Entry, Augmentable, Responsable, Protectable, JsonSerializable, ResolvesValuesContract, ArrayAccess, Arrayable
{
    use HasAugmentedInstance, ForwardsCalls, TracksQueriedColumns, ResolvesValues, ContainsSupplementalData;

    protected $tree;
    protected $reference;
    protected $route;
    protected $parent;
    protected $children;
    protected $isRoot = false;
    protected $id;
    protected $url;
    protected $title;
    protected $depth;
    protected $data = [];

    public function __construct()
    {
        $this->supplements = collect();
    }

    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    public function url()
    {
        return $this->url ?? optional($this->entry())->url();
    }

    public function urlWithoutRedirect()
    {
        return $this->url ?? optional($this->entry())->urlWithoutRedirect();
    }

    public function isRedirect()
    {
        return optional($this->entry())->isRedirect() ?? false;
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

    public function hasCustomTitle()
    {
        return $this->title !== null;
    }

    public function hasCustomUrl()
    {
        return $this->url !== null;
    }

    public function setEntry($reference): self
    {
        if ($reference === null) {
            return $this;
        }

        if (is_object($reference)) {
            throw_unless($id = $reference->id(), new \Exception('Cannot set an entry without an ID'));
            Blink::store('structure-entries')->put($id, $reference);
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

        if ($cached = Blink::store('structure-entries')->get($this->reference)) {
            return $cached;
        }

        return $this->tree->entry($this->reference);
    }

    public function reference()
    {
        return $this->reference;
    }

    public function referenceExists()
    {
        return $this->entry() !== null;
    }

    public function parent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
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
        if ($this->url) {
            return $this->url();
        }

        if (! $this->reference) {
            return null;
        }

        $uris = Blink::store('structure-uris');

        if ($this->tree->uriCacheEnabled() && ($cached = $uris[$this->reference] ?? null)) {
            return $cached;
        }

        if (! $this->structure() instanceof CollectionStructure) {
            return $uris[$this->reference] = $this->entry()->uri();
        }

        return $uris[$this->reference] = app(UrlBuilder::class)
            ->content($this)
            ->merge([
                'parent_uri' => $this->parent && ! $this->parent->isRoot() ? $this->parent->uri() : '',
                'slug' => $this->isRoot() ? '' : $this->slug(),
                'depth' => $this->depth,
                'is_root' => $this->isRoot(),
            ])
            ->build($this->route);
    }

    public function absoluteUrl()
    {
        if ($this->url) {
            return URL::makeAbsolute($this->url);
        }

        return optional($this->entry())->absoluteUrl();
    }

    public function absoluteUrlWithoutRedirect()
    {
        if ($this->url) {
            return $this->absoluteUrl();
        }

        return optional($this->entry())->absoluteUrlWithoutRedirect();
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

    public function setPageData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function pageData()
    {
        return collect($this->data);
    }

    public function data()
    {
        $data = $this->pageData();

        if ($entry = $this->entry()) {
            $data = $entry->data()->merge($data);
        }

        return $data;
    }

    public function values()
    {
        $data = $this->pageData();

        if ($entry = $this->entry()) {
            $data = $entry->values()->merge($data);
        }

        return $data;
    }

    public function get(string $key, $fallback = null)
    {
        if ($value = $this->data[$key] ?? null) {
            return $value;
        }

        if ($entry = $this->entry()) {
            $value = $entry->get($key);
        }

        return $value ?? $fallback;
    }

    public function value(string $key)
    {
        if ($value = $this->data[$key] ?? null) {
            return $value;
        }

        if ($entry = $this->entry()) {
            $value = $entry->value($key);
        }

        return $value;
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

    public function newAugmentedInstance(): Augmented
    {
        return new AugmentedPage($this);
    }

    public function shallowAugmentedArrayKeys()
    {
        return optional($this->entry())->shallowAugmentedArrayKeys() ?? ['title', 'url'];
    }

    public function editUrl()
    {
        return optional($this->entry())->editUrl();
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function id()
    {
        return $this->id;
    }

    public function in($site)
    {
        if ($this->reference && $this->referenceExists()) {
            if (! $entry = $this->entry()->in($site)) {
                return null;
            }

            return $this->structure()->in($site)->findByEntry($entry->id());
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

    public function published()
    {
        return $this->entry()->published();
    }

    public function private()
    {
        return $this->entry()->private();
    }

    public function status()
    {
        return optional($this->entry())->status();
    }

    public function blueprint()
    {
        if ($this->structure() instanceof Nav) {
            return $this->structure()->blueprint();
        }
    }

    public function collection()
    {
        return Collection::findByMount($this);
    }

    public function getProtectionScheme()
    {
        return optional($this->entry())->getProtectionScheme();
    }

    public function __call($method, $args)
    {
        return $this->forwardCallTo($this->entry(), $method, $args);
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        // hmmm? ->with() ?

        return $this
            ->toAugmentedCollection($this->selectedQueryColumns)
            ->withShallowNesting()
            ->toArray();
    }
}
