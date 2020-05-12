<?php

namespace Kodeine\Acl\Helper;

class Config
{
    public static function usersTableName()
    {
        return config('acl.users_table') === '' ? 'users' : config('acl.users_table');
    }
}