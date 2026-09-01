<?php

namespace Tests\Feature\Entries;

use Facades\Statamic\Tokens\Generator;
use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Token;
use Statamic\Facades\User;
use Statamic\Tokens\Handlers\SharedPreview;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class SharedPreviewEntryTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2024-01-01 12:00:00'));
        optional(Token::find('test-shared-token'))->delete();
    }

    public function tearDown(): void
    {
        optional(Token::find('test-shared-token'))->delete();

        parent::tearDown();
    }

    #[Test]
    public function it_mints_a_shared_preview_token()
    {
        Collection::make('blog')->routes('/blog/{slug}')->save();

        $entry = EntryFactory::id('the-entry')
            ->collection('blog')
            ->slug('the-existing-entry')
            ->published(false)
            ->data(['title' => 'The Existing Entry'])
            ->create();

        Generator::shouldReceive('generate')->andReturn('test-shared-token');

        $this
            ->actingAs($this->user())
            ->postJson('/cp/collections/blog/entries/the-entry/shared-preview')
            ->assertOk()
            ->assertJsonPath('url', 'http://localhost/blog/the-existing-entry?token=test-shared-token')
            ->assertJsonPath('expires_in_hours', 24);

        $token = Token::find('test-shared-token');

        $this->assertNotNull($token);
        $this->assertEquals(SharedPreview::class, $token->handler());
        $this->assertEquals($entry->reference(), $token->get('reference'));
        $this->assertTrue($token->expiry()->eq(Carbon::now()->addMinutes(1440)));
    }

    #[Test]
    public function it_mints_a_token_with_a_configured_expiry()
    {
        config(['statamic.live_preview.shared_link_expiry' => 60]);

        Collection::make('blog')->routes('/blog/{slug}')->save();

        EntryFactory::id('the-entry')
            ->collection('blog')
            ->slug('the-existing-entry')
            ->published(false)
            ->create();

        Generator::shouldReceive('generate')->andReturn('test-shared-token');

        $this
            ->actingAs($this->user())
            ->postJson('/cp/collections/blog/entries/the-entry/shared-preview')
            ->assertOk()
            ->assertJsonPath('expires_in_hours', 1);

        $token = Token::find('test-shared-token');

        $this->assertTrue($token->expiry()->eq(Carbon::now()->addMinutes(60)));
    }

    #[Test]
    public function it_can_be_minted_with_view_permission()
    {
        Collection::make('blog')->routes('/blog/{slug}')->save();

        EntryFactory::id('the-entry')
            ->collection('blog')
            ->slug('the-existing-entry')
            ->published(false)
            ->create();

        $this->setTestRoles(['viewer' => ['access cp', 'view blog entries']]);
        $user = User::make()->assignRole('viewer')->save();

        Generator::shouldReceive('generate')->andReturn('test-shared-token');

        $this
            ->actingAs($user)
            ->postJson('/cp/collections/blog/entries/the-entry/shared-preview')
            ->assertOk()
            ->assertJsonPath('url', 'http://localhost/blog/the-existing-entry?token=test-shared-token');
    }

    #[Test]
    public function it_doesnt_mint_a_token_without_permission()
    {
        Collection::make('blog')->routes('/blog/{slug}')->save();

        EntryFactory::id('the-entry')
            ->collection('blog')
            ->slug('the-existing-entry')
            ->published(false)
            ->create();

        $this->setTestRoles(['viewer' => ['access cp']]);
        $user = User::make()->assignRole('viewer')->save();

        $this
            ->actingAs($user)
            ->postJson('/cp/collections/blog/entries/the-entry/shared-preview')
            ->assertForbidden();

        $this->assertNull(Token::find('test-shared-token'));
    }

    #[Test]
    public function it_reuses_an_unexpired_token()
    {
        Collection::make('blog')->routes('/blog/{slug}')->save();

        EntryFactory::id('the-entry')
            ->collection('blog')
            ->slug('the-existing-entry')
            ->published(false)
            ->create();

        Generator::shouldReceive('generate')->once()->andReturn('test-shared-token');

        $first = $this
            ->actingAs($this->user())
            ->postJson('/cp/collections/blog/entries/the-entry/shared-preview')
            ->assertOk()
            ->json();

        $second = $this
            ->actingAs($this->user())
            ->postJson('/cp/collections/blog/entries/the-entry/shared-preview')
            ->assertOk()
            ->json();

        $this->assertEquals($first['url'], $second['url']);
        $this->assertEquals(1, Token::all()->count());
    }

    #[Test]
    public function it_mints_a_pinned_revision_token()
    {
        config(['statamic.revisions.enabled' => true]);

        Collection::make('blog')->routes('/blog/{slug}')->revisionsEnabled(true)->save();

        $entry = EntryFactory::id('the-entry')
            ->collection('blog')
            ->slug('the-existing-entry')
            ->published(true)
            ->data(['title' => 'Live title'])
            ->create();

        $revision = tap($entry->makeRevision(), function ($revision) {
            $attrs = $revision->attributes();
            $attrs['data']['title'] = 'Revision title';
            $revision->attributes($attrs)->date(Carbon::parse('2023-12-01 09:00:00'));
        });
        $revision->save();

        Generator::shouldReceive('generate')->andReturn('test-shared-token');

        $this
            ->actingAs($this->user())
            ->postJson('/cp/collections/blog/entries/the-entry/shared-preview', [
                'revision' => $revision->date()->timestamp,
            ])
            ->assertOk()
            ->assertJsonPath('url', 'http://localhost/blog/the-existing-entry?token=test-shared-token');

        $token = Token::find('test-shared-token');

        $this->assertEquals($revision->date()->timestamp, $token->get('revision'));
    }

    #[Test]
    public function it_uses_a_preview_target_url()
    {
        Collection::make('blog')
            ->routes('/blog/{slug}')
            ->previewTargets([
                ['label' => 'Entry', 'format' => '{permalink}', 'refresh' => true],
                ['label' => 'Listing', 'format' => '/blog', 'refresh' => true],
            ])
            ->save();

        EntryFactory::id('the-entry')
            ->collection('blog')
            ->slug('the-existing-entry')
            ->published(false)
            ->create();

        Generator::shouldReceive('generate')->andReturn('test-shared-token');

        $this
            ->actingAs($this->user())
            ->postJson('/cp/collections/blog/entries/the-entry/shared-preview', [
                'target' => 1,
            ])
            ->assertOk()
            ->assertJsonPath('url', 'http://localhost/blog?token=test-shared-token');
    }

    #[Test]
    public function guests_cannot_mint_tokens()
    {
        Collection::make('blog')->routes('/blog/{slug}')->save();

        EntryFactory::id('the-entry')
            ->collection('blog')
            ->slug('the-existing-entry')
            ->published(false)
            ->create();

        $this
            ->postJson('/cp/collections/blog/entries/the-entry/shared-preview')
            ->assertUnauthorized();
    }

    private function user()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit blog entries']]);

        return User::make()->assignRole('test')->save();
    }
}
