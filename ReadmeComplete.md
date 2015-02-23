# Role based permissions to Laravel 5.
[![Laravel](https://img.shields.io/badge/Laravel-~5.0-orange.svg?style=flat-square)](http://laravel.com)
[![Source](http://img.shields.io/badge/source-kodeine/laravel--acl-blue.svg?style=flat-square)](https://github.com/kodeine/laravel-acl/)
[![Build Status](http://img.shields.io/travis/kodeine/laravel--acl/master.svg?style=flat-square)](https://travis-ci.org/kodeine/laravel-acl)
[![License](http://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://tldrlegal.com/license/mit-license)

Laravel ACL adds role based permissions to your Laravel 5 applications.
Middleware helps protect routes based on roles and even protects crud methods.
Follow along the documentation to find out more.

## Installation

#### Composer

Add this to your composer.json file, in the require object:

```javascript
"kodeine/laravel-acl": "dev-master"
```

After that, run composer install to install the package.

#### Migration Table Schema
```php
```
## Configuration


#### Model Setup

Next, add the `HasRole`, `HasPermission` traits to your User model:

```php
use Kodeine\ACL\HasRole;
use Kodeine\ACL\HasPermission;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword, HasRole, HasPermission;
}
```

## Working With ACL

#### Create Role

Lets create your first roles.

```php
$role = new Role();
$roleAdmin = $role->create([
    'name' => 'Administrator',
    'slug' => 'administrator',
    'description'=>'manage administration privileges'
]);

$role = new Role();
$roleModerator = $role->create([
    'name' => 'Moderator',
    'slug' => 'moderator',
    'description'=>'manage moderator privileges'
]);
```

#### Assign Role(s) To User

Lets assign created roles to a User.

> **Note:** You can pass an object, an array, role->id or just a slug.

```php
$user = User::find(1);
// by object
$user->assignRole($roleAdmin);
// or by id
$user->assignRole($roleAdmin->id);
// or by just a slug
$user->assignRole('administrator');
```

Or `multiple roles` at once:

```php
// multiple roles in an array
$user->assignRole(array($roleAdmin, $roleModerator));
// or mutiple role slugs separated by comma or pipe.
$user->assignRole('administrator, moderator');
```

> **Note:** The system will throw an exception if role does not exists.

#### Revoke Role(s) From User

Similarly, you may revoke roles from user

> **Note:** You can pass an object, an array, role->id or just a slug.

```php
$user = User::find(1);
// by object
$user->revokeRole($roleAdmin);
// or by id
$user->revokeRole($roleAdmin->id);
// or by just a slug
$user->revokeRole('administrator');
```

Or `multiple roles` at once:

```php
// multiple roles in an array
$user->revokeRole(array($roleAdmin, $roleModerator));
// or mutiple role slugs separated by comma or pipe.
$user->revokeRole('administrator, moderator');
```

> **Note:** The system will throw an exception if role does not exists.

#### Sync Role(s) To User

You can pass an array of role objects,ids or slugs to sync them to a user.

```php
$user->syncRoles([1,2,3]);
$user->syncRoles('administrator, moderator');
$user->syncRoles((array($roleAdmin, $roleModerator));
```

> **Note:** The system will throw an exception if role does not exists.

#### Revoke All User Roles

You can revoke all roles assigned to a user.

```php
$user->revokeAllRoles();
```

#### Get User Roles

Get roles assigned to a user.

```php
$user = User::first();
$user->getRoles();
```



#### Create Permissions

Lets create your first permission.

```php
$permission = new Permission();
$permUser = $permission->create([ 
    'name'        => 'user',
    'slug'        => [          // pass an array of permissions.
        'create'     => true,
        'view'       => true,
        'update'     => true,
        'delete'     => true,
        'view.phone' => true
    ],
    'description' => 'manage user permissions'
]);

$permission = new Permission();
$permPost = $permission->create([ 
    'name'        => 'post',
    'slug'        => [          // pass an array of permissions.
        'create'     => true,
        'view'       => true,
        'update'     => true,
        'delete'     => true,
    ],
    'description' => 'manage post permissions'
]);
```

#### Assign Permission(s) to Role

Lets assign created permissions to a Role.

> **Note:** You can pass an object, an array, role->id or just name.

```php
$roleAdmin = Role::first(); // administrator
// permission as an object
$roleAdmin->assignPermission($permUser);
// as an id
$roleAdmin->assignPermission($permUser->id);
// or by name
$roleAdmin->assignPermission('user');
```

Or `multiple permissions` at once:

```php
// multiple permissions in an array
$roleAdmin->assignPermission(array($permUser, $permPost->id));
// or mutiple role slugs separated by comma or pipe.
$roleAdmin->assignPermission('user, 'post');
```

> **Note:** The system will throw an exception if permission does not exists.

#### Revoke Permission(s) from Role

Similarly, you may revoke permissions from a role

> **Note:** You can pass an object, an array, permission->id or just a name.

```php
$roleAdmin = Role::first(); // administrator
// permission as an object
$roleAdmin->revokePermission($permUser);
// as an id
$roleAdmin->revokePermission($permUser->id);
// or by name
$roleAdmin->revokePermission('user');
```

Or `multiple permissions` at once:

```php
// multiple permissions in an array
$roleAdmin->assignPermission(array($permUser, $permPost->id));
// or mutiple role slugs separated by comma or pipe.
$roleAdmin->assignPermission('user, 'post');
```

> **Note:** The system will throw an exception if role does not exists.

#### Sync Role Permissions

You can pass an array of role objects,ids or slugs to sync them to a user.

```php
$roleAdmin->syncPermissions([1,2,3]);
$roleAdmin->syncPermissions('user, post');
$roleAdmin->syncPermissions((array($permUser, $permPost));
```

> **Note:** The system will throw an exception if role does not exists.

#### Revoke All Permissions From A Role

You can revoke all roles assigned to a user.

```php
$roleAdmin->revokeAllPermissions();
```

#### Get Role Permissions

Get permissions assigned to a role.

```php
$roleAdmin->getPermissions();
```

#### Get User Permissions

Get permissions assigned to a user.

```php
$user = User::first();
$user->getPermissions();
```


## Validate Permissions and Roles.

#### Validate Roles

Roles can be validated by calling `is` method.

> Validate based on User

```php
$user = User::first();
$user->is('administrator');
$user->isAdministrator();	// using method
```

#### Validate Permissions

Permissions can be validated by calling `can` method.

> Validate based on a Role

```php
$admin = Role::first();	// administrator
$admin->can('view.user');
$admin->canViewUser();	// using method.

// by an array
$admin->can(array('view.user', 'edit.user'));

// multiple validations by comma, pipe separated
$admin->can('view.user, edit.user, view.admin, delete.admin');
```

> Validate based on User

```php
$user = User::first();
$user->can('delete.user');
$user->canDeleteUser();	// using method
```


