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
    public function it_augments_the_current_user_when_value_is_the_current_string()
    {
        $this->actingAs(Facades\User::find('123'));

        $augmented = $this->fieldtype(['max_items' => 1])->augment('current');

        $this->assertInstanceOf(User::class, $augmented);
        $this->assertEquals('123', $augmented->id());
    }

    #[Test]
    public function it_augments_the_current_user_to_a_query_builder_when_value_is_the_current_string()
    {
        $this->actingAs(Facades\User::find('123'));

        $augmented = $this->fieldtype()->augment('current');

        $this->assertInstanceOf(Builder::class, $augmented);
        $this->assertEquals(['123'], $augmented->get()->map->id()->all());
    }

    #[Test]
    public function it_augments_to_null_when_value_is_the_current_string_and_no_user_is_authenticated()
    {
        $augmented = $this->fieldtype(['max_items' => 1])->augment('current');

        $this->assertNull($augmented);
    }

    #[Test]
    public function it_resolves_the_current_value_when_preprocessing_for_the_field()
    {
        $this->actingAs(Facades\User::find('123'));

        $this->assertEquals(['123'], $this->fieldtype()->preProcess('current'));
        $this->assertEquals(['123'], $this->fieldtype()->preProcess(['current']));
        $this->assertEquals(['123', '456'], $this->fieldtype()->preProcess(['current', '456']));
    }

    #[Test]
    public function it_keeps_the_current_value_when_preprocessing_the_config()
    {
        $this->actingAs(Facades\User::find('123'));

        // The default picker keeps "current" as-is so it round-trips through the
        // blueprint editor instead of being resolved to the current user's id.
        $this->assertEquals(['current'], $this->fieldtype(['allow_current' => true])->preProcessConfig('current'));
        $this->assertEquals(['current'], $this->fieldtype(['allow_current' => true])->preProcessConfig(['current']));
    }

    #[Test]
    public function it_includes_a_current_user_option_when_allowed()
    {
        $this->actingAs($this->cpUserWithPermissions(['access cp', 'view users']));

        $items = $this->fieldtype(['allow_current' => true])->getIndexItems(new Request(['paginate' => false]));

        $this->assertEquals(['id' => 'current', 'title' => 'Current User'], $items->first());
    }

    #[Test]
    public function it_does_not_include_a_current_user_option_by_default()
    {
        $this->actingAs($this->cpUserWithPermissions(['access cp', 'view users']));

        $items = $this->fieldtype()->getIndexItems(new Request(['paginate' => false]));

        $this->assertFalse($items->contains(fn ($item) => $item['id'] === 'current'));
    }

    #[Test]
    public function it_excludes_the_current_user_option_when_it_does_not_match_the_search()
    {
        $this->actingAs($this->cpUserWithPermissions(['access cp', 'view users']));

        $fieldtype = $this->fieldtype(['allow_current' => true]);

        $this->assertTrue(
            $fieldtype->getIndexItems(new Request(['paginate' => false, 'search' => 'curr']))
                ->contains(fn ($item) => $item['id'] === 'current')
        );

        $this->assertFalse(
            $fieldtype->getIndexItems(new Request(['paginate' => false, 'search' => 'zzz']))
                ->contains(fn ($item) => $item['id'] === 'current')
        );
    }

    #[Test]
    public function it_provides_item_data_for_the_current_user_option()
    {
        $data = $this->fieldtype(['allow_current' => true])->getItemData(['current']);

        $this->assertEquals([['id' => 'current', 'title' => 'Current User']], $data->all());
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
    public function it_returns_empty_index_items_without_view_users_permission()
    {
        $this->actingAs($this->cpUserWithPermissions(['access cp']));

        $items = $this->fieldtype()->getIndexItems(new Request(['paginate' => false]));

        $this->assertTrue($items->isEmpty());
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
