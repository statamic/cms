<?php

namespace Tests\Feature\Entries;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Token;
use Statamic\Tokens\Handlers\SharedPreview;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ViewSharedPreviewTest extends TestCase
{
    use FakesViews;
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        $this->withStandardFakeViews();
        $this->withStandardBlueprints();
        $this->viewShouldReturnRaw('layout', '<html><body>{{ template_content }}</body></html>');

        Carbon::setTestNow(Carbon::parse('2024-01-01 12:00:00'));
    }

    #[Test]
    public function unpublished_drafts_are_visible_with_a_shared_preview_token()
    {
        Collection::make('blog')->routes('/blog/{slug}')->save();

        $entry = EntryFactory::id('1')
            ->collection('blog')
            ->slug('about')
            ->published(false)
            ->data(['content' => 'Draft content'])
            ->create();

        $token = $this->tokenFor($entry);

        $this
            ->get('/blog/about?token='.$token->token())
            ->assertOk()
            ->assertHeader('X-Statamic-Draft', true)
            ->assertHeader('X-Statamic-Shared-Preview', true)
            ->assertHeader('X-Robots-Tag', 'noindex')
            ->assertSee('Draft content')
            ->assertSee('Draft preview');
    }

    #[Test]
    public function the_shared_preview_banner_can_be_disabled()
    {
        config(['statamic.live_preview.shared_link_banner' => false]);

        Collection::make('blog')->routes('/blog/{slug}')->save();

        $entry = EntryFactory::id('1')
            ->collection('blog')
            ->slug('about')
            ->published(false)
            ->data(['content' => 'Draft content'])
            ->create();

        $token = $this->tokenFor($entry);

        $this
            ->get('/blog/about?token='.$token->token())
            ->assertOk()
            ->assertSee('Draft content')
            ->assertDontSee('Draft preview');
    }

    #[Test]
    public function unpublished_drafts_are_not_visible_without_a_token()
    {
        $this->withStandardFakeErrorViews();

        Collection::make('blog')->routes('/blog/{slug}')->save();

        EntryFactory::id('1')
            ->collection('blog')
            ->slug('about')
            ->published(false)
            ->data(['content' => 'Draft content'])
            ->create();

        $this->get('/blog/about')->assertNotFound();
    }

    #[Test]
    public function tokens_for_a_different_entry_do_not_unlock_drafts()
    {
        $this->withStandardFakeErrorViews();

        Collection::make('blog')->routes('/blog/{slug}')->save();

        EntryFactory::id('1')
            ->collection('blog')
            ->slug('about')
            ->published(false)
            ->data(['content' => 'Draft content'])
            ->create();

        $other = EntryFactory::id('2')
            ->collection('blog')
            ->slug('other')
            ->published(false)
            ->create();

        $token = $this->tokenFor($other);

        $this
            ->get('/blog/about?token='.$token->token())
            ->assertNotFound();
    }

    #[Test]
    public function expired_tokens_do_not_unlock_drafts()
    {
        $this->withStandardFakeErrorViews();

        Collection::make('blog')->routes('/blog/{slug}')->save();

        $entry = EntryFactory::id('1')
            ->collection('blog')
            ->slug('about')
            ->published(false)
            ->data(['content' => 'Draft content'])
            ->create();

        $token = $this->tokenFor($entry, Carbon::now()->subMinute());

        $this
            ->get('/blog/about?token='.$token->token())
            ->assertNotFound();
    }

    #[Test]
    public function future_private_entries_are_visible_with_a_shared_preview_token()
    {
        Collection::make('blog')
            ->routes('/blog/{slug}')
            ->dated(true)
            ->futureDateBehavior('private')
            ->save();

        $entry = EntryFactory::id('1')
            ->collection('blog')
            ->slug('about')
            ->date('2024-01-02')
            ->published(true)
            ->data(['content' => 'Scheduled content'])
            ->create();

        $token = $this->tokenFor($entry);

        $this
            ->get('/blog/about?token='.$token->token())
            ->assertOk()
            ->assertHeader('X-Statamic-Private', true)
            ->assertHeader('X-Statamic-Shared-Preview', true)
            ->assertSee('Scheduled content');
    }

    #[Test]
    public function working_copy_content_is_served_when_revisions_are_enabled()
    {
        config(['statamic.revisions.enabled' => true]);

        Collection::make('blog')
            ->routes('/blog/{slug}')
            ->revisionsEnabled(true)
            ->save();

        $entry = EntryFactory::id('1')
            ->collection('blog')
            ->slug('about')
            ->published(true)
            ->data(['content' => 'Published content'])
            ->create();

        tap($entry->makeWorkingCopy(), function ($copy) {
            $attrs = $copy->attributes();
            $attrs['data']['content'] = 'Working copy content';
            $copy->attributes($attrs);
        })->save();

        $token = $this->tokenFor($entry);

        $this
            ->get('/blog/about?token='.$token->token())
            ->assertOk()
            ->assertHeader('X-Statamic-Shared-Preview', true)
            ->assertSee('Working copy content')
            ->assertDontSee('Published content');

        $entry->deleteWorkingCopy();
    }

    #[Test]
    public function published_entries_render_normally_with_a_token()
    {
        Collection::make('blog')->routes('/blog/{slug}')->save();

        $entry = EntryFactory::id('1')
            ->collection('blog')
            ->slug('about')
            ->published(true)
            ->data(['content' => 'Live content'])
            ->create();

        $token = $this->tokenFor($entry);

        $this
            ->get('/blog/about?token='.$token->token())
            ->assertOk()
            ->assertHeader('X-Statamic-Shared-Preview', true)
            ->assertHeader('X-Robots-Tag', 'noindex')
            ->assertHeaderMissing('X-Statamic-Draft')
            ->assertSee('Live content')
            ->assertDontSee('Draft preview');
    }

    #[Test]
    public function pinned_revisions_are_served_instead_of_current_content()
    {
        config(['statamic.revisions.enabled' => true]);

        Collection::make('blog')
            ->routes('/blog/{slug}')
            ->revisionsEnabled(true)
            ->save();

        $entry = EntryFactory::id('1')
            ->collection('blog')
            ->slug('about')
            ->published(true)
            ->data(['content' => 'Published content'])
            ->create();

        $revision = tap($entry->makeRevision(), function ($revision) {
            $attrs = $revision->attributes();
            $attrs['data']['content'] = 'Pinned revision content';
            $revision->attributes($attrs)->date(Carbon::parse('2023-12-01 09:00:00'));
        });
        $revision->save();

        $token = $this->tokenFor($entry, revision: $revision->date()->timestamp);

        $this
            ->get('/blog/about?token='.$token->token())
            ->assertOk()
            ->assertSee('Pinned revision content')
            ->assertDontSee('Published content')
            ->assertSee('Revision preview');
    }

    #[Test]
    public function missing_pinned_revisions_404()
    {
        $this->withStandardFakeErrorViews();

        config(['statamic.revisions.enabled' => true]);

        Collection::make('blog')
            ->routes('/blog/{slug}')
            ->revisionsEnabled(true)
            ->save();

        $entry = EntryFactory::id('1')
            ->collection('blog')
            ->slug('about')
            ->published(true)
            ->data(['content' => 'Published content'])
            ->create();

        $token = $this->tokenFor($entry, revision: 1234567890);

        $this
            ->get('/blog/about?token='.$token->token())
            ->assertNotFound();
    }

    #[Test]
    public function shared_preview_bypasses_protect_schemes()
    {
        Collection::make('blog')->routes('/blog/{slug}')->save();

        $entry = EntryFactory::id('1')
            ->collection('blog')
            ->slug('about')
            ->published(true)
            ->data(['content' => 'Protected content', 'protect' => 'logged_in'])
            ->create();

        $this->get('/blog/about')->assertRedirect('/login?redirect=http://localhost/blog/about');

        $token = $this->tokenFor($entry);

        $this
            ->get('/blog/about?token='.$token->token())
            ->assertOk()
            ->assertSee('Protected content');
    }

    #[Test]
    public function deleted_entries_404_even_with_a_token()
    {
        $this->withStandardFakeErrorViews();

        Collection::make('blog')->routes('/blog/{slug}')->save();

        $entry = EntryFactory::id('1')
            ->collection('blog')
            ->slug('about')
            ->published(false)
            ->data(['content' => 'Draft content'])
            ->create();

        $token = $this->tokenFor($entry);

        $entry->delete();

        $this
            ->get('/blog/about?token='.$token->token())
            ->assertNotFound();
    }

    private function tokenFor($entry, ?Carbon $expiresAt = null, $revision = null)
    {
        $data = ['reference' => $entry->reference()];

        if ($revision) {
            $data['revision'] = $revision;
        }

        $token = Token::make(null, SharedPreview::class, $data)
            ->expireAt($expiresAt ?? Carbon::now()->addDay());

        $token->save();

        return $token;
    }

    private function withStandardBlueprints()
    {
        Blueprint::shouldReceive('in')->withAnyArgs()->andReturn(collect([new \Statamic\Fields\Blueprint]));
    }
}
