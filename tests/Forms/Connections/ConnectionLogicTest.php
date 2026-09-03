<?php

namespace Tests\Forms\Connections;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Forms\Connections\ConnectionLogic;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ConnectionLogicTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_generates_ids_for_conditions_when_pre_processing()
    {
        $conditions = ConnectionLogic::preProcess([
            ['field' => 'name', 'operator' => 'equals', 'value' => 'Bob', 'join' => 'and'],
            ['field' => 'name', 'operator' => 'equals', 'value' => 'Alice', 'join' => 'or'],
        ]);

        $this->assertNotEmpty($conditions[0]['_id']);
        $this->assertNotEmpty($conditions[1]['_id']);
        $this->assertNotEquals($conditions[0]['_id'], $conditions[1]['_id']);
        $this->assertEquals('Bob', $conditions[0]['value']);
        $this->assertEquals('Alice', $conditions[1]['value']);
    }

    #[Test]
    public function it_processes_conditions()
    {
        $this->assertEquals([
            ['field' => 'name', 'operator' => 'equals', 'value' => 'Bob', 'join' => 'and'],
        ], ConnectionLogic::process([
            ['_id' => 'vue-row', 'field' => 'name', 'operator' => 'equals', 'value' => 'Bob', 'join' => 'and'],
            ['field' => null, 'operator' => 'equals', 'value' => 'incomplete', 'join' => 'and'],
            ['field' => 'name', 'operator' => 'equals', 'value' => '', 'join' => 'and'],
        ]));
    }

    #[Test]
    public function it_processes_empty_conditions_to_null()
    {
        $this->assertNull(ConnectionLogic::process([]));
        $this->assertNull(ConnectionLogic::process([
            ['field' => null, 'operator' => 'equals', 'value' => 'incomplete', 'join' => 'and'],
        ]));
    }

    #[Test]
    #[DataProvider('passesProvider')]
    public function it_determines_whether_a_config_passes_for_a_submission(array $config, bool $passes)
    {
        $form = tap(Form::make('test')->formFields([
            'fields' => [
                ['handle' => 'how_did_you_hear', 'field' => ['type' => 'text']],
            ],
        ]))->save();

        $submission = $form->makeSubmission()->data(['how_did_you_hear' => 'friend']);

        $this->assertEquals($passes, ConnectionLogic::passes($config, $submission));
    }

    public static function passesProvider(): array
    {
        $matching = [['field' => 'how_did_you_hear', 'operator' => 'equals', 'value' => 'friend', 'join' => 'and']];
        $nonMatching = [['field' => 'how_did_you_hear', 'operator' => 'equals', 'value' => 'google', 'join' => 'and']];

        return [
            'no conditions' => [[], true],
            'explicitly enabled' => [['enabled' => true], true],
            'matching conditions' => [['conditions' => $matching], true],
            'non-matching conditions' => [['conditions' => $nonMatching], false],
            'disabled' => [['enabled' => false], false],
            'disabled with matching conditions' => [['enabled' => false, 'conditions' => $matching], false],
        ];
    }
}
