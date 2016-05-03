<?php namespace Kodeine\Acl\Tests\Models;

use Kodeine\Acl\Models\Eloquent\Permission;
use Kodeine\Acl\Models\Eloquent\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PermissionTest extends ModelsTest
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var Permission */
    protected $permissionModel;

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp()
    {
        parent::setUp();

        $this->migrate();

        $this->permissionModel = new Permission;
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->permissionModel);
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
            \Kodeine\Acl\Models\Eloquent\Permission::class,
        ];

        foreach ($expectations as $expected) {
            $this->assertInstanceOf($expected, $this->permissionModel);
        }
    }

    /** @test */
    public function itHasRelationships()
    {
        $rolesRelationship = $this->permissionModel->roles();

        $this->assertInstanceOf(BelongsToMany::class, $rolesRelationship);

        $this->assertInstanceOf(
            \Kodeine\Acl\Models\Eloquent\Role::class,
            $rolesRelationship->getRelated()
        );
    }

    /** @test */
    public function itCanCreate()
    {
        $attributes = [
            'name'        => 'Create users',
            'slug'        => 'auth.users.create',
            'description' => 'Allow to create users',
        ];

        $permission = $this->permissionModel->create($attributes);

        $this->assertEquals($attributes['name'], $permission->name);
        $this->assertEquals([$attributes['slug'] => true], $permission->slug);
        $this->assertEquals($attributes['description'], $permission->description);

        $this->seeInDatabase('permissions', [
            'name'        => 'Create users',
            'description' => 'Allow to create users',
        ]);
    }

    /** @test */
    public function itCanUpdate()
    {
        $attributes = [
            'name'        => 'Create users',
            'slug'        => 'auth.users.create',
            'description' => 'Allow to create users',
        ];

        $permission        = $this->permissionModel->create($attributes);
        $updatedAttributes = [
            'name'        => 'Update users',
            'slug'        => 'auth.users.update',
            'description' => 'Allow to update users',
        ];

        $this->seeInDatabase('permissions', [
            'name'        => 'Create users',
            'description' => 'Allow to create users',
        ]);

        $permission->update($updatedAttributes);

        $this->seeInDatabase('permissions', [
            'name'        => 'Update users',
            'description' => 'Allow to update users',
        ]);
        $this->dontSeeInDatabase('permissions', $attributes);
    }

    /** @test */
    public function itCanDelete()
    {
        $attributes = [
            'name'        => 'Create users',
            'slug'        => 'auth.users.create',
            'description' => 'Allow to create users',
        ];

        $permission = $this->permissionModel->create($attributes);

        $this->seeInDatabase('permissions', [
            'name'        => 'Create users',
            'description' => 'Allow to create users',
        ]);

        $permission->delete();

        $this->dontSeeInDatabase('permissions', $attributes);
    }
}
