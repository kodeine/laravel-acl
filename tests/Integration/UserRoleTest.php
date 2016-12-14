<?php namespace Kodeine\Acl\Tests\Integration;

use Kodeine\Acl\Models\Eloquent\Permission;
use Kodeine\Acl\Models\Eloquent\Role;
use Kodeine\Acl\Models\Eloquent\User;

class UserRoleTest extends IntegrationTest
{

    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var User */
    protected $userModel;


    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp()
    {
        parent::setUp();

        $this->userModel = new User;
    }


    public function tearDown()
    {
        parent::tearDown();

        unset($this->userModel);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Test Functions
     | ------------------------------------------------------------------------------------------------
     */

    /** @test */
    public function itCanAssignRevokeRolesForModelId()
    {
        list($role, $permissions, $user) = $this->createRolesPermissionUser();
        if ($user instanceof User) {
        }
        $models = [
            'first'  => [
                'model' => 'example_model',
                'id'    => 42,
            ],
            'second' => [
                'model' => 'example_model',
                'id'    => 43,
            ],
            'third'  => [
                'model' => 'another_model',
                'id'    => 0,
            ],
        ];

        $objRole           = new Role();
        $manager_role_slug = str_slug('Manager role', config('laravel-auth.slug-separator'));
        $roleAttributes    = [
            'name'        => 'Manager',
            'slug'        => $manager_role_slug,
            'description' => 'Manager role description.',
        ];
        $manager_role      = $objRole->create($roleAttributes);

        // No roles assigned yet.
        $this->assertEquals([], $user->getRoles(), 'Expected empty roles set');

        // Assign user role to three model entities.
        foreach ($models as $m) {
            $user->assignRole($role, $m['model'], $m['id']);
        }
        $role_slug = str_slug('Admin role', config('laravel-auth.slug-separator'));

        // Sorted by slug:model_reference_key.
        $this->assertEquals([
            1 => [
                "{$role_slug}:{$models['third']['model']}",
                "{$role_slug}:{$models['first']['model']}:{$models['first']['id']}",
                "{$role_slug}:{$models['second']['model']}:{$models['second']['id']}",
            ]
        ], $user->getRoles(), 'Expected assigned "admin" role to different models');
        //
        $user->assignRole($manager_role);
        $this->assertEquals([
            1 => [
                "{$role_slug}:{$models['third']['model']}",
                "{$role_slug}:{$models['first']['model']}:{$models['first']['id']}",
                "{$role_slug}:{$models['second']['model']}:{$models['second']['id']}",
            ],
            2 => [
                "{$manager_role_slug}",
            ]
        ], $user->getRoles(), 'Expected assigned "manager" role to all models(="") and ids(=0)');

        $user->assignRole($role);
        $this->assertEquals([
            1 => [
                "{$role_slug}",
                "{$role_slug}:{$models['third']['model']}",
                "{$role_slug}:{$models['first']['model']}:{$models['first']['id']}",
                "{$role_slug}:{$models['second']['model']}:{$models['second']['id']}",
            ],
            2 => [
                "{$manager_role_slug}",
            ]
        ], $user->getRoles(), 'Expected assigned "admin" role to all models(="") and ids(=0)');

        $user->revokeRole($manager_role);
        $this->assertEquals([
            1 => [
                "{$role_slug}",
                "{$role_slug}:{$models['third']['model']}",
                "{$role_slug}:{$models['first']['model']}:{$models['first']['id']}",
                "{$role_slug}:{$models['second']['model']}:{$models['second']['id']}",
            ]
        ], $user->getRoles(), 'Expected revoked "manager" role');
    }


    private function createRolesPermissionUser()
    {
        $objRole        = new Role();
        $roleAttributes = [
            'name'        => 'Admin',
            'slug'        => str_slug('Admin role', config('laravel-auth.slug-separator')),
            'description' => 'Admin role descriptions.',
        ];
        $role           = $objRole->create($roleAttributes);

        $objPermission = new Permission();

        $permissionAttributes = [
            'name'        => 'post',
            'slug'        => [
                'create' => true,
                'view'   => true,
                'update' => true,
                'delete' => false,
            ],
            'description' => 'manage post permissions'
        ];
        $permission           = $objPermission->create($permissionAttributes);

        $role->syncPermissions($permission);
        $permissions = $role->getPermissions();

        $user           = new User();
        $user->username = 'Role test';
        $user->email    = 'role@test.com';
        $user->password = 'RoleTest';
        $user->save();

        return [ $role, $permissions, $user ];
    }


    /** @test */
    public function itCanCheckRolesForModelId()
    {
        list($role, $permissions, $user) = $this->createRolesPermissionUser();
        if ($user instanceof User) {
        }
        $models = [
            'first'  => [
                'model' => 'example_model',
                'id'    => 42,
            ],
            'second' => [
                'model' => 'example_model_2',
                'id'    => 43,
            ],
            'third'  => [
                'model' => 'example_model_3',
                'id'    => 0,
            ],
        ];

        $objRole           = new Role();
        $manager_role_slug = str_slug('Manager role', config('laravel-auth.slug-separator'));
        $roleAttributes    = [
            'name'        => 'Manager',
            'slug'        => $manager_role_slug,
            'description' => 'Manager role description.',
        ];
        $manager_role      = $objRole->create($roleAttributes);

        // Assign user role to three model entities.
        foreach ($models as $m) {
            $user->assignRole($role, $m['model'], $m['id']);
        }

        $this->assertEquals(true, $user->hasRole($role, $models['first']['model'], $models['first']['id']),
            'User has role on selected model & reference_id');

        $this->assertEquals(false, $user->hasRole($role, $models['first']['model'], $models['second']['id']),
            'User doesn\'t have role on selected model but different reference_id');

        $this->assertEquals(true, $user->hasRole($role, $models['third']['model'], $models['third']['id']),
            'User has role on selected model');

        $this->assertEquals(true, $user->hasRole($role, $models['third']['model'], $models['first']['id']),
            'User still have role on selected model but different reference_id, because reference_id == 0 means "all".');

        // Two roles.
        $user->assignRole($manager_role, $models['second']['model'], $models['second']['id']);

        $this->assertEquals(true, $user->hasRole([
            $role,
            $manager_role
        ], $models['second']['model'], $models['second']['id']), 'User have roles on both models/reference_ids');

        $this->assertEquals(false, $user->hasRole([
            $role,
            $manager_role
        ], $models['first']['model'], $models['first']['id']), 'User have both roles only on "second" model though.');

    }


    /** @test */
    public function itCanCheckRolePermissionsForModelId()
    {
        list($role, $permissions, $user) = $this->createRolesPermissionUser();
        // Assign user role to model entity & id.
        $example_model    = 'example_model';
        $example_model_id = 42;
        $user->assignRole($role, $example_model, $example_model_id);

        $this->assertEquals($user->getRoles(), [
            1 => [
                str_slug('Admin role', config('laravel-auth.slug-separator')).":{$example_model}:{$example_model_id}"
            ]
        ], 'Expected "admin" role assigned to model and id');
        // User have permissions on `model` = 'example_model', where `id` = 42.
        // 'post:example_model:42' => array(...)
        $this->assertEquals($user->getPermissions(),
            [ "post:{$example_model}:{$example_model_id}" => $permissions['post'] ],
            'Expected permissions from "admin" role assigned to model and id');

        // Permissions given to certain model and id.
        $this->assertTrue($user->can('create.post', $example_model, $example_model_id),
            'User have permissions on assigned model & id');
        // User doesn't have permissions on all models and ids.
        $this->assertFalse($user->can('create.post'), 'User don\'t have permissions on all models & ids');
        // User role must have exact model id.
        $this->assertFalse($user->can('create.post', $example_model), 'User don\'t have permissions on all model ids');

        $user->assignRole($role);
        $this->assertEquals($user->getPermissions(), [
            "post"                                      => $permissions['post'],
            "post:{$example_model}:{$example_model_id}" => $permissions['post'],
        ], 'User have permissions from "admin" role assigned to model & id and as global role');

    }


    /** @test */
    public function itCanCheckMultipleRolePermissionsForModelId()
    {
        list($role, $permissions, $user) = $this->createRolesPermissionUser();
        // Assign user role to model entity & id.
        $example_model    = 'example_model';
        $example_model_id = 42;
        $user->assignRole($role, $example_model, $example_model_id);

        // Multiple permissions.
        $able_or  = $permissions['post']['view'] || $permissions['post']['delete'];
        $able_and = $permissions['post']['view'] && $permissions['post']['delete'];
        $this->assertEquals($able_or, $user->can('view.post|delete.post', $example_model, $example_model_id),
            'Expected OR behavior when checking permissions');
        $this->assertEquals($able_and, $user->can('view.post,delete.post', $example_model, $example_model_id),
            'Expected AND behavior when checking permissions');

        // Comparing with AND operator by default.
        $this->assertEquals($able_and, $user->can([
            'view.post',
            'delete.post'
        ], $example_model, $example_model_id), 'Expected AND behavior by default');

        // However, if we set operator to 'OR'.
        $this->assertEquals($able_or, $user->can([
            'view.post',
            'delete.post'
        ], $example_model, $example_model_id, 'or'), 'Expected OR behavior when operator "OR" passed');

        // Same applies to string permissions.
        $this->assertEquals($able_and, $user->can('view.post|delete.post', $example_model, $example_model_id, 'and'),
            'Expected AND behavior when operator "AND" passed even with pipe as separator');
        $this->assertEquals($able_or, $user->can('view.post,delete.post', $example_model, $example_model_id, 'or'),
            'Expected OR behavior when operator "OR" passed even with comma as separator');

        // Using method.
        $this->assertEquals($permissions['post']['view'], $user->canViewPost($example_model, $example_model_id),
            'Expected correct permissions when using method with model & id as parameters');
        $this->assertEquals(! $permissions['post']['view'], $user->canViewPost($example_model),
            'Expected correct permissions when using method without parameters');
    }

}
