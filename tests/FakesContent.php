<?php

namespace Tests;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Collection;

trait FakesContent
{
    protected function createPage($slug, $attributes = [])
    {
        $this->makeCollection()?->save();

        return tap($this->makePage($slug, $attributes))->save();
    }

    protected function makePage($slug, $attributes = [])
    {
        return EntryFactory::slug($slug)
            ->id($slug)
            ->collection('pages')
            ->data($attributes['with'] ?? [])
            ->make();
    }

    protected function makeCollection()
    {
        if (Collection::find('pages')) {
            return;
        }

        return Collection::make('pages')
            ->routes('{slug}')
            ->template('default');
    }
}
