<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Model Definitions
    |--------------------------------------------------------------------------
    |
    | If you want to use your own model and extend it to package's model. You
    | can define your model here.
    */

    'role'       => Kodeine\Acl\Models\Eloquent\Role::class,
    'permission' => Kodeine\Acl\Models\Eloquent\Permission::class,

    /*
    |--------------------------------------------------------------------------
    | Most Permissive Wins Right
    |--------------------------------------------------------------------------
    |
    | If you have multiple permission aliases assigned, each alias has a common
    | permission, view.house => false, but one alias has it set to true. If this
    | right is enabled, true value wins the race, ie the most permissive wins.
    */

    'most_permissive_wins' => false,

    /*
    |--------------------------------------------------------------------------
    | Database Table Prefix
    |--------------------------------------------------------------------------
    |
    | If you want to add a prefix to the table names, define it here.  By default,
    | no prefix is applied.
    */

    'db_prefix' => null,

    /*
    |--------------------------------------------------------------------------
    | User Table
    |--------------------------------------------------------------------------
    |
    | Most of the time the users are stored in a table named users.  If this is
    | not true for your app, define the user table name here.
    */

    'users_table' => 'users',
];
