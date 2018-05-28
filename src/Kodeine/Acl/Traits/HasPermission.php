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

        $permissions = \Cache::remember(
            'acl.getPermissionsById_'.$this->id,
            config('acl.cacheMinutes'),
            function () {
                $permissions = [];
                // permissions based on role.
                // more permissive permission wins
                // if user has multiple roles we keep
                // true values.
                foreach ($this->roles as $role) {
                    foreach ($role->getPermissions() as $slug => $array) {
                        if ( array_key_exists($slug, $permissions) ) {
                            foreach ($array as $clearance => $value) {
                                ! $value ?: $permissions[$slug][$clearance] = true;
                            }
                        } else {
                            $permissions = array_merge($permissions, [$slug => $array]);
                        }
                    }
                }

                // user permissions that override roles ones.
                foreach ($this->getPermissionsInherited() as $slug => $array) {
                    if ( array_key_exists($slug, $permissions) ) {
                        foreach ($array as $clearance => $value) {
                            $permissions[$slug][$clearance] = $value;
                        }
                    } else {
                        $permissions = array_merge($permissions, [$slug => $array]);
                    }
                }
                return $permissions;
            }
        );

        return $permissions;
    }

    /**
     * Check if User has the given permission.
     *
     * @param  string $permission
     * @param  string $operator
     * @return bool
     */
    public function can($permission, $operator = null)
    {
        // user permissions including
        // all of user role permissions
        $merge =  \Cache::remember(
            'acl.getMergeById_'.$this->id,
            config('acl.cacheMinutes'),
            function () {
                return $this->getPermissions();
            }
        );

        // lets call our base can() method
        // from role class. $merge already
        // has user & role permissions
        $model = config('acl.role', 'Kodeine\Acl\Models\Eloquent\Role');

        return (new $model)->can($permission, $operator, $merge);
    }

    /**
     * Assigns the given permission to the user.
     *
     * @param  collection|object|array|string|int $permission
     * @return bool
     */
    public function assignPermission($permission)
    {
        $this->deletePermissionCache();

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
     * @param  collection|object|array|string|int $permission
     * @return bool
     */
    public function revokePermission($permission)
    {
        $this->deletePermissionCache();

        return $this->mapArray($permission, function ($permission) {

            $permissionId = $this->parsePermissionId($permission);

            return $this->permissions()->detach($permissionId);
        });
    }

    /**
     * Syncs the given permission(s) with the user.
     *
     * @param  collection|object|array|string|int $permissions
     * @return bool
     */
    public function syncPermissions($permissions)
    {
        $sync = [];
        $this->mapArray($permissions, function ($permission) use (&$sync) {

            $sync[] = $this->parsePermissionId($permission);

            return $sync;
        });
        $this->deletePermissionCache();
        return $this->permissions()->sync($sync);
    }

    /**
     * Revokes all permissions from the user.
     *
     * @return bool
     */
    public function revokeAllPermissions()
    {
        $this->deletePermissionCache();
        return $this->permissions()->detach();
    }

    /*
    |----------------------------------------------------------------------
    | Protected Methods
    |----------------------------------------------------------------------
    |
    */


    /**
     * Delete cache for this traits.
     *
     * @return null
     */
    protected function deletePermissionCache()
    {
        \Cache::forget('acl.getPermissionsById_'.$this->id);
        \Cache::forget('acl.getMergeById_'.$this->id);
    }

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