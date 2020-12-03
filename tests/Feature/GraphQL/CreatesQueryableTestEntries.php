<?php

namespace Tests\Feature\GraphQL;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Blueprint;

trait CreatesQueryableTestEntries
{
    public function createEntries()
    {
        EntryFactory::collection('blog')->id('1')->data([
            'title' => 'Standard Blog Post',
            'intro' => 'The intro',
            'content' => 'The standard blog post content',
        ])->create();

        EntryFactory::collection('blog')->id('2')->data([
            'blueprint' => 'art_directed',
            'title' => 'Art Directed Blog Post',
            'hero_image' => 'hero.jpg',
            'content' => 'The art directed blog post content',
        ])->create();

        EntryFactory::collection('events')->id('3')->data(['title' => 'Event One'])->create();

        EntryFactory::collection('events')->id('4')->data(['title' => 'Event Two'])->create();

        EntryFactory::collection('food')->id('5')->data([
            'title' => 'Hamburger',
            'calories' => 350,
        ])->create();

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
    }
}
