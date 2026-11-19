<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Inertia\Inertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InertiaRootViewTest extends TestCase
{
    #[Test]
    public function it_does_not_set_the_inertia_root_view_globally()
    {
        $response = Inertia::render('Test')->toResponse(Request::create('/'));

        $this->assertEquals('app', $response->getOriginalContent()->name());
    }
}
