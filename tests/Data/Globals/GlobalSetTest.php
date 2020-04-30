<?php

namespace Tests\Data\Globals;

use Statamic\Facades\Site;
use Statamic\Globals\GlobalSet;
use Tests\TestCase;

class GlobalSetTest extends TestCase
{
    /** @test */
    public function it_gets_file_contents_for_saving_with_a_single_site()
    {
        Site::setConfig([
            'default' => 'en',
            'sites' => [
                'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
            ],
        ]);

        $set = (new GlobalSet)->title('The title');

        $variables = $set->makeLocalization('en')->data([
            'array' => ['first one', 'second one'],
            'string' => 'The string',
        ]);

        $set->addLocalization($variables);

        $expected = <<<'EOT'
title: 'The title'
data:
  array:
    - 'first one'
    - 'second one'
  string: 'The string'

EOT;
        $this->assertEquals($expected, $set->fileContents());
    }

    /** @test */
    public function it_gets_file_contents_for_saving_with_multiple_sites()
    {
        Site::setConfig([
            'default' => 'en',
            'sites' => [
                'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
                'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => 'http://fr.test.com/'],
                'de' => ['name' => 'German', 'locale' => 'de_DE', 'url' => 'http://test.com/de/'],
            ],
        ]);

        $set = (new GlobalSet)->title('The title');

        // We set the data but it's basically irrelevant since it won't get saved to this file.
        $set->in('en', function ($loc) {
            $loc->data([
                'array' => ['first one', 'second one'],
                'string' => 'The string',
            ]);
        });
        $set->in('fr', function ($loc) {
            $loc->data([
                'array' => ['le first one', 'le second one'],
                'string' => 'Le string',
            ]);
        });

        $expected = <<<'EOT'
title: 'The title'

EOT;
        $this->assertEquals($expected, $set->fileContents());
    }
}
