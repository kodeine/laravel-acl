<?php namespace Kodeine\Acl\Traits;


trait HasRole
{
    use HasPermission;

    /*
    |----------------------------------------------------------------------
    | Role Trait Methods
    |----------------------------------------------------------------------
    |
    */

    /**
     * Users can have many roles.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function roles()
    {
        $model = config('acl.role', 'Kodeine\Acl\Models\Eloquent\Role');

        return $this->belongsToMany($model)->withTimestamps();
    }

    /**
     * Get all roles.
     *
     * @return array|null
     */
    public function getRoles()
    {
        return is_null($this->roles) ? [] : $this->roles->lists('slug');
    }

    /**
     * Checks if the user has the given role.
     *
     * @param  string $slug
     * @return bool
     */
    public function is($slug, $operator = null)
    {
        $operator = is_null(null) ? $this->parseOperator($slug) : $operator;

        $roles = $this->getRoles();
        $slug = $this->hasDelimiterToArray($slug);

        // array of slugs
        if ( is_array($slug) ) {

            if ( ! in_array($operator, ['and', 'or']) ) {
                $e = 'Invalid operator, available operators are "and", "or".';
                throw new \InvalidArgumentException($e);
            }

            $call = 'isWith' . ucwords($operator);

            return $this->$call($slug, $roles);
        }

        // single slug
        return in_array($slug, $roles);
    }

    /**
     * Assigns the given role to the user.
     *
     * @param  object|array|string|int $role
     * @return bool
     */
    public function assignRole($role)
    {
        return $this->mapArray($role, function ($role) {

            $roleId = $this->parseRoleId($role);

            if ( ! $this->roles->keyBy('id')->has($roleId) ) {
                $this->roles()->attach($roleId);

                return $role;
            }

            return false;
        });
    }

    /**
     * Revokes the given role from the user.
     *
     * @param  object|array|string|int $role
     * @return bool
     */
    public function revokeRole($role)
    {
        return $this->mapArray($role, function ($role) {

            $roleId = $this->parseRoleId($role);

            return $this->roles()->detach($roleId);
        });
    }

    /**
     * Syncs the given role(s) with the user.
     *
     * @param  object|array|string|int $roles
     * @return bool
     */
    public function syncRoles($roles)
    {
        $sync = [];
        $this->mapArray($roles, function ($role) use (&$sync) {

            $sync[] = $this->parseRoleId($role);

            return $sync;
        });

        return $this->roles()->sync($sync);
    }

    /**
     * Revokes all roles from the user.
     *
     * @return bool
     */
    public function revokeAllRoles()
    {
        return $this->roles()->detach();
    }

    /*
    |----------------------------------------------------------------------
    | Protected Methods
    |----------------------------------------------------------------------
    |
    */

    /**
     * @param $slug
     * @param $roles
     * @return bool
     */
    protected function isWithAnd($slug, $roles)
    {
        foreach ($slug as $check) {
            if ( ! in_array($check, $roles) ) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $slug
     * @param $roles
     * @return bool
     */
    protected function isWithOr($slug, $roles)
    {
        foreach ($slug as $check) {
            if ( in_array($check, $roles) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parses role id from object, array
     * or a string.
     *
     * @param object|array|string|int $role
     * @return int
     */
    protected function parseRoleId($role)
    {
        if ( is_string($role) || is_numeric($role) ) {

            $model = config('acl.role', 'Kodeine\Acl\Models\Eloquent\Role');
            $key = is_numeric($role) ? 'id' : 'slug';
            $alias = (new $model)->where($key, $role)->first();

            if ( ! is_object($alias) || ! $alias->exists ) {
                throw new \InvalidArgumentException('Specified role ' . $key . ' does not exists.');
            }

            $role = $alias->getKey();
        }

        $model = '\Illuminate\Database\Eloquent\Model';
        if ( is_object($role) && $role instanceof $model ) {
            $role = $role->getKey();
        }

        return (int) $role;
    }

    /*
    |----------------------------------------------------------------------
    | Magic Methods
    |----------------------------------------------------------------------
    |
    */

    /**
     * Magic __call method to handle dynamic methods.
     *
     * @param  string $method
     * @param  array  $arguments
     * @return mixed
     */
    public function __call($method, $arguments = [])
    {
        // Handle isRoleSlug() methods
        if ( starts_with($method, 'is') and $method !== 'is' and ! starts_with($method, 'isWith') ) {
            $role = substr($method, 2);

            return $this->is($role);
        }

        // Handle canDoSomething() methods
        if ( starts_with($method, 'can') and $method !== 'can' and ! starts_with($method, 'canWith') ) {
            $permission = substr($method, 3);
            $permission = snake_case($permission, '.');

            return $this->can($permission);
        }

        return parent::__call($method, $arguments);
    }
}
