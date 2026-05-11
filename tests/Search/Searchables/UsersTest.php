<?php

namespace Tests\Search\Searchables;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Statamic\Query\Scopes\Scope;
use Statamic\Search\Searchables\Users;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UsersTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    #[DataProvider('usersProvider')]
    public function it_gets_users($locale, $config, $expected)
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en'],
            'fr' => ['url' => '/fr/', 'locale' => 'fr'],
        ]);

        User::make()->id('alfa')->email('alfa@test.com')->save();
        User::make()->id('bravo')->email('bravo@test.com')->save();

        $provider = $this->makeProvider($locale, $config);

        // Check if it provides the expected users.
        $this->assertEquals($expected, $provider->provide()->all());

        // Check if the users are contained by the provider or not.
        foreach (User::all() as $user) {
            $this->assertEquals(
                $shouldBeIn = in_array($user->reference(), $expected),
                $provider->contains($user),
                "User {$user->email()} should ".($shouldBeIn ? '' : 'not ').'be contained in the provider.'
            );
        }
    }

    public static function usersProvider()
    {
        return [
            'content' => [
                null,
                ['searchables' => 'content'],
                ['user::alfa', 'user::bravo'],
            ],
            'all users' => [
                null,
                ['searchables' => ['users']],
                ['user::alfa', 'user::bravo'],
            ],

            'content, english' => [
                'en',
                ['searchables' => 'content'],
                ['user::alfa', 'user::bravo'],
            ],
            'all users, english' => [
                'en',
                ['searchables' => ['users']],
                ['user::alfa', 'user::bravo'],
            ],

            'content, french' => [
                'fr',
                ['searchables' => 'content'],
                ['user::alfa', 'user::bravo'],
            ],
            'all users, french' => [
                'fr',
                ['searchables' => ['users']],
                ['user::alfa', 'user::bravo'],
            ],
        ];
    }

    #[Test]
    #[DataProvider('indexFilterProvider')]
    public function it_can_use_a_custom_filter($filter)
    {
        $a = tap(User::make()->id('a')->email('a@test.com'))->save();
        $b = tap(User::make()->id('b')->email('b@test.com')->set('is_searchable', false))->save();
        $c = tap(User::make()->id('c')->email('c@test.com')->set('is_searchable', true))->save();
        $d = tap(User::make()->id('d')->email('d@test.com'))->save();

        $provider = $this->makeProvider(null, [
            'searchables' => 'content',
            'filter' => $filter,
        ]);

        $this->assertEquals(
            ['user::a', 'user::c', 'user::d'],
            $provider->provide()->all()
        );

        $this->assertTrue($provider->contains($a));
        $this->assertFalse($provider->contains($b));
        $this->assertTrue($provider->contains($c));
        $this->assertTrue($provider->contains($d));
    }

    public static function indexFilterProvider()
    {
        return [
            'class' => [TestSearchableUsersFilter::class],
            'closure' => [
                function ($entry) {
                    return $entry->get('is_searchable') !== false;
                },
            ],
        ];
    }

    #[Test]
    public function it_can_use_a_query_scope()
    {
        CustomUsersScope::register();

        $a = tap(User::make()->id('a')->email('a@test.com'))->save();
        $b = tap(User::make()->id('b')->email('b@test.com')->set('is_searchable', false))->save();
        $c = tap(User::make()->id('c')->email('c@test.com')->set('is_searchable', true))->save();
        $d = tap(User::make()->id('d')->email('d@test.com'))->save();

        $provider = $this->makeProvider(null, [
            'searchables' => 'all',
            'query_scope' => 'custom_users_scope',
        ]);

        $this->assertEquals(
            ['user::a', 'user::c', 'user::d'],
            $provider->provide()->all()
        );

        $this->assertTrue($provider->contains($a));
        $this->assertFalse($provider->contains($b));
        $this->assertTrue($provider->contains($c));
        $this->assertTrue($provider->contains($d));
    }

    private function makeProvider($locale, $config)
    {
        $index = $this->makeIndex($locale, $config);

        $keys = $this->normalizeSearchableKeys($config['searchables'] ?? null);

        return (new Users)->setIndex($index)->setKeys($keys);
    }

    private function makeIndex($locale, $config)
    {
        $index = $this->mock(\Statamic\Search\Index::class);

        $index->shouldReceive('config')->andReturn($config);
        $index->shouldReceive('locale')->andReturn($locale);

        return $index;
    }

    private function normalizeSearchableKeys($keys)
    {
        // a bit of duplicated implementation logic.
        // but it makes the test look more like the real thing.
        return collect($keys === 'content' ? ['*'] : $keys)
            ->map(fn ($key) => str_replace('users:', '', $key))
            ->all();
    }
}

class TestSearchableUsersFilter
{
    public function handle($item)
    {
        return $item->get('is_searchable') !== false;
    }
}

class CustomUsersScope extends Scope
{
    public function apply($query, $params)
    {
        $query->where('is_searchable', '!=', false);
    }
}
