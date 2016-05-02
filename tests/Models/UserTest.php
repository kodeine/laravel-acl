<?php namespace Kodeine\Acl\Tests\Models;

use Kodeine\Acl\Models\Eloquent\Permission;
use Kodeine\Acl\Models\Eloquent\Role;
use Kodeine\Acl\Models\Eloquent\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class UserTest extends ModelsTest
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
    public function it_can_be_instantiated()
    {
        $expectations = [
            \Illuminate\Database\Eloquent\Model::class,
            \Kodeine\Acl\Models\Eloquent\User::class,
        ];

        foreach ($expectations as $expected) {
            $this->assertInstanceOf($expected, $this->userModel);
        }
    }

    /** @test */
    public function it_can_attach_role()
    {
        $objRole = new Role();
        $role = $objRole->create([
            'name'        => 'Admin',
            'slug'        => str_slug('Admin role', config('laravel-auth.slug-separator')),
            'description' => 'Admin role descriptions.',
        ]);
        
        $user = new User();
        $user->username = 'Role test';
        $user->email = 'role@test.com';
        $user->password = 'RoleTest';
        $user->save();
        
        $user->syncRoles(str_slug('Admin role', config('laravel-auth.slug-separator')));
        
        $this->assertEquals($user->getRoles(), [str_slug('Admin role', config('laravel-auth.slug-separator'))]);
        
    }

    /** @test */
    public function it_can_attach_role_and_permission()
    {
        $objRole = new Role();
        $roleAttributes = [
            'name'        => 'Admin',
            'slug'        => str_slug('Admin role', config('laravel-auth.slug-separator')),
            'description' => 'Admin role descriptions.',
        ];
        $role = $objRole->create($roleAttributes);
        
        $objPermission = new Permission();
        $permissionAttributes = [
            'name'        => 'post',
            'slug'        => [
                'create'     => true,
                'view'       => true,
                'update'     => true,
                'delete'     => true,
            ],
            'description' => 'manage post permissions'
        ];
        $permission = $objPermission->create($permissionAttributes);
        
        $role->syncPermissions($permission);
        
        $user = new User();
        $user->username = 'Role test';
        $user->email = 'role@test.com';
        $user->password = 'RoleTest';
        $user->save();
        $user->syncRoles($role);
   
        $this->assertEquals($user->getRoles(), [str_slug('Admin role', config('laravel-auth.slug-separator'))]);
        $this->assertEquals($user->getPermissions(), ['post' => $permissionAttributes['slug']]);
    }
}
