<?php

namespace Tests\Feature\GraphQL;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;

trait CreatesQueryableTestEntries
{
    public function createEntries()
    {
        $article = Blueprint::makeFromFields([
            'intro' => ['type' => 'text'],
            'content' => ['type' => 'textarea'],
        ]);
        $artDirected = Blueprint::makeFromFields([
            'hero_image' => ['type' => 'text'],
            'content' => ['type' => 'textarea'],
        ]);
        $event = Blueprint::makeFromFields([]);
        $food = Blueprint::makeFromFields([
            'calories' => ['type' => 'integer'],
        ]);

        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'article' => $article->setHandle('article'),
            'art_directed' => $artDirected->setHandle('art_directed'),
        ]));
        BlueprintRepository::shouldReceive('in')->with('collections/events')->andReturn(collect([
            'event' => $event->setHandle('event'),
        ]));
        BlueprintRepository::shouldReceive('in')->with('collections/food')->andReturn(collect([
            'food' => $food->setHandle('food'),
        ]));

        Collection::make('blog')
            ->routes(['en' => '/blog/{slug}'])
            ->dated(true)
            ->futureDateBehavior('private')
            ->save();
        Collection::make('events')->routes(['en' => '/events/{slug}'])->dated(true)->save();
        Collection::make('food')->routes(['en' => '/food/{slug}'])->save();

        EntryFactory::collection('blog')
            ->id('1')
            ->date(now()->subMonths(2))
            ->slug('standard-blog-post')
            ->data([
                'title' => 'Standard Blog Post',
                'intro' => 'The intro',
                'content' => 'The standard blog post content',
            ])->create();

        EntryFactory::collection('blog')
            ->id('2')
            ->date(now()->subMonths(3))
            ->slug('art-directed-blog-post')
            ->data([
                'blueprint' => 'art_directed',
                'title' => 'Art Directed Blog Post',
                'hero_image' => 'hero.jpg',
                'content' => 'The art directed blog post content',
            ])->create();

        EntryFactory::collection('events')
            ->id('3')
            ->slug('event-one')
            ->date('2017-11-03')
            ->data([
                'title' => 'Event One',
                'updated_at' => '1514208540',
            ])
            ->create();

        EntryFactory::collection('events')->id('4')->slug('event-two')->data(['title' => 'Event Two'])->create();

        EntryFactory::collection('food')->id('5')->slug('hamburger')->data([
            'title' => 'Hamburger',
            'calories' => 350,
        ])->create();
    }
}
