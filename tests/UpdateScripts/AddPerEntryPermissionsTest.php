<?php

namespace Tests\UpdateScripts;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Role;
use Statamic\UpdateScripts\AddPerEntryPermissions;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;
use Tests\UpdateScripts\Concerns\RunsUpdateScripts;

class AddPerEntryPermissionsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk, RunsUpdateScripts;

    #[Test]
    public function it_can_add_new_per_entry_permissions()
    {
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

        $this->runUpdateScript(AddPerEntryPermissions::class);

        $expectedAuthor = [
            'access cp',
            'create blog entries',
            'edit blog entries',
            'create news entries',
            'edit news entries',
            'edit other authors blog entries', // New permission
            'edit other authors news entries', // New permission
        ];

        $expectedBlogAdmin = [
            'access cp',
            'create blog entries',
            'edit blog entries',
            'publish blog entries',
            'delete blog entries',
            'reorder blog entries',
            'edit other authors blog entries', // New permission
            'publish other authors blog entries', // New permission
            'delete other authors blog entries', // New permission
        ];

        $this->assertTrue(Role::find('webmaster')->isSuper());
        $this->assertEquals($expectedAuthor, Role::find('author')->permissions()->all());
        $this->assertEquals($expectedBlogAdmin, Role::find('blog_admin')->permissions()->all());

        Role::all()->each->delete(); // Clean up
    }
}
