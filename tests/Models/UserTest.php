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
    public function setUp(): void
    {
        parent::setUp();

        $this->userModel = new User;
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->userModel);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Test Functions
     | ------------------------------------------------------------------------------------------------
     */
    /** @test */
    public function itCanBeInstantiated()
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
    public function itCanAttachRole()
    {
        $objRole = new Role();
        $role = $objRole->create([
            'name'        => 'Admin',
            'slug'        => $this->str_slug('Admin role', config('laravel-auth.slug-separator')),
            'description' => 'Admin role descriptions.',
        ]);
        
        $user = new User();
        $user->name = 'Role test';
        $user->email = 'role@test.com';
        $user->password = 'RoleTest';
        $user->save();
        
        $user->syncRoles($this->str_slug('Admin role', config('laravel-auth.slug-separator')));
        
        $this->assertEquals($user->getRoles(), [ 1 => $this->str_slug('Admin role', config('laravel-auth.slug-separator'))]);
    }

    /** @test */
    public function itCanAttachRoleAndPermission()
    {
        $objRole = new Role();
        $roleAttributes = [
            'name'        => 'Admin',
            'slug'        => $this->str_slug('Admin role', config('laravel-auth.slug-separator')),
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
        $user->name = 'Role test';
        $user->email = 'role@test.com';
        $user->password = 'RoleTest';
        $user->save();
        $user->syncRoles($role);
   
        $this->assertEquals($user->getRoles(), [ 1 => $this->str_slug('Admin role', config('laravel-auth.slug-separator'))]);
        $this->assertEquals($user->getPermissions(), ['post' => $permissionAttributes['slug']]);
    }

    /** @test */
    public function cacheTest()
    {
        $objRole = new Role();
        $roleAttributes = [
            'name'        => 'Admin',
            'slug'        => $this->str_slug('Admin role', config('laravel-auth.slug-separator')),
            'description' => 'Admin role descriptions.',
        ];
        $role = $objRole->create($roleAttributes);
        
        $objPermission = new Permission();
        $permissionAttributes = [
            'name'        => 'cache',
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
        $user->name = 'Cache test';
        $user->email = 'cache@test.com';
        $user->password = 'CacheTest';
        $user->save();
        $user->syncRoles($role);
        
        \DB::connection()->enableQueryLog();
        $user->getPermissions();
        $queriesNoCache = count(\DB::getQueryLog());
        \DB::flushQueryLog();
        
        \DB::connection()->enableQueryLog();
        $user->getPermissions();
        $queriesCache = count(\DB::getQueryLog());
        
        $this->assertGreaterThan($queriesCache, $queriesNoCache);
    }
}
