<?php

namespace Tests\Search\Searchables;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
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

        User::make()->email('alfa@test.com')->save();
        User::make()->email('bravo@test.com')->save();

        $provider = $this->makeProvider($locale, $config);

        // Check if it provides the expected users.
        $this->assertEquals($expected, $provider->provide()->map->email()->all());

        // Check if the users are contained by the provider or not.
        foreach (User::all() as $user) {
            $this->assertEquals(
                $shouldBeIn = in_array($user->email(), $expected),
                $provider->contains($user),
                "User {$user->email()} should ".($shouldBeIn ? '' : 'not ').'be contained in the provider.'
            );
        }
    }

    public static function usersProvider()
    {
        return [
            'all' => [
                null,
                ['searchables' => 'all'],
                ['alfa@test.com', 'bravo@test.com'],
            ],
            'all users' => [
                null,
                ['searchables' => ['users']],
                ['alfa@test.com', 'bravo@test.com'],
            ],

            'all, english' => [
                'en',
                ['searchables' => 'all'],
                ['alfa@test.com', 'bravo@test.com'],
            ],
            'all users, english' => [
                'en',
                ['searchables' => ['users']],
                ['alfa@test.com', 'bravo@test.com'],
            ],

            'all, french' => [
                'fr',
                ['searchables' => 'all'],
                ['alfa@test.com', 'bravo@test.com'],
            ],
            'all users, french' => [
                'fr',
                ['searchables' => ['users']],
                ['alfa@test.com', 'bravo@test.com'],
            ],
        ];
    }

    #[Test]
    #[DataProvider('indexFilterProvider')]
    public function it_can_use_a_custom_filter($filter)
    {
        $a = tap(User::make()->email('a@test.com'))->save();
        $b = tap(User::make()->email('b@test.com')->set('is_searchable', false))->save();
        $c = tap(User::make()->email('c@test.com')->set('is_searchable', true))->save();
        $d = tap(User::make()->email('d@test.com'))->save();

        $provider = $this->makeProvider(null, [
            'searchables' => 'all',
            'filter' => $filter,
        ]);

        $this->assertEquals(['a@test.com', 'c@test.com', 'd@test.com'], $provider->provide()->map->email()->all());

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
        return collect($keys === 'all' ? ['*'] : $keys)
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
