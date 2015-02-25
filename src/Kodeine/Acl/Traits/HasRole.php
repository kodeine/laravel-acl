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
        $model = \Config::get('acl.role', 'Kodeine\Acl\Models\Eloquent\Role');
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
    public function is($slug)
    {
        $slug = strtolower($slug);

        return in_array($slug, $this->getRoles());
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

            if ( ! $this->roles->contains($roleId) ) {
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
     * Parses role id from object, array
     * or a string.
     *
     * @param object|array|string|int $role
     * @return int
     */
    protected function parseRoleId($role)
    {
        if ( is_string($role) || is_numeric($role) ) {

            $model = new \Kodeine\Acl\Models\Eloquent\Role;
            $key = ctype_alpha($role) ? 'slug' : 'id';
            $find = $model->where($key, $role)->first();

            if ( ! is_object($find) ) {
                throw new \InvalidArgumentException('Specified role ' . $key . ' does not exists.');
            }

            $role = $find->getKey();
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
        if ( starts_with($method, 'is') and $method !== 'is' ) {
            $role = substr($method, 2);

            return $this->is($role);
        }

        // Handle canDoSomething() methods
        if ( starts_with($method, 'can') and $method !== 'can' ) {
            $permission = substr($method, 3);

            return $this->can($permission);
        }

        return parent::__call($method, $arguments);
    }
}
