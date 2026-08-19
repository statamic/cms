<?php

namespace Tests\CP\ResourceIndex;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Statamic\Contracts\CP\ResourceIndex\GroupRepository;
use Statamic\CP\ResourceIndex\ResourceIndexRepository;

class ResourceIndexRepositoryTest extends TestCase
{
    private ResourceIndexRepository $repository;
    private InMemoryGroupRepository $groups;

    protected function setUp(): void
    {
        parent::setUp();

        $this->groups = new InMemoryGroupRepository;
        $this->repository = new ResourceIndexRepository($this->groups);
    }

    #[Test]
    public function it_creates_named_request_local_indexes()
    {
        $index = $this->repository->make('ships', [
            ['id' => 'falcon', 'title' => 'Millennium Falcon'],
        ])->title('Ships')->itemLabel('Ship');

        $this->assertSame('Ships', $index->title());
        $this->assertSame('Ship', $index->itemLabel());
        $this->assertSame('Millennium Falcon', $index->all()->first()['title']);
    }

    #[Test]
    public function saved_groups_override_defaults_preserve_unknown_ids_and_can_be_reset()
    {
        $index = $this->makeIndex([
            ['id' => 'default', 'title' => 'Default', 'items' => ['one']],
        ]);

        $this->repository->saveGroups($index, [
            ['id' => 'custom', 'title' => 'Custom', 'items' => ['two', 'missing', 'two']],
            ['id' => ResourceIndexRepository::FALLBACK_GROUP, 'title' => 'Reserved', 'items' => ['one']],
        ]);

        $this->assertTrue($this->repository->hasSavedGroups($index));
        $this->assertSame([
            ['id' => 'custom', 'title' => 'Custom', 'items' => ['two', 'missing']],
        ], $this->repository->groups($index));
        $this->assertSame(
            $this->repository->groups($index),
            $this->groups->find('ships')
        );

        $this->repository->resetGroups($index);

        $this->assertFalse($this->repository->hasSavedGroups($index));
        $this->assertSame([
            ['id' => 'default', 'title' => 'Default', 'items' => ['one']],
        ], $this->repository->groups($index));
    }

    #[Test]
    public function it_preserves_a_valid_zero_group_title()
    {
        $index = $this->makeIndex([]);

        $this->repository->saveGroups($index, [
            ['id' => 'zero', 'title' => '0', 'items' => ['one']],
        ]);

        $this->assertSame('0', $this->repository->groups($index)[0]['title']);
    }

    #[Test]
    public function saving_no_groups_resets_to_the_default_configuration()
    {
        $index = $this->makeIndex([
            ['id' => 'default', 'title' => 'Default', 'items' => ['one']],
        ]);

        $this->repository->saveGroups($index, [
            ['id' => 'custom', 'title' => 'Custom', 'items' => ['two']],
        ]);

        $this->repository->saveGroups($index, []);

        $this->assertFalse($this->repository->hasSavedGroups($index));
        $this->assertSame([
            ['id' => 'default', 'title' => 'Default', 'items' => ['one']],
        ], $this->repository->groups($index));
    }

    private function makeIndex(array $defaultGroups)
    {
        return $this->repository->make('ships', [
            ['id' => 'one', 'title' => 'One'],
            ['id' => 'two', 'title' => 'Two'],
            ['id' => 'three', 'title' => 'Three'],
        ])->defaultGroups($defaultGroups);
    }
}

class InMemoryGroupRepository implements GroupRepository
{
    private array $groups = [];

    public function find(string $resourceIndex): ?array
    {
        return $this->groups[$resourceIndex] ?? null;
    }

    public function save(string $resourceIndex, array $groups): void
    {
        $this->groups[$resourceIndex] = $groups;
    }

    public function delete(string $resourceIndex): void
    {
        unset($this->groups[$resourceIndex]);
    }
}
