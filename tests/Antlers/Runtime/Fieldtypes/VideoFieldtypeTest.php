<?php

namespace Tests\Antlers\Runtime\Fieldtypes;

use Tests\Antlers\ParserTestCase;

class VideoFieldtypeTest extends ParserTestCase
{
    public function test_render_video_fieldtype()
    {
        $this->runFieldTypeTest('video');
    }
}
