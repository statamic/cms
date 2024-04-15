<?php

namespace Tests\View;

use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\FakesContent;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class StateTest extends TestCase
{
    use FakesContent,
        FakesViews,
        PreventSavingStacheItemsToDisk;

    public function test_tag_state_is_cleared_between_responses()
    {
        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');

        $template = <<< 'EOT'
{{ title }}
{{ loop from="1" to="5" }}<{{ switch between="one|two" }}>{{ /loop }}
<{{ increment:test }}>
EOT;

        $this->viewShouldReturnRaw('default', $template);

        $this->createPage('home', [
            'with' => [
                'title' => 'Home Page',
                'content' => 'This is the home page.',
                'template' => 'default',
            ],
        ]);

        $resultOne = $this->getResponseContent('/home');
        $resultTwo = $this->getResponseContent('/home');
        $resultThree = $this->getResponseContent('/home');

        $expected = <<<'EOT'
Home Page
<one><two><one><two><one>
<0>
EOT;

        $this->assertSame($expected, $resultOne);
        $this->assertSame($expected, $resultTwo);
        $this->assertSame($expected, $resultThree);
    }

    protected function getResponseContent($uri)
    {
        $response = $this->get($uri)->assertStatus(200);

        return StringUtilities::normalizeLineEndings(trim($response->getContent()));
    }
}
