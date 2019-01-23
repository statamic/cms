<?php

namespace Statamic\Data\Structures;

use Statamic\API\Entry as EntryAPI;
use Statamic\Data\Content\UrlBuilder;
use Statamic\Contracts\Data\Entries\Entry;
use Statamic\Data\Routable;

class Page implements Entry
{
    use Routable;

    protected $reference;
    protected $entry;
    protected $route;
    protected $parent;
    protected $children;
    protected $isRoot = false;

    public function setEntry($reference): self
    {
        if (! is_string($reference)) {
            $this->entry = $reference;
            $reference = $reference->id();
        }

        $this->reference = $reference;

        return $this;
    }

    public function entry(): ?Entry
    {
        if (!$this->reference && !$this->entry) {
            return null;
        }

        return $this->entry = $this->entry ?? EntryAPI::find($this->reference);
    }

    public function reference()
    {
        return $this->reference;
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

    public function setRoute(string $route): self
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
        return $this->entry()->slug();
    }

    public function uri()
    {
        return app(UrlBuilder::class)
            ->content($this)
            ->merge([
                'parent_uri' => $this->parent ? $this->parent->uri() : '',
                'slug' => $this->isRoot() ? '' : $this->slug()
            ])
            ->build($this->route);
    }

    public function isRoot()
    {
        return $this->isRoot;
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
        return (new Pages)
            ->setTree($this->children ?? [])
            ->setParent($this)
            ->setRoute($this->route)
            ->prependParent(false);
    }

    public function flattenedPages()
    {
        return $this->pages()->flattenedPages();
    }

    // TODO: tests for these

    public function toArray()
    {
        return array_merge($this->entry()->toArray(), [
            'url' => $this->url(),
            'uri' => $this->uri(),
            'permalink' => $this->absoluteUrl(),
        ]);
    }

    public function editUrl()
    {
        return $this->entry()->editUrl();
    }

    public function __call($method, $args)
    {
        return call_user_func_array([$this->entry(), $method], $args);
    }
}
