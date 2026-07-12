<?php

namespace Tests\CP;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Data;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EditRedirectControllerTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_redirects_to_edit_page()
    {
        $item = Mockery::mock();
        $item->shouldReceive('editUrl')->once()->andReturn($targetUrl = '/somewhere');
        Data::shouldReceive('find')->with('123')->once()->andReturn($item);

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->get('/cp/edit/123')
            ->assertRedirect($targetUrl);
    }

    #[Test]
    public function it_404s_if_id_doesnt_exist()
    {
        Data::shouldReceive('find')->with('123')->once()->andReturnNull();

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->get('/cp/edit/123')
            ->assertNotFound();
    }
}
