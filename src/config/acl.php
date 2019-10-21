<?php

return [

    /*
    /--------------------------------------------------------------------------
    / Custom Database Options
    /--------------------------------------------------------------------------
    /
    / If you want to add a prefix to your acl tables, or if you use a different
    / table for your user class, define it here
    */

    'db_prefix'   => '',
    'users_table' => '',

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

    /**
     * Cache Minutes
     * Set the minutes that roles and permissions will be cached.
     */
		
    'cacheMinutes' => 1,
];
