
# Kodeine/Laravel-ACL

[![Laravel](https://img.shields.io/badge/Laravel-~5.0-orange.svg?style=flat-square)](http://laravel.com)
[![Source](http://img.shields.io/badge/source-kodeine/laravel--acl-blue.svg?style=flat-square)](https://github.com/kodeine/laravel-acl/)
[![Build Status](http://img.shields.io/travis/kodeine/laravel--acl/master.svg?style=flat-square)](https://travis-ci.org/kodeine/laravel-acl)
[![License](http://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://tldrlegal.com/license/mit-license)
[![Total Downloads](http://img.shields.io/packagist/dt/kodeine/laravel-acl.svg?style=flat-square)](https://packagist.org/packages/kodeine/laravel-acl)

Laravel ACL adds role based permissions to built in Auth System of Laravel 5. ACL middleware protects routes and even crud controller methods.

# Table of Contents
* [Requirements](#requirements)
* [Getting Started](#getting-started)
* [Documentation](#documentation)
* [Roadmap](#roadmap)
* [Change Logs](#change-logs)
* [Contribution Guidelines](#contribution-guidelines)


# <a name="requirements"></a>Requirements

* This package requires PHP 5.5+

# <a name="getting-started"></a>Getting Started

1. Require the package in your `composer.json` and update your dependency with `composer update`:

```
"require": {
...
"kodeine/laravel-acl": "~1.0@dev",
...
},
```

2. Add the package to your application service providers in `config/app.php`.

```php
'providers' => [

'Illuminate\Foundation\Providers\ArtisanServiceProvider',
'Illuminate\Auth\AuthServiceProvider',
...
'Kodeine\Acl\AclServiceProvider',

],
```

3. Publish the package migrations to your application and run these with `php artisan migrate.

```
$ php artisan vendor:publish --provider="Kodeine\Acl\AclServiceProvider"
```

> **Use your own models.**
> Once you publish, it publishes the configuration file where you can define your own models which should extend to Acl models.

4. Add the middleware to your `app/Http/Kernel.php`.

```php
protected $routeMiddleware = [

....
'acl' => 'Kodeine\Acl\Middleware\HasPermission',

];
```

5. Add the HasRole trait to your `User` model.

```php
use Kodeine\Acl\Traits\HasRole;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
use Authenticatable, CanResetPassword, HasRole;
}
```

# <a name="documentation"></a>Documentation

Follow along the [Wiki](https://github.com/kodeine/laravel-acl/wiki) to find out more.

# <a name="roadmap"></a>Roadmap

Here's the TODO list for the next release (**2.0**).

* [ ] Refactoring the source code.
* [ ] Correct all issues.
* [ ] Adding cache to final user permissions.

# <a name="change-logs"></a>Change Logs

**September 22, 2016**
* [x] Added unit tests

**September 20, 2016**
* [x] Added support for Laravel 5.3

*September 19, 2016*
* [x] Added cache support to Roles and Permissions.

*June 14, 2015*
* [x] Added backward compatibility to l5.0 for lists() method.
* [x] Added [Blade Template Extensions](https://github.com/kodeine/laravel-acl/wiki/Blade-Extensions).

*March 28, 2015*
* [x] Added Role Scope to get all users having a specific role. e.g `User::role('admin')->get();` will list all users having `admin` role.

*March 7, 2015*
* [x] `is()` and `can()` methods now support comma for `AND` and pipe as `OR` operator. Or pass an operator as a second param. [more information](https://github.com/kodeine/laravel-acl/wiki/Validate-Permissions-and-Roles)
* [x] You can bind multiple permissions together so they inherit ones permission. [more information](https://github.com/kodeine/laravel-acl/wiki/Permissions-Inheritance)

# <a name="contribution-guidelines"></a>Contribution Guidelines

Support follows PSR-2 PHP coding standards, and semantic versioning.

Please report any issue you find in the issues page.
Pull requests are welcome.
