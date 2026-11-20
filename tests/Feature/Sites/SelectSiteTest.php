<?php

namespace Tests\Feature\Sites;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class SelectSiteTest extends TestCase
{
    use FakesRoles, PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en'],
            'fr' => ['url' => '/fr/', 'locale' => 'fr'],
        ]);

        $this->setTestRoles([
            'can_access_fr' => [
                'access cp',
                'access en site',
                'access fr site',
            ],
            'cant_access_fr' => [
                'access cp',
                'access en site',
            ],
        ]);
    }

    #[Test]
    public function site_can_be_selected()
    {
        $this->assertEquals('en', Site::selected()->handle());

        $this
            ->actingAs(tap(User::make()->assignRole('can_access_fr'))->save())
            ->from('/original')
            ->get('cp/select-site/fr')
            ->assertRedirect('/original')
            ->assertSessionHas('success', 'Site selected.');

        $this->assertEquals('fr', Site::selected()->handle());
    }

    /** @see https://github.com/statamic/cms/issues/11557 */
    #[Test]
    public function site_filter_in_the_previous_url_is_switched_to_the_selected_site()
    {
        $filters = base64_encode(json_encode(['site' => ['site' => 'en'], 'status' => ['status' => 'published']]));
        $expectedFilters = base64_encode(json_encode(['site' => ['site' => 'fr'], 'status' => ['status' => 'published']]));

        $this
            ->actingAs(tap(User::make()->assignRole('can_access_fr'))->save())
            ->from('/original?page=2&filters='.urlencode($filters))
            ->get('cp/select-site/fr')
            ->assertRedirect('/original?page=2&filters='.urlencode($expectedFilters));

        $this->assertEquals('fr', Site::selected()->handle());
    }

    #[Test]
    public function invalid_site_cannot_be_selected()
    {
        $this->assertEquals('en', Site::selected()->handle());

        $this
            ->actingAs(tap(User::make()->assignRole('can_access_fr'))->save())
            ->from('/original')
            ->get('cp/select-site/invalid')
            ->assertRedirect('/original')
            ->assertSessionHas('error', 'Invalid site.');

        $this->assertEquals('en', Site::selected()->handle());
    }

    #[Test]
    public function site_cannot_be_selected_without_permission()
    {
        $this->assertEquals('en', Site::selected()->handle());

        $this
            ->actingAs(tap(User::make()->assignRole('cant_access_fr'))->save())
            ->from('/original')
            ->get('cp/select-site/fr')
            ->assertRedirect('/original')
            ->assertSessionHas('error', 'This action is unauthorized.');

        $this->assertEquals('en', Site::selected()->handle());
    }
}
