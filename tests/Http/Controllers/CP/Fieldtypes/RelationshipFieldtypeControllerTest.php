<?php

namespace Tests\Http\Controllers\CP\Fieldtypes;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use Statamic\Fieldtypes\Relationship;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class RelationshipFieldtypeControllerTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        Facades\Collection::make('blog')->save();
        EntryFactory::id('123')->collection('blog')->slug('one')->data(['title' => 'One'])->create();
    }

    private function request($config, $selections = ['123'])
    {
        return $this
            ->actingAs(Facades\User::make()->makeSuper())
            ->post(cp_route('relationship.data'), [
                // JSON.stringify() in the browser leaves multibyte characters as-is,
                // so don't let PHP escape them to \uXXXX sequences here.
                'config' => base64_encode(json_encode($config, JSON_UNESCAPED_UNICODE)),
                'selections' => $selections,
            ]);
    }

    #[Test]
    public function it_gets_item_data_for_the_configured_fieldtype()
    {
        $this->request(['type' => 'entries', 'collections' => ['blog']])
            ->assertOk()
            ->assertJsonPath('data.0.title', 'One');
    }

    #[Test]
    public function it_doesnt_mangle_multibyte_characters_in_the_config()
    {
        // The base64-encoded JSON config used to be run through mb_convert_encoding()
        // with a detection list, which could misdetect perfectly valid UTF-8 as another
        // encoding and corrupt it. Emoji were mangled into Cyrillic. See #566.
        (new class extends Relationship
        {
            public static function handle()
            {
                return 'config_echo';
            }

            protected function toItemArray($id)
            {
                return ['id' => $id, 'title' => $this->config('display')];
            }

            public function getIndexItems($request)
            {
                return collect();
            }
        })::register();

        $display = '😀👍';

        $this->request(['type' => 'config_echo', 'display' => $display])
            ->assertOk()
            ->assertJsonPath('data.0.title', $display);
    }
}
