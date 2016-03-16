# Blog Post 1 - Kodeine/Laravel-ACL - by jonesj38

[![Laravel](https://img.shields.io/badge/Laravel-~5.0-orange.svg?style=flat-square)](http://laravel.com)
[![Source](http://img.shields.io/badge/source-kodeine/laravel--acl-blue.svg?style=flat-square)](https://github.com/kodeine/laravel-acl/)
[![License](http://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://tldrlegal.com/license/mit-license)

Laravel ACL adds role based permissions to built in Auth System of Laravel 5. ACL middleware protects routes and crud controller methods.
# [Artifact 1]
# Installation Instructions for Kodeine/Laravel-ACL - by jonesj38

# Table of Contents
* [Requirements](#requirements)
* [Getting Started](#getting-started)
* [Adding Roles](#adding-roles) -- note: Artifact 2 starts here
* [Adding Permissions](#adding-permissions)
* [Setting Up Blade Directives](#setting-up-blade-directives)
* [Usage Examples for Blade Directives](#usage-examples-for-blade-directives)
* [Documentation](#documentation)


# <a name="requirements"></a>Requirements

* This package requires PHP 5.4+

# <a name="getting-started"></a>Getting Started

1. Run `composer require kodeine/laravel-acl`

2. Add the package to your application service providers in `config/app.php`.

```php
'providers' => [

Illuminate\Validation\ValidationServiceProvider::class,
Illuminate\View\ViewServiceProvider::class,
Kodeine\Acl\AclServiceProvider::class,
...
],
```

3. Clear the config cache, Publish the package migrations to your application, and run migrations.

```
$ php artisan config:clear
$ php artisan vendor:publish --provider="Kodeine\Acl\AclServiceProvider"
$ php artisan migrate
```

> **Use your own models.**
> Once you publish, it publishes the configuration file where you can define your own models which should extend to Acl models.

4. Add the middleware to your `app/Http/Kernel.php`.

```php
protected $routeMiddleware = [

....
'acl' => \Kodeine\Acl\Middleware\HasPermission::class,

];
```

5. Add the HasRole trait, Authenticatable contract, and CanResetPassword contract to your `User` model.

```php
use Kodeine\Acl\Traits\HasRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword, HasRole;
    ...
}
```

# [Artifact 2]

# <a name="adding-roles"></a>Adding Roles
## Using Seeder

First, create a new seeder using php artisan `php artisan create:seeder Roles_Table_Seeder`

Next, add roles to seeder

```php
use Illuminate\Database\Seeder;
use Kodeine\Acl\Models\Eloquent\Role;
use Kodeine\Acl\Models\Permission;

class Roles_Table_Seeder extends Seeder
{
    public function run()
    {
        $role = new Role()
        
        $roleAdmin = $role->create([
            'name'=> 'Admin',
            'slug'=> 'admin',     //NOTE: SLUG MUST BE LOWER-CASE!
            'description'=> 'Manage Administration privileges'
        ]);
        
        //Assign permissions to role (adding permissions is covered in the next section)
        $roleAdmin->assignPermission('mywork')
        ... //NOTE: You can assign multiple permissions to a role.
        
        //OR you can assign all permissions to a role
        $roleAdmin->assignPermission(Permission::all());
    }
}
```
# <a name="adding-permissions"></a>Adding Permissions
## Using Seeder

First, create a new seeder using php artisan `php artisan create:seeder Permissions_Table_Seeder`

Next, add permissions to seeder (adding permissions is a little bit different from adding roles. Also, it's important to note that permissions supercede roles)

```php
use Illuminate\Database\Seeder;
use Kodeine\Acl\Models\Permission;

class Permissions_Table_Seeder extends Seeder
{
    public function run()
    {
        $permission = new Permission()
        
        $permission->create([
            'name' => 'mywork',  //NOTE: THIS IS PART OF THE SLUG, SO IT MUST BE LOWER-CASE!
            'slug' => [
                'create'    => true,
                'view'      => true,
                'update'    => true,
                'delete'    => true
            ],
            'description'   => 'manage mywork permissions'
        ]);
        
        ...
    }
}
```

# <a name="setting-up-blade-directives"></a>Setting Up Blade Directives
## As of March 2016, the default blade directive location is not translating. For a temporary workaround, add the blade directives to your routes file. Once directives are added to routes file, they will work as expected.

Add blade directives to routes.php

```php

...

//BLADE ROLE AND PERMISSION DIRECTIVES
// role
Blade::directive('role', function ($expression) {
    return "<?php if (Auth::check() && Auth::User()->is{$expression}): ?>";
});

Blade::directive('endrole', function () {
    return "<?php endif; ?>";
});

// permission
Blade::directive('permission', function ($expression) {
    return "<?php if (Auth::check() && Auth::User()->can{$expression}): ?>";
});

Blade::directive('endpermission', function () {
    return "<?php endif; ?>";
});
```

# <a name="usage-examples-for-blade-directives"></a>Usage Examples for Blade Directives

## Role

Simply wrap that part of the html that you want to be under a  role permission with a role blade directive.

### MyWork Button is visible

### Wrapping MyWork button in a role
NOTE: you can add multiple roles by piping slugs together within the single-quotes e.g. @role('admin|...|...|')...@endrole
```html
@role('admin')<li><a href="{{ url('/mywork') }}">My Work</a></li>@endrole
```

### Result, MyWork button is gone

### After logging in as admin

## Permission

Simply wrap that part of the html that you want to be under a permission with a permission blade directive

### MyWork Button is visible

### Wrapping MyWork button in a permission
NOTE: you can add multiple permissions by piping slugs together within the single-quotes e.g. @permission('view.mywork|update.mywork|...')...@endpermission
```html
@permission('view.mywork')<li><a href="{{ url('/mywork') }}">My Work</a></li>@endpermission
```

### Result, MyWork button is gone

### After logging in as admin, Which has the view.mywork permission

# <a name="documentation"></a>Documentation

Check out the [Wiki](https://github.com/kodeine/laravel-acl/wiki) to find out more.