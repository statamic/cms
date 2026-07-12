<?php

namespace Tests\UpdateScripts;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\File;
use Statamic\UpdateScripts\UseClassBasedStatamicUniqueRules;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;
use Tests\UpdateScripts\Concerns\RunsUpdateScripts;

class UseClassBasedStatamicUniqueRulesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk, RunsUpdateScripts;

    public function tearDown(): void
    {
        foreach (static::examplePaths() as $path) {
            File::delete($path[0]);
        }

        parent::tearDown();
    }

    #[Test]
    public function it_is_registered()
    {
        $this->assertUpdateScriptRegistered(UseClassBasedStatamicUniqueRules::class);
    }

    public static function examplePaths()
    {
        return [
            'example blueprint' => ['resources/blueprints/collections/articles/test.yaml'],
            'example fieldset' => ['resources/fieldsets/test.yaml'],
        ];
    }

    #[Test]
    #[DataProvider('examplePaths')]
    public function it_can_update_old_string_based_rules_in_file_based_blueprints($path)
    {
        File::put($path, <<<'BLUEPRINT'
field:
  validate_array:
    - 'unique_entry_value:{collection},{id},{site}'
    - 'unique_term_value:{taxonomy},{id},{site}'
    - 'unique_user_value:{id}'
    - 'unique_user_value:{id},some_column'
  validate_string_entry: 'unique_entry_value:{collection},{id},{site}'
  validate_string_term: 'unique_term_value:{taxonomy},{id},{site}'
  validate_string_user: 'unique_user_value:{id}'
  validate_string_user_with_column: 'unique_user_value:{id},another_column'
BLUEPRINT
        );

        $this->runUpdateScript(UseClassBasedStatamicUniqueRules::class);

        $expected = <<<'BLUEPRINT'
field:
  validate_array:
    - 'new \Statamic\Rules\UniqueEntryValue({collection}, {id}, {site})'
    - 'new \Statamic\Rules\UniqueTermValue({taxonomy}, {id}, {site})'
    - 'new \Statamic\Rules\UniqueUserValue({id})'
    - 'new \Statamic\Rules\UniqueUserValue({id}, "some_column")'
  validate_string_entry: 'new \Statamic\Rules\UniqueEntryValue({collection}, {id}, {site})'
  validate_string_term: 'new \Statamic\Rules\UniqueTermValue({taxonomy}, {id}, {site})'
  validate_string_user: 'new \Statamic\Rules\UniqueUserValue({id})'
  validate_string_user_with_column: 'new \Statamic\Rules\UniqueUserValue({id}, "another_column")'
BLUEPRINT;

        $this->assertEquals($expected, File::get($path));
    }

    #[Test]
    #[DataProvider('examplePaths')]
    public function it_doesnt_overzealously_try_to_replace_complicated_pipe_delimed_validate_rules($path)
    {
        File::put($path, <<<'BLUEPRINT'
validate: 'unique_entry_value:{collection},{id},{site}'
validate: 'unique_term_value:{taxonomy},{id},{site}'
validate: 'unique_user_value:{id}'
validate: 'unique_user_value:{id},some_column'
validate_piped_entry: 'required|unique_entry_value:{collection},{id},{site}'
validate_piped_term: 'required|unique_term_value:{taxonomy},{id},{site}'
validate_piped_user: 'required|unique_user_value:{id}'
validate_piped_user_with_column: 'required|unique_user_value:{id},another_column'
BLUEPRINT
        );

        $this->runUpdateScript(UseClassBasedStatamicUniqueRules::class);

        $expected = <<<'BLUEPRINT'
validate: 'new \Statamic\Rules\UniqueEntryValue({collection}, {id}, {site})'
validate: 'new \Statamic\Rules\UniqueTermValue({taxonomy}, {id}, {site})'
validate: 'new \Statamic\Rules\UniqueUserValue({id})'
validate: 'new \Statamic\Rules\UniqueUserValue({id}, "some_column")'
validate_piped_entry: 'required|unique_entry_value:{collection},{id},{site}'
validate_piped_term: 'required|unique_term_value:{taxonomy},{id},{site}'
validate_piped_user: 'required|unique_user_value:{id}'
validate_piped_user_with_column: 'required|unique_user_value:{id},another_column'
BLUEPRINT;

        $this->assertEquals($expected, File::get($path));
    }
}
