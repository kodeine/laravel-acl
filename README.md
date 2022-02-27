
# Kodeine/Laravel-ACL

[![Laravel](https://img.shields.io/badge/Laravel-~8.0-green.svg?style=flat-square)](http://laravel.com)
[![Source](http://img.shields.io/badge/source-kodeine/laravel--acl-blue.svg?style=flat-square)](https://github.com/kodeine/laravel-acl/)
[![Build Status](https://img.shields.io/travis/kodeine/laravel-acl/master?style=flat-square)](https://travis-ci.org/kodeine/laravel-acl)
[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://tldrlegal.com/license/mit-license)
[![Total Downloads](https://img.shields.io/packagist/dt/kodeine/laravel-acl.svg?style=flat-square)](https://packagist.org/packages/kodeine/laravel-acl)


Laravel ACL adds role based permissions to built in Auth System of Laravel 9.0+. ACL middleware protects routes and even crud controller methods.

# Table of Contents
* [Requirements](#requirements)
* [Getting Started](#getting-started)
* [Documentation](#documentation)
* [Roadmap](#roadmap)
* [Change Logs](#change-logs)
* [Contribution Guidelines](#contribution-guidelines)


# <a name="requirements"></a>Requirements

* Version 2.x of this package requires PHP 7.2+ and Laravel 6.0+ 
* Version 1.x requires PHP 5.6+ and Laravel 5.0+

# <a name="getting-started"></a>Getting Started

Install the package using composer 

```
composer require kodeine/laravel-acl
```

If you need to support Laravel 5.x, make sure to install version 1.x

```
composer require kodeine/laravel-acl "^1.0"
```

2. If you are using Laravel before version 5.4, manually register the service provider in your config/app.php file 

```php
'providers' => [
    'Illuminate\Foundation\Providers\ArtisanServiceProvider',
    'Illuminate\Auth\AuthServiceProvider',
    ...
    'Kodeine\Acl\AclServiceProvider',
],
```

3. Publish the package configuartion files and add your own models to the list of ACL models"

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

6. Run the migrations to generate your roles and permissions tables

Please note that if you are upgrading to 6.0 from a previous version, the default column type for the id on the users table has changed. On certain databases foreign keys can only be defined with matching column types. As such, you will need to change the id column on your users table to bigInteger in to user this package. 

```
php artisan migrate
```

# <a name="documentation"></a>Documentation

Follow along the [Wiki](https://github.com/kodeine/laravel-acl/wiki) to find out more.

# <a name="roadmap"></a>Roadmap

Here's the TODO list for the next release.

* [ ] Refactoring the source code.
* [ ] Correct all issues.
* [ ] Adding cache to final user permissions.

# <a name="change-logs"></a>Change Logs

**September 14 2019**
* [x] Updated the readme to reflect new major release

**September 13, 2019**
* [x] Added support for Laravel 6

*September 22, 2016**
* [x] Added unit tests

*September 20, 2016**
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
