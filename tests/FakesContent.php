<?php

namespace Tests;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Collection;
use Statamic\Facades\Blueprint;

trait FakesContent
{
    protected function createPage($slug, $attributes = [])
    {
        $this->makeCollection()->save();

        return tap($this->makePage($slug, $attributes))->save();
    }

    protected function createLink($slug, $attributes = [])
    {
        $this->makeCollection()->save();
        $this->makeLinkBlueprint()->save();

        return tap($this->makeLink($slug, $attributes))->blueprint('link')->save();
    }

    protected function makePage($slug, $attributes = [])
    {
        return EntryFactory::slug($slug)
            ->id($slug)
            ->collection('pages')
            ->data($attributes['with'] ?? [])
            ->make();
    }

    protected function makeLink($slug, $attributes = [])
    {
        return EntryFactory::slug($slug)
            ->id($slug)
            ->collection('pages')
            ->data($attributes['with'] ?? [])
            ->make()
            ->blueprint('link');
    }

    protected function makeCollection()
    {
        return Collection::make('pages')
            ->routes('{slug}')
            ->template('default');
    }

    protected function makeLinkBlueprint()
    {
        return Blueprint::make('link')
            ->setNamespace('collections.pages')
            ->setContents([
                'title' => __('Link'),
                'fields' => [
                    ['handle' => 'title', 'field' => ['type' => 'text']],
                    ['handle' => 'redirect', 'field' => ['type' => 'link', 'required' => true]],
                ],
            ])
            ->save();
    }
}
