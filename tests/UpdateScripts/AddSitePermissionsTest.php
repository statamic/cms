<?php

namespace Tests\UpdateScripts;

use Statamic\Facades\Role;
use Statamic\Facades\Site;
use Statamic\UpdateScripts\AddSitePermissions;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;
use Tests\UpdateScripts\Concerns\RunsUpdateScripts;

class AddSitePermissionsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk, RunsUpdateScripts;

    /** @test */
    public function it_is_registered()
    {
        $this->assertUpdateScriptRegistered(AddSitePermissions::class);
    }

    /** @test */
    public function it_can_add_site_permissions()
    {
        Site::setConfig(['sites' => [
            'first' => ['name' => 'First Site', 'locale' => 'en_US', 'url' => '/'],
            'second' => ['name' => 'Second Site', 'locale' => 'en_US', 'url' => '/second'],
        ]]);

        Role::make()
            ->title('Webmaster')
            ->handle('webmaster')
            ->permissions('super')
            ->save();

        Role::make()
            ->title('Author')
            ->handle('author')
            ->permissions([
                'access cp',
                'create blog entries',
                'edit blog entries',
                'create news entries',
                'edit news entries',
            ])
            ->save();

        Role::make()
            ->title('Blog Admin')
            ->handle('blog_admin')
            ->permissions([
                'access cp',
                'create blog entries',
                'edit blog entries',
                'publish blog entries',
                'delete blog entries',
                'reorder blog entries',
            ])
            ->save();

        $this->runUpdateScript(AddSitePermissions::class);

        $expectedAuthor = [
            'access cp',
            'create blog entries',
            'edit blog entries',
            'create news entries',
            'edit news entries',
            'access first site', // New permission
            'access second site', // New permission
        ];

        $expectedBlogAdmin = [
            'access cp',
            'create blog entries',
            'edit blog entries',
            'publish blog entries',
            'delete blog entries',
            'reorder blog entries',
            'access first site', // New permission
            'access second site', // New permission
        ];

        $this->assertTrue(Role::find('webmaster')->isSuper());
        $this->assertEquals($expectedAuthor, Role::find('author')->permissions()->all());
        $this->assertEquals($expectedBlogAdmin, Role::find('blog_admin')->permissions()->all());

        Role::all()->each->delete(); // Clean up
    }
}
