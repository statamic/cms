<?php

namespace Tests\Feature\Assets;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\TestCase;

class UpdateAssetTest extends TestCase
{
    use FakesRoles;

    #[Test]
    public function it_404s_when_the_asset_doesnt_exist_even_when_a_site_is_provided()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit test assets']]);
        $user = User::make()->assignRole('test')->save();

        $this
            ->actingAs($user)
            ->patchJson('/cp/assets/'.base64_encode('test::unknown.txt').'?site='.Site::default()->handle(), [])
            ->assertNotFound();
    }
}
