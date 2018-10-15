<?php

namespace Statamic\Data\Structures;

use Statamic\API\Entry as EntryAPI;
use Statamic\Data\Content\UrlBuilder;
use Statamic\Contracts\Data\Entries\Entry;

class Page implements Entry
{
    protected $entry;
    protected $route;
    protected $parent;
    protected $children;

    public function setEntry($entry): self
    {
        $this->entry = $entry;

        return $this;
    }

    public function entry(): ?Entry
    {
        if (is_string($this->entry)) {
            $this->entry = $this->actualEntry();
        }

        return $this->entry;
    }

    protected function actualEntry(): ?Entry
    {
        return EntryAPI::find($this->entry);
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

    public function uri()
    {
        return app(UrlBuilder::class)
            ->content($this)
            ->merge([
                'parent_uri' => $this->parent ? $this->parent->uri() : '',
                'slug' => $this->isParent() ? '' : $this->slug()
            ])
            ->build($this->route);
    }

    protected function isParent()
    {
        return $this->entry()->id() === optional($this->parent)->id();
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
            ->setRoute($this->route);
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
            'url' => $this->uri(),
            'permalink' => $this->absoluteUrl(),
        ]);
    }

    public function editUrl()
    {
        return $this->entry()->editUrl();
    }

    public function fieldset($fieldset = null)
    {
        return $this->entry()->fieldset($fieldset);
    }

    public function in($locale)
    {
        return new \Statamic\Data\LocalizedData($locale, $this);
    }

    public function __call($method, $args)
    {
        return call_user_func_array([$this->entry(), $method], $args);
    }
}
