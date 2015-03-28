
# Role based permissions for Laravel 5.

[![Laravel](https://img.shields.io/badge/Laravel-~5.0-orange.svg?style=flat-square)](http://laravel.com)
[![Source](http://img.shields.io/badge/source-kodeine/laravel--acl-blue.svg?style=flat-square)](https://github.com/kodeine/laravel-acl/)
[![Build Status](http://img.shields.io/travis/kodeine/laravel--acl/master.svg?style=flat-square)](https://travis-ci.org/kodeine/laravel-acl)
[![License](http://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://tldrlegal.com/license/mit-license)
[![Total Downloads](http://img.shields.io/packagist/dt/kodeine/laravel-acl.svg?style=flat-square)](https://packagist.org/packages/kodeine/laravel-acl)

Laravel ACL adds role based permissions to built in Auth System of Laravel 5. Acl middleware protects routes and even crud controller methods.


#### Documentation

Follow along the [Wiki](https://github.com/kodeine/laravel-acl/wiki) to find out more.

#### Recent Changes

> **March 28, 2015.**

* Added Role Scope to get all users having a specific role. e.g `User::role('admin')->get();` will list all users having `admin` role.

> March 7, 2015.


* `is()` and `can()` methods now support comma for `AND` and pipe as `OR` operator. Or pass an operator as a second param. [more here...](https://github.com/kodeine/laravel-acl/wiki/Validate-Permissions-and-Roles)
* Permissions inheritance
    * You can bind multiple permissions together so they inherit ones permission. [more here...](https://github.com/kodeine/laravel-acl/wiki/Permissions-Inheritance)

#### Contribution guidelines

Support follows PSR-1 and PSR-4 PHP coding standards, and semantic versioning.

Please report any issue you find in the issues page.
Pull requests are welcome.
