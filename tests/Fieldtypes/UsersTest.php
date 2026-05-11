<?php

namespace Tests\Fieldtypes;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\UserCollection;
use Statamic\Contracts\Auth\User;
use Statamic\Contracts\Query\Builder;
use Statamic\Data\AugmentedCollection;
use Statamic\Facades;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Users;
use Tests\FakesRoles;
use Tests\Fieldtypes\Concerns\TestsQueryableValueWithMaxItems;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UsersTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;
    use TestsQueryableValueWithMaxItems;

    public function setUp(): void
    {
        parent::setUp();

        Facades\User::make()->id('123')->set('name', 'One')->email('one@domain.com')->save();
        Facades\User::make()->id('456')->set('name', 'Two')->email('two@domain.com')->save();
        Facades\User::make()->id('789')->email('nameless@domain.com')->save();
    }

    #[Test]
    public function it_augments_to_a_query_builder()
    {
        $augmented = $this->fieldtype()->augment([456, '123']);

        $this->assertInstanceOf(Builder::class, $augmented);
        $this->assertEveryItemIsInstanceOf(User::class, $augmented->get());
        $this->assertEquals(['456', '123'], $augmented->get()->map->id()->all());
    }

    #[Test]
    public function it_augments_to_a_query_builder_when_theres_no_value()
    {
        $augmented = $this->fieldtype()->augment(null);

        $this->assertInstanceOf(Builder::class, $augmented);
        $this->assertCount(0, $augmented->get());
    }

    #[Test]
    public function it_augments_to_a_single_user_when_max_items_is_one()
    {
        $augmented = $this->fieldtype(['max_items' => 1])->augment(['123']);

        $this->assertInstanceOf(User::class, $augmented);
        $this->assertEquals('one@domain.com', $augmented->email());
    }

    #[Test]
    public function it_shallow_augments_to_a_collection_of_users()
    {
        $augmented = $this->fieldtype()->shallowAugment(['123', 456]);

        $this->assertInstanceOf(Collection::class, $augmented);
        $this->assertNotInstanceOf(UserCollection::class, $augmented);
        $this->assertEveryItemIsInstanceOf(AugmentedCollection::class, $augmented);
        $this->assertEquals([
            [
                'id' => '123',
                'name' => 'One',
                'email' => 'one@domain.com',
                'api_url' => 'http://localhost/api/users/123',
            ],
            [
                'id' => '456',
                'name' => 'Two',
                'email' => 'two@domain.com',
                'api_url' => 'http://localhost/api/users/456',
            ],
        ], $augmented->toArray());
    }

    #[Test]
    public function it_shallow_augments_to_a_single_user_when_max_items_is_one()
    {
        $augmented = $this->fieldtype(['max_items' => 1])->shallowAugment(['123']);

        $this->assertInstanceOf(AugmentedCollection::class, $augmented);
        $this->assertEquals([
            'id' => '123',
            'name' => 'One',
            'email' => 'one@domain.com',
            'api_url' => 'http://localhost/api/users/123',
        ], $augmented->toArray());
    }

    #[Test]
    public function it_hides_email_from_index_items_without_view_users_permission()
    {
        $this->actingAs($this->cpUserWithPermissions(['access cp']));

        $items = $this->fieldtype()->getIndexItems(new Request(['paginate' => false]));
        $namelessUser = $items->firstWhere('id', '789');

        $this->assertArrayNotHasKey('email', $namelessUser);
        $this->assertEquals('789', $namelessUser['title']);
    }

    #[Test]
    public function it_includes_email_in_index_items_with_view_users_permission()
    {
        $this->actingAs($this->cpUserWithPermissions(['access cp', 'view users']));

        $items = $this->fieldtype()->getIndexItems(new Request(['paginate' => false]));
        $namelessUser = $items->firstWhere('id', '789');

        $this->assertEquals('nameless@domain.com', $namelessUser['title']);
        $this->assertEquals('nameless@domain.com', $namelessUser['email']);
    }

    #[Test]
    public function it_hides_the_email_column_without_view_users_permission()
    {
        $this->actingAs($this->cpUserWithPermissions(['access cp']));

        $columns = $this->getColumns($this->fieldtype());

        $this->assertCount(1, $columns);
        $this->assertEquals('title', $columns[0]->field);
    }

    #[Test]
    public function it_includes_the_email_column_with_view_users_permission()
    {
        $this->actingAs($this->cpUserWithPermissions(['access cp', 'view users']));

        $columns = $this->getColumns($this->fieldtype());

        $this->assertCount(2, $columns);
        $this->assertEquals('title', $columns[0]->field);
        $this->assertEquals('email', $columns[1]->field);
    }

    public function fieldtype($config = [])
    {
        $field = new Field('test', array_merge([
            'type' => 'users',
        ], $config));

        return (new Users)->setField($field);
    }

    private function cpUserWithPermissions(array $permissions)
    {
        $this->setTestRoles(['test' => $permissions]);

        return tap(Facades\User::make()->id(uniqid())->assignRole('test'))->save();
    }

    private function getColumns(Users $fieldtype): array
    {
        $method = new \ReflectionMethod($fieldtype, 'getColumns');

        return $method->invoke($fieldtype);
    }
}
