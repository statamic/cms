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
