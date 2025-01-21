<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('graphql')]
class VideoFieldtypeTest extends FieldtypeTestCase
{
    #[Test]
    public function it_gets_the_video_url()
    {
        $this->createEntryWithFields([
            'filled' => [
                'value' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'field' => ['type' => 'video'],
            ],
            'undefined' => [
                'value' => null,
                'field' => ['type' => 'video'],
            ],
        ]);

        $this->assertGqlEntryHas('filled, undefined', [
            'filled' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'undefined' => null,
        ]);
    }
}
