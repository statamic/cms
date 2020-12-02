<?php

namespace Tests\Feature\GraphQL;

use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/** @group graphql */
class GraphQLTestCase extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        TestResponse::macro('assertGqlData', function ($data) {
            $this->assertExactJson(['data' => $data]);
        });
    }
}
