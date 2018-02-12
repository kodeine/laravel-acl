<?php namespace Kodeine\Acl\Traits;

/**
 * Class HasRoleImplementation
 * @package Kodeine\Acl\Traits
 *
 * @method static Builder|Collection|\Eloquent role($role, $column = null)
 */
trait HasRoleImplementation
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
     * @return array
     */
    public function getRoles()
    {
        $this_roles = \Cache::remember(
            'acl.getRolesById_'.$this->id,
            config('acl.cacheMinutes'),
            function () {
                return $this->roles;
            }
        );

        $slugs = method_exists($this_roles, 'pluck') ? $this_roles->pluck('slug','id') : $this_roles->lists('slug','id');
        return is_null($this_roles)
            ? []
            : $this->collectionAsArray($slugs);
    }

    /**
     * Scope to select users having a specific
     * role. Role can be an id or slug.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|string $role
     * @param string $column
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRole($query, $role, $column = null)
    {
        if (is_null($role)) {
            return $query;
        }

        return $query->whereHas('roles', function ($query) use ($role, $column) {
            if (is_array($role)) {
                $queryColumn = !is_null($column) ? $column : 'roles.slug';

                $query->whereIn($queryColumn, $role);
            } else {
                $queryColumn = !is_null($column) ? $column : (is_numeric($role) ? 'roles.id' : 'roles.slug');

                $query->where($queryColumn, $role);
            }
        });
    }

    /**
     * Checks if the user has the given role.
     *
     * @param  string $slug
     * @return bool
     */
    public function hasRole($slug, $operator = null)
    {
        $operator = is_null($operator) ? $this->parseOperator($slug) : $operator;

        $roles = $this->getRoles();
        $roles = $roles instanceof \Illuminate\Contracts\Support\Arrayable ? $roles->toArray() : (array) $roles;
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
     * @param  collection|object|array|string|int $role
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
     * @param  collection|object|array|string|int $role
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
     * @param  collection|object|array|string|int $roles
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
    public function __call($method, $arguments)
    {
        // Handle isRoleSlug() methods
        if ( starts_with($method, 'is') and $method !== 'is' and ! starts_with($method, 'isWith') ) {
            $role = substr($method, 2);

            return $this->hasRole($role);
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

$laravel = app();
if ($laravel instanceof \Illuminate\Foundation\Application && version_compare($laravel::VERSION, '5.3', '<')) {
    trait HasRole
    {
        use HasRoleImplementation {
            hasRole as is;
        }
    }
} else {
    trait HasRole
    {
        use HasRoleImplementation;
    }
}
