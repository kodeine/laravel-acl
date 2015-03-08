<?php namespace Kodeine\Acl\Traits;

use Kodeine\Acl\Helper\Helper;

trait HasPermission
{
    use HasUserPermission, HasPermissionInheritance, Helper;

    /*
    |----------------------------------------------------------------------
    | Permission Trait Methods
    |----------------------------------------------------------------------
    |
    */

    /**
     * Users can have many permissions overridden from permissions.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function permissions()
    {
        $model = config('acl.permission', 'Kodeine\Acl\Models\Eloquent\Permission');

        return $this->belongsToMany($model)->withTimestamps();
    }

    /**
     * Get all user permissions including
     * user all role permissions.
     *
     * @return array|null
     */
    public function getPermissions()
    {
        // user permissions overridden from role.
        $permissions = $this->getPermissionsInherited();

        // permissions based on role.
        foreach ($this->roles as $role) {
            $permissions = $permissions + $role->getPermissions();
        }

        return $permissions;
    }

    /**
     * Check if user has the given permission.
     *
     * @param  string $permission
     * @return bool
     */
    public function can($permission)
    {
        // user permissions including
        // all of user role permissions
        $merge = $this->getPermissions();

        // get first role and use can method
        // $merge already has all user role
        // permissions.
        $role = $this->roles->first();

        return ! is_null($role) && $role->can($permission, null, $merge);
    }

    /**
     * Assigns the given permission to the user.
     *
     * @param  object|array|string|int $permission
     * @return bool
     */
    public function assignPermission($permission)
    {
        return $this->mapArray($permission, function ($permission) {

            $permissionId = $this->parsePermissionId($permission);

            if ( ! $this->permissions->keyBy('id')->has($permissionId) ) {
                $this->permissions()->attach($permissionId);

                return $permission;
            }

            return false;
        });
    }

    /**
     * Revokes the given permission from the user.
     *
     * @param  object|array|string|int $permission
     * @return bool
     */
    public function revokePermission($permission)
    {
        return $this->mapArray($permission, function ($permission) {

            $permissionId = $this->parsePermissionId($permission);

            return $this->permissions()->detach($permissionId);
        });
    }

    /**
     * Syncs the given permission(s) with the user.
     *
     * @param  object|array|string|int $permissions
     * @return bool
     */
    public function syncPermissions($permissions)
    {
        $sync = [];
        $this->mapArray($permissions, function ($permission) use (&$sync) {

            $sync[] = $this->parsePermissionId($permission);

            return $sync;
        });

        return $this->permissions()->sync($sync);
    }

    /**
     * Revokes all permissions from the user.
     *
     * @return bool
     */
    public function revokeAllPermissions()
    {
        return $this->permissions()->detach();
    }

    /*
    |----------------------------------------------------------------------
    | Protected Methods
    |----------------------------------------------------------------------
    |
    */


    /**
     * Parses permission id from object or array.
     *
     * @param object|array|int $permission
     * @return mixed
     */
    protected function parsePermissionId($permission)
    {
        if ( is_string($permission) || is_numeric($permission) ) {

            $model = config('acl.permission', 'Kodeine\Acl\Models\Eloquent\Permission');
            $key = is_numeric($permission) ? 'id' : 'name';
            $alias = (new $model)->where($key, $permission)->first();

            if ( ! is_object($alias) || ! $alias->exists ) {
                throw new \InvalidArgumentException('Specified permission ' . $key . ' does not exists.');
            }

            $permission = $alias->getKey();
        }

        $model = '\Illuminate\Database\Eloquent\Model';
        if ( is_object($permission) && $permission instanceof $model ) {
            $permission = $permission->getKey();
        }

        return (int) $permission;
    }
}
