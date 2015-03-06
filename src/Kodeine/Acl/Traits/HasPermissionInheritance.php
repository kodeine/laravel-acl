<?php namespace Kodeine\Acl\Traits;


trait HasPermissionInheritance
{
    protected $cacheInherit;

    /*
    |----------------------------------------------------------------------
    | Permission Inheritance Trait Methods
    |----------------------------------------------------------------------
    |
    */

    public function getPermissionsInherited()
    {
        $permissions = $this->permissions->lists('slug', 'name');
        $inherits = $this->permissions->lists('inherit_id', 'name');

        foreach ($inherits as $name => $inherit_id) {
            if ( ! $inherit_id ) continue;

            // get inherit row from cache else query it.
            $inherit = $this->getCacheInherit($inherit_id);

            // add inherit row to cache.
            $this->setCacheInherit($inherit);

            // merge inheritances
            // rename permission name to inherited name.
            if ( $inherit->exists ) {
                if ( isset($permissions[$inherit->name]) ) {
                    $permissions[$inherit->name] = $permissions[$name] + $permissions[$inherit->name];
                } else {
                    $permissions[$inherit->name] = $permissions[$name] + $inherit->slug;
                }
                unset($permissions[$name]);
            }

        }

        return $permissions;
    }

    protected function getCacheInherit($inherit_id)
    {
        if ( isset($this->cacheInherit[$inherit_id]) ) {
            return $this->cacheInherit[$inherit_id];
        }

        $model = config('acl.permission', 'Kodeine\Acl\Models\Eloquent\Permission');
        return (new $model)->where('id', $inherit_id)->first();
    }

    protected function setCacheInherit($inherit)
    {
        return $this->cacheInherit[$inherit->getKey()] = $inherit;
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