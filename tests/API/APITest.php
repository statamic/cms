<?php

namespace Tests\API;

use Tests\TestCase;

class APITest extends TestCase
{
    /** @test */
    function not_found_responses_are_formatted_with_json()
    {
        $this
            ->get('/api/blah')
            ->assertNotFound()
            ->assertJson(['message' => 'Not found.']);
    }
}
