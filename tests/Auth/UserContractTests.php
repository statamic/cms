<?php

namespace Tests\Auth;

use BadMethodCallException;
use Facades\Statamic\Fields\BlueprintRepository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Statamic\Auth\File\Role;
use Statamic\Auth\File\UserGroup;
use Statamic\Events\UserSaved;
use Statamic\Events\UserSaving;
use Statamic\Facades;
use Statamic\Facades\Blueprint;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\Value;
use Statamic\Support\Arr;

trait UserContractTests
{
    abstract public function makeUser();

    public function user()
    {
        $user = $this->makeUser()
            ->id(123)
            ->email('john@example.com')
            ->data([
                'name' => 'John Smith',
                'foo' => 'bar',
                'content' => 'Lorem Ipsum',
            ])
            ->setPreferredLocale('en')
            ->setSupplement('supplemented', 'qux')
            ->assignRole($roleOne = $this->createRole('role_one'))
            ->assignRole($roleTwo = $this->createRole('role_two'))
            ->addToGroup($groupOne = $this->createGroup('group_one'))
            ->addToGroup($groupTwo = $this->createGroup('group_two'));

        Role::shouldReceive('all')->andReturn(collect([$roleOne, $roleTwo]));
        UserGroup::shouldReceive('all')->andReturn(collect([$groupOne, $groupTwo]));

        return $user;
    }

    /** @test */
    public function it_gets_email()
    {
        $this->assertEquals('john@example.com', $this->user()->email());
    }

    /** @test */
    public function it_gets_email_as_property()
    {
        $this->assertEquals('john@example.com', $this->user()->email);
    }

    /** @test */
    public function gets_the_name()
    {
        $this->assertEquals('John', $this->makeUser()->set('name', 'John')->name());
        $this->assertEquals('John Smith', $this->makeUser()->set('name', 'John Smith')->name());
        $this->assertEquals('John', $this->makeUser()->data(['name' => null, 'first_name' => 'John'])->name());
        $this->assertEquals('John Smith', $this->makeUser()->data(['name' => null, 'first_name' => 'John', 'last_name' => 'Smith'])->name());
        $this->assertEquals('john@example.com', $this->makeUser()->remove('name')->email('john@example.com')->name());
    }

    /** @test */
    public function it_gets_data()
    {
        $this->assertEquals(array_merge([
            'name' => 'John Smith',
            'foo' => 'bar',
            'content' => 'Lorem Ipsum',
            'roles' => [
                'role_one',
                'role_two',
            ],
            'groups' => [
                'group_one',
                'group_two',
            ],
        ], $this->additionalDataValues()), $this->user()->data()->all());
    }

    /** @test */
    public function it_gets_custom_computed_data()
    {
        Facades\User::computed('balance', function ($user) {
            return $user->name().'\'s balance is $25 owing.';
        });

        $user = $this->makeUser()->data(['name' => 'Han Solo']);

        $expectedData = [
            'name' => 'Han Solo',
        ];

        $expectedComputedData = [
            'balance' => 'Han Solo\'s balance is $25 owing.',
        ];

        $expectedValues = array_merge($expectedData, $expectedComputedData);

        $this->assertArraySubset($expectedData, $user->data()->all());
        $this->assertEquals($expectedComputedData, $user->computedData()->all());
        $this->assertEquals($expectedValues['name'], $user->value('name'));
        $this->assertEquals($expectedValues['balance'], $user->value('balance'));
    }

    /** @test */
    public function it_gets_empty_computed_data_by_default()
    {
        $this->assertEquals([], $this->user()->computedData()->all());
    }

    /** @test */
    public function it_doesnt_recursively_get_computed_data_when_callback_uses_value_method()
    {
        Facades\User::computed('balance', function ($user) {
            return $user->value('balance') ?? $user->name().'\'s balance is $25 owing.';
        });

        $user = $this->makeUser()->data(['name' => 'Han Solo']);

        $this->assertEquals('Han Solo\'s balance is $25 owing.', $user->value('balance'));
    }

    /** @test */
    public function it_can_use_actual_data_to_compose_computed_data()
    {
        Facades\User::computed('nickname', function ($user, $value) {
            return $value ?? 'Nameless';
        });

        $user = $this->makeUser();

        $this->assertEquals('Nameless', $user->value('nickname'));

        $user->data(['nickname' => 'The Hoff']);

        $this->assertEquals('The Hoff', $user->value('nickname'));
    }

    public function additionalDataValues()
    {
        return [];
    }

    /**
     * @test
     *
     * @dataProvider queryBuilderProvider
     **/
    public function it_has_magic_property_and_methods_for_fields_that_augment_to_query_builders($builder)
    {
        $builder->shouldReceive('get')->times(2)->andReturn('query builder results');
        app()->instance('mocked-builder', $builder);

        (new class extends Fieldtype
        {
            protected static $handle = 'test';

            public function augment($value)
            {
                return app('mocked-builder');
            }
        })::register();

        $blueprint = Facades\Blueprint::makeFromFields(['foo' => ['type' => 'test']]);
        BlueprintRepository::shouldReceive('find')->with('user')->andReturn($blueprint);

        $user = $this->user();
        $user->set('foo', 'delta');

        $this->assertEquals('query builder results', $user->foo);
        $this->assertEquals('query builder results', $user['foo']);
        $this->assertSame($builder, $user->foo());
    }

    public static function queryBuilderProvider()
    {
        return [
            'statamic' => [Mockery::mock(\Statamic\Query\Builder::class)],
            'database' => [Mockery::mock(\Illuminate\Database\Query\Builder::class)],
            'eloquent' => [Mockery::mock(\Illuminate\Database\Eloquent\Builder::class)],
        ];
    }

    /** @test */
    public function calling_unknown_method_throws_exception()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage(sprintf('Call to undefined method %s::thisFieldDoesntExist()', get_class($this->user())));

        $this->user()->thisFieldDoesntExist();
    }

    /** @test */
    public function it_converts_to_an_array()
    {
        $fieldtype = new class extends Fieldtype
        {
            protected static $handle = 'test';

            public function augment($value)
            {
                return [
                    new Value('alfa'),
                    new Value([
                        new Value('bravo'),
                        new Value('charlie'),
                        'delta',
                    ]),
                ];
            }
        };
        $fieldtype::register();

        $blueprint = Blueprint::makeFromFields([
            'baz' => [
                'type' => 'test',
            ],
        ]);
        BlueprintRepository::shouldReceive('find')->with('user')->andReturn($blueprint->setHandle('post'));

        $user = $this->user()
            ->set('foo', 'bar')
            ->set('baz', 'qux');

        $this->assertInstanceOf(Arrayable::class, $user);

        $array = $user->toArray();
        $this->assertEquals($user->augmented()->keys(), array_keys($array));
        $this->assertEquals([
            'alfa',
            [
                'bravo',
                'charlie',
                'delta',
            ],
        ], $array['baz'], 'Value objects are not resolved recursively');

        $array = $user
            ->selectedQueryColumns($keys = ['id', 'foo', 'baz'])
            ->toArray();

        $this->assertEquals($keys, array_keys($array), 'toArray keys differ from selectedQueryColumns');
    }

    /** @test */
    public function only_requested_relationship_fields_are_included_in_to_array()
    {
        $regularFieldtype = new class extends Fieldtype
        {
            protected static $handle = 'regular';

            public function augment($value)
            {
                return 'augmented '.$value;
            }
        };
        $regularFieldtype::register();

        $relationshipFieldtype = new class extends Fieldtype
        {
            protected static $handle = 'relationship';
            protected $relationship = true;

            public function augment($values)
            {
                return collect($values)->map(fn ($value) => 'augmented '.$value)->all();
            }
        };
        $relationshipFieldtype::register();

        $blueprint = Blueprint::makeFromFields([
            'alfa' => ['type' => 'regular'],
            'bravo' => ['type' => 'relationship'],
            'charlie' => ['type' => 'relationship'],
        ]);
        BlueprintRepository::shouldReceive('find')->with('user')->andReturn($blueprint->setHandle('post'));

        $user = $this->user()
            ->set('alfa', 'one')
            ->set('bravo', ['a', 'b'])
            ->set('charlie', ['c', 'd']);

        $this->assertEquals([
            'alfa' => 'augmented one',
            'bravo' => ['a', 'b'],
            'charlie' => ['augmented c', 'augmented d'],
        ], Arr::only($user->selectedQueryRelations(['charlie'])->toArray(), ['alfa', 'bravo', 'charlie']));
    }

    /** @test */
    public function it_gets_id()
    {
        $this->assertEquals('123', $this->user()->id());
    }

    /** @test */
    public function it_gets_initials_from_name()
    {
        $this->assertEquals('JS', $this->user()->initials());
    }

    /** @test */
    public function it_gets_initials_from_name_with_no_surname()
    {
        $this->assertEquals('J', $this->user()->set('name', 'John')->initials());
    }

    /** @test */
    public function it_gets_initials_from_email_if_name_doesnt_exist()
    {
        $user = $this->user()->remove('name');

        $this->assertEquals('J', $user->initials());
    }

    /** @test */
    public function it_gets_avatar_from_gravatar_if_config_allows()
    {
        config(['statamic.users.avatars' => 'gravatar']);

        $this->assertEquals(
            'https://www.gravatar.com/avatar/d4c74594d841139328695756648b6bd6?s=64',
            $this->user()->avatar()
        );
        $this->assertEquals(
            'https://www.gravatar.com/avatar/d4c74594d841139328695756648b6bd6?s=32',
            $this->user()->avatar(32)
        );

        config(['statamic.users.avatars' => 'initials']);

        $this->assertNull($this->user()->avatar());
    }

    /** @test */
    public function it_gets_preferred_locale()
    {
        $this->assertEquals('en', $this->user()->preferredLocale());
    }

    /** @test */
    public function it_encrypts_a_password()
    {
        $user = $this->user();

        $this->assertNull($user->password());

        $user->password('secret');

        $this->assertNotNull($user->password());
        $this->assertNotEquals('secret', $user->password());
        $this->assertTrue(Hash::check('secret', $user->password()));
    }

    /** @test */
    public function it_encrypts_a_password_when_set_through_data()
    {
        $user = $this->user();

        $this->assertNull($user->password());

        $user->data([
            'foo' => 'bar',
            'password' => 'secret',
        ]);

        $this->assertNotNull($user->password());
        $this->assertNotEquals('secret', $user->password());
        $this->assertTrue(Hash::check('secret', $user->password()));
        $this->assertArrayNotHasKey('password', $user->data());
    }

    /** @test */
    public function it_provides_email_field_fallback_in_blueprint()
    {
        $blueprint = Blueprint::make();
        Blueprint::shouldReceive('find')->with('user')->andReturn($blueprint);

        $this->assertTrue($this->user()->blueprint()->hasField('email'));
        $this->assertEquals('Email Address', $this->user()->blueprint()->fields()->get('email')->display());
        $this->assertEquals('email', $this->user()->blueprint()->fields()->get('email')->get('input_type'));
    }

    /** @test */
    public function it_allows_email_field_customizations_in_blueprint()
    {
        $blueprint = Blueprint::makeFromFields(['email' => ['display' => 'Custom Email Display']]);
        Blueprint::shouldReceive('find')->with('user')->andReturn($blueprint);

        $this->assertTrue($this->user()->blueprint()->hasField('email'));
        $this->assertEquals('Custom Email Display', $this->user()->blueprint()->fields()->get('email')->display());
        $this->assertEquals('email', $this->user()->blueprint()->fields()->get('email')->get('input_type'));
    }

    /** @test */
    public function it_provides_roles_and_groups_field_fallbacks_in_blueprint()
    {
        $blueprint = Blueprint::make();
        Blueprint::shouldReceive('find')->with('user')->andReturn($blueprint);

        $this->assertTrue($this->user()->blueprint()->hasField('roles'));
        $this->assertEquals('Roles', $this->user()->blueprint()->fields()->get('roles')->display());
        $this->assertEquals('user_roles', $this->user()->blueprint()->fields()->get('roles')->type());

        $this->assertTrue($this->user()->blueprint()->hasField('groups'));
        $this->assertEquals('Groups', $this->user()->blueprint()->fields()->get('groups')->display());
        $this->assertEquals('user_groups', $this->user()->blueprint()->fields()->get('groups')->type());
    }

    /** @test */
    public function it_allows_roles_and_groups_field_customizations_in_blueprint()
    {
        $blueprint = Blueprint::makeFromFields([
            'roles' => ['display' => 'Custom Roles Display'],
            'groups' => ['display' => 'Custom Groups Display'],
        ]);
        Blueprint::shouldReceive('find')->with('user')->andReturn($blueprint);

        $this->assertTrue($this->user()->blueprint()->hasField('roles'));
        $this->assertEquals('Custom Roles Display', $this->user()->blueprint()->fields()->get('roles')->display());
        $this->assertEquals('user_roles', $this->user()->blueprint()->fields()->get('roles')->type());

        $this->assertTrue($this->user()->blueprint()->hasField('groups'));
        $this->assertEquals('Custom Groups Display', $this->user()->blueprint()->fields()->get('groups')->display());
        $this->assertEquals('user_groups', $this->user()->blueprint()->fields()->get('groups')->type());
    }

    /** @test */
    public function it_removes_roles_and_groups_field_fallbacks_in_blueprint_when_pro_is_disabled()
    {
        config(['statamic.editions.pro' => false]);
        $blueprint = Blueprint::make();
        Blueprint::shouldReceive('find')->with('user')->andReturn($blueprint);

        $this->assertFalse($this->user()->blueprint()->hasField('roles'));
        $this->assertFalse($this->user()->blueprint()->hasField('groups'));
    }

    /** @test */
    public function it_removes_roles_and_groups_event_when_explicitly_defined_in_blueprint_when_pro_is_disabled()
    {
        config(['statamic.editions.pro' => false]);
        $blueprint = Blueprint::makeFromFields([
            'roles' => ['display' => 'Custom Roles Display'],
            'groups' => ['display' => 'Custom Groups Display'],
        ]);
        Blueprint::shouldReceive('find')->with('user')->andReturn($blueprint);

        $this->assertFalse($this->user()->blueprint()->hasField('roles'));
        $this->assertFalse($this->user()->blueprint()->hasField('groups'));
    }

    /** @test */
    public function converts_to_array()
    {
        Role::shouldReceive('all')->andReturn(collect([
            $this->createRole('role_one'),
            $this->createRole('role_two'),
            $this->createRole('role_three'),
        ]));

        UserGroup::shouldReceive('all')->andReturn(collect([
            $this->createGroup('group_one'),
            $this->createGroup('group_two'),
            $this->createGroup('group_three'),
        ]));

        $arr = $this->user()->toAugmentedArray();

        $arr = array_map(function ($item) {
            return $item instanceof \Statamic\Fields\Value ? $item->value() : $item;
        }, $arr);

        $this->assertEquals(array_merge([
            'name' => 'John Smith',
            'foo' => 'bar',
            'content' => 'Lorem Ipsum',
            'email' => 'john@example.com',
            'id' => 123,
            'roles' => [
                'role_one',
                'role_two',
            ],
            'groups' => [
                'group_one',
                'group_two',
            ],
            'is_role_one' => true,
            'is_role_two' => true,
            'is_role_three' => false,
            'in_group_one' => true,
            'in_group_two' => true,
            'in_group_three' => false,
            'supplemented' => 'qux',
            'avatar' => null,
            'initials' => 'JS',
            'is_user' => true,
            'title' => 'john@example.com',
            'edit_url' => 'http://localhost/cp/users/123/edit',
            'last_login' => null,
            'api_url' => 'http://localhost/api/users/123',
            'preferred_locale' => 'en',
        ], $this->additionalToArrayValues()), $arr);
    }

    public function additionalToArrayValues()
    {
        return [];
    }

    /**
     * @test
     */
    public function it_has_a_dirty_state()
    {
        $user = $this->makeUser();
        $user->email('test@test.com');
        $user->save();

        $this->assertFalse($user->isDirty());
        $this->assertFalse($user->isDirty('email'));
        $this->assertFalse($user->isDirty('name'));
        $this->assertFalse($user->isDirty(['email']));
        $this->assertFalse($user->isDirty(['name']));
        $this->assertFalse($user->isDirty(['email', 'name']));
        $this->assertTrue($user->isClean());
        $this->assertTrue($user->isClean('email'));
        $this->assertTrue($user->isClean('name'));
        $this->assertTrue($user->isClean(['email']));
        $this->assertTrue($user->isClean(['name']));
        $this->assertTrue($user->isClean(['email', 'name']));

        $user->email('test@tester.com');

        $this->assertTrue($user->isDirty());
        $this->assertTrue($user->isDirty('email'));
        $this->assertFalse($user->isDirty('name'));
        $this->assertTrue($user->isDirty(['email']));
        $this->assertFalse($user->isDirty(['name']));
        $this->assertTrue($user->isDirty(['email', 'name']));
        $this->assertFalse($user->isClean());
        $this->assertFalse($user->isClean('email'));
        $this->assertTrue($user->isClean('name'));
        $this->assertFalse($user->isClean(['email']));
        $this->assertTrue($user->isClean(['name']));
        $this->assertFalse($user->isClean(['email', 'name']));
    }

    /** @test */
    public function it_syncs_original_at_the_right_time()
    {
        $eventsHandled = 0;

        Event::listen(function (UserSaving $event) use (&$eventsHandled) {
            $eventsHandled++;
            $this->assertTrue($event->user->isDirty());
        });
        Event::listen(function (UserSaved $event) use (&$eventsHandled) {
            $eventsHandled++;
            $this->assertTrue($event->user->isDirty());
        });

        $user = $this->makeUser();
        $user->email('test@test.com');
        $user->save();

        $this->assertFalse($user->isDirty());
        $this->assertEquals(2, $eventsHandled);
    }

    private function createRole($handle)
    {
        $class = new class($handle) extends Role
        {
            public function __construct($handle)
            {
                $this->handle = $handle;
            }
        };

        Facades\Role::shouldReceive('find')
            ->with($handle)
            ->andReturn($class);

        return $class;
    }

    private function createGroup($handle)
    {
        $class = new class($handle) extends UserGroup
        {
            public function __construct($handle)
            {
                $this->handle = $handle;
            }
        };

        Facades\UserGroup::shouldReceive('find')
            ->with($handle)
            ->andReturn($class);

        return $class;
    }
}
