<?php

namespace Tests\Feature\Navigation;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Statamic\Fields\FieldtypeRepository;
use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Nav;
use Statamic\Facades\User;
use Statamic\Fields\Fieldtype;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EditNavigationPageTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    private function request($nav, $id, $site = 'en')
    {
        $url = cp_route('navigation.pages.edit', [$nav->handle(), $id]);

        return $this->getJson($url.'?site='.$site);
    }

    private function mockTextFieldtype()
    {
        FieldtypeRepository::shouldReceive('find')->with('text')
            ->andReturn(new class extends Fieldtype
            {
                public function preProcess($value)
                {
                    if (! $value) {
                        return;
                    }

                    return $value.' (preprocessed)';
                }

                public function preload()
                {
                    return ['hello' => 'world'];
                }
            });
    }

    #[Test]
    public function it_gets_the_values_for_a_regular_nav_item()
    {
        $this->withoutExceptionHandling();
        $this->mockTextFieldtype();
        $user = tap(User::make()->makeSuper())->save();
        $nav = tap(Nav::make('test'))->save();
        $nav->makeTree('en', [
            [
                'id' => 'id7',
                'title' => 'The title',
                'url' => 'http://example.com',
                'data' => [
                    'foo' => 'bar',
                ],
            ],
        ])->save();
        $blueprint = Blueprint::makeFromFields(['foo' => ['type' => 'text']]);
        BlueprintRepository::partialMock();
        BlueprintRepository::shouldReceive('find')->with('navigation.test')->andReturn($blueprint);

        $this
            ->actingAs($user)
            ->request($nav, 'id7')
            ->assertJson([
                'values' => [
                    'foo' => 'bar (preprocessed)',
                    'title' => 'The title (preprocessed)',
                    'url' => 'http://example.com (preprocessed)',
                ],
                'meta' => [
                    'foo' => ['hello' => 'world'],
                    'title' => ['hello' => 'world'],
                    'url' => ['hello' => 'world'],
                ],
                'originValues' => null,
                'originMeta' => null,
                'localizedFields' => [
                    'foo',
                    'title',
                    'url',
                ],
                'syncableFields' => [],
            ]);
    }

    #[Test]
    public function it_gets_the_values_for_a_text_only_nav_item()
    {
        $this->withoutExceptionHandling();
        $this->mockTextFieldtype();
        $user = tap(User::make()->makeSuper())->save();
        $nav = tap(Nav::make('test'))->save();
        $nav->makeTree('en', [
            [
                'id' => 'id7',
                'url' => 'http://example.com',
                'data' => [
                    'foo' => 'bar',
                ],
            ],
        ])->save();
        $blueprint = Blueprint::makeFromFields(['foo' => ['type' => 'text']]);
        BlueprintRepository::partialMock();
        BlueprintRepository::shouldReceive('find')->with('navigation.test')->andReturn($blueprint);

        $this
            ->actingAs($user)
            ->request($nav, 'id7')
            ->assertJson([
                'values' => [
                    'title' => null,
                    'url' => 'http://example.com (preprocessed)',
                    'foo' => 'bar (preprocessed)',
                ],
                'meta' => [
                    'title' => ['hello' => 'world'],
                    'foo' => ['hello' => 'world'],
                    'url' => ['hello' => 'world'],
                ],
                'originValues' => null,
                'originMeta' => null,
                'localizedFields' => [
                    'foo',
                    'url',
                ],
                'syncableFields' => [],
            ]);
    }

    #[Test]
    public function it_gets_the_values_for_an_entry_nav_item()
    {
        $this->withoutExceptionHandling();
        $this->mockTextFieldtype();
        $user = tap(User::make()->makeSuper())->save();
        $nav = tap(Nav::make('test'))->save();
        $nav->makeTree('en', [
            [
                'id' => 'id7',
                'entry' => '123',
                'title' => 'The page title',
                'data' => [
                    'foo' => 'page foo',
                ],
            ],
        ])->save();

        $entryBlueprint = Blueprint::makeFromFields([
            'foo' => ['type' => 'text'],
            'bar' => ['type' => 'text'],
            'baz' => ['type' => 'text'],
            'qux' => ['type' => 'text'],
        ]);

        $navBlueprint = Blueprint::makeFromFields([
            'foo' => ['type' => 'text'],
            'bar' => ['type' => 'text'],
            'baz' => ['type' => 'text'],
        ]);

        BlueprintRepository::partialMock();
        BlueprintRepository::shouldReceive('find')->with('navigation.test')->andReturn($navBlueprint);
        BlueprintRepository::shouldReceive('in')->with('collections/articles')->andReturn(collect(['articles' => $entryBlueprint]));

        tap(Collection::make('articles')->cascade(['baz' => 'collection baz']))->save();

        EntryFactory::id('123')
            ->collection('articles')
            ->data([
                'title' => 'entry title',
                'foo' => 'entry foo',
                'bar' => 'entry bar',
                'qux' => 'entry qux',
            ])
            ->create();

        $this
            ->actingAs($user)
            ->request($nav, 'id7')
            ->assertJson([
                'values' => [
                    'title' => 'The page title (preprocessed)',
                    'foo' => 'page foo (preprocessed)',
                    'bar' => 'entry bar (preprocessed)',
                    'baz' => null,
                ],
                'meta' => [
                    'title' => ['hello' => 'world'],
                    'url' => ['hello' => 'world'],
                    'foo' => ['hello' => 'world'],
                    'bar' => ['hello' => 'world'],
                ],
                'originValues' => [
                    'title' => 'entry title (preprocessed)',
                ],
                'originMeta' => [
                    'title' => ['hello' => 'world'],
                ],
                'localizedFields' => [
                    'foo',
                    'title',
                ],
                'syncableFields' => [
                    'title',
                    'foo',
                    'bar',
                    'baz',
                ],
            ]);
    }
}
