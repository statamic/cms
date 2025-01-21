<?php

namespace Tests\Feature\Entries;

use Facades\Statamic\CP\LivePreview;
use Facades\Tests\Factories\EntryFactory;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Tokens\Token;
use Statamic\Facades\Collection;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class PreviewEntryTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_creates_a_token_with_entry_for_creation()
    {
        Collection::make('blog')->routes('/blog/{slug}')->save();

        $token = Mockery::mock(Token::class);
        $token->shouldReceive('token')->andReturn('test-token');

        LivePreview::shouldReceive('tokenize')->withArgs(function ($token, $entry) {
            return $token === null
                && $entry->id() === null
                && $entry->collectionHandle() === 'blog'
                && $entry->title === 'The new entry'
                && $entry->live_preview === ['foo' => 'bar'];
        })->andReturn($token);

        $response = $this
            ->actingAs($this->user())
            ->postJson('/cp/collections/blog/entries/create/en/preview', [
                'preview' => [
                    'title' => 'The new entry',
                    'slug' => 'the-new-entry',
                ],
                'extras' => [
                    'foo' => 'bar',
                ],
            ])
            ->assertOk()
            ->assertJsonStructure(['token', 'url'])
            ->assertJsonPath('token', 'test-token');

        $this->assertMatchesRegularExpression(
            '/^http:\/\/localhost\/blog\/the-new-entry\?live-preview=\w{16}&token=test-token$/',
            $response['url']
        );
    }

    #[Test]
    public function it_creates_a_token_with_entry_for_editing()
    {
        Collection::make('blog')->routes('/blog/{slug}')->save();

        EntryFactory::id('the-entry')
            ->collection('blog')
            ->slug('the-existing-entry')
            ->data(['title' => 'The Existing Entry'])
            ->create();

        $token = Mockery::mock(Token::class);
        $token->shouldReceive('token')->andReturn('test-token');

        LivePreview::shouldReceive('tokenize')->withArgs(function ($token, $entry) {
            return $token === null
                && $entry->id() === 'the-entry'
                && $entry->title === 'Edited title'
                && $entry->live_preview === ['foo' => 'bar'];
        })->andReturn($token);

        $response = $this
            ->actingAs($this->user())
            ->postJson('/cp/collections/blog/entries/the-entry/preview', [
                'preview' => [
                    'title' => 'Edited title',
                    'slug' => 'edited-slug',
                ],
                'extras' => [
                    'foo' => 'bar',
                ],
            ])
            ->assertOk()
            ->assertJsonStructure(['token', 'url'])
            ->assertJsonPath('token', 'test-token');

        $this->assertMatchesRegularExpression(
            '/^http:\/\/localhost\/blog\/the-existing-entry\?live-preview=\w{16}&token=test-token$/',
            $response['url']
        );
    }

    #[Test]
    public function it_updates_existing_token_with_entry_for_editing()
    {
        Collection::make('blog')->routes('/blog/{slug}')->save();

        EntryFactory::id('the-entry')
            ->collection('blog')
            ->slug('the-existing-entry')
            ->data(['title' => 'The Existing Entry'])
            ->create();

        $token = Mockery::mock(Token::class);
        $token->shouldReceive('token')->andReturn('existing-token');

        LivePreview::shouldReceive('tokenize')->withArgs(function ($token, $entry) {
            return $token === 'existing-token'
                && $entry->id() === 'the-entry'
                && $entry->title === 'Edited title'
                && $entry->live_preview === ['foo' => 'bar'];
        })->andReturn($token);

        $response = $this
            ->actingAs($this->user())
            ->postJson('/cp/collections/blog/entries/the-entry/preview', [
                'token' => 'existing-token',
                'preview' => [
                    'title' => 'Edited title',
                    'slug' => 'edited-slug',
                ],
                'extras' => [
                    'foo' => 'bar',
                ],
            ])
            ->assertOk()
            ->assertJsonStructure(['token', 'url'])
            ->assertJsonPath('token', 'existing-token');

        $this->assertMatchesRegularExpression(
            '/^http:\/\/localhost\/blog\/the-existing-entry\?live-preview=\w{16}&token=existing-token$/',
            $response['url']
        );
    }

    #[Test]
    public function it_sets_live_preview_to_true_if_theres_no_additional_data()
    {
        Collection::make('blog')->routes('/blog/{slug}')->save();

        EntryFactory::id('the-entry')
            ->collection('blog')
            ->slug('the-existing-entry')
            ->data(['title' => 'The Existing Entry'])
            ->create();

        $token = Mockery::mock(Token::class);
        $token->shouldReceive('token')->andReturn('existing-token');

        LivePreview::shouldReceive('tokenize')->withArgs(function ($token, $entry) {
            return $token === 'existing-token'
                && $entry->id() === 'the-entry'
                && $entry->title === 'Edited title'
                && $entry->live_preview === true;
        })->andReturn($token);

        $response = $this
            ->actingAs($this->user())
            ->postJson('/cp/collections/blog/entries/the-entry/preview', [
                'token' => 'existing-token',
                'preview' => [
                    'title' => 'Edited title',
                    'slug' => 'edited-slug',
                ],
                'extras' => [],
            ])
            ->assertOk()
            ->assertJsonStructure(['token', 'url'])
            ->assertJsonPath('token', 'existing-token');

        $this->assertMatchesRegularExpression(
            '/^http:\/\/localhost\/blog\/the-existing-entry\?live-preview=\w{16}&token=existing-token$/',
            $response['url']
        );
    }

    private function user()
    {
        $this->setTestRoles(['test' => ['access cp', 'create blog entries', 'edit blog entries']]);

        return User::make()->assignRole('test')->save();
    }
}
