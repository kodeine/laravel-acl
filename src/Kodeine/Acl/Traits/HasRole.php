<?php namespace Kodeine\Acl\Traits;

use Kodeine\Acl\Models\Eloquent\Role;

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

        return $this->belongsToMany($model)->withPivot(['model', 'reference_id'])->withTimestamps();
    }

    /**
     * Get all roles.
     *
     * @return array
     */
    public function getRoles()
    {
        $this_roles = \Cache::remember(
            "acl.getRolesById_{$this->id}",
            config('acl.cacheMinutes'),
            function () {
                return $this->roles()->get();
            }
        );

        return $this->getRolesMap($this_roles);
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
     * @param string  $slug
     * @param string  $model
     * @param int     $reference_id
     * @param string  $operator
     *
     * @return bool
     */
    public function hasRole($slug, $model = '', $reference_id = 0, $operator = null)
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

            return $this->$call($slug, $roles, $model, $reference_id);
        }

        // single slug
        return $this->checkRole($slug, $roles, $model, $reference_id);
    }


    /**
     * @param        $role_slug
     * @param        $roles
     * @param string $model
     * @param int    $reference_id
     *
     * @return bool
     */
    private function checkRole($role_slug, $roles, $model = '', $reference_id = 0)
    {
        if ($role_slug instanceof Role) {
            $role_slug = $role_slug->slug;
        }

        $roles_exist = array_map(function ($role_slugs) use ($role_slug, $model, $reference_id) {
            $role_exist     = false;
            $role_model     = "{$role_slug}:{$model}";
            $role_reference = "{$role_slug}:{$model}:{$reference_id}";
            $checks           = [
                // If user have global role.
                $role_slug,
                // If user have role to this model.
                $role_model,
                // If user have role to this model and reference_id.
                $role_reference
            ];

            foreach ($checks as $c) {
                if (in_array($c, $role_slugs)) {
                    $role_exist = true;
                }
            }

            return (int)$role_exist;
        }, $roles);

        return (bool)array_sum($roles_exist);
    }

    /**
     * Assigns the given role to the user.
     *
     * @param collection|object|array|string|int $role
     * @param string                             $model
     * @param int                                $reference_id
     *
     * @return bool|array
     */
    public function assignRole($role, $model = '', $reference_id = 0)
    {
        $id = $this->id;
        return $this->mapArray($role, function ($role) use ($model, $reference_id, $id) {

            $roleId = $this->parseRoleId($role);

            if ( ! $this->roles->keyBy('id')->has($roleId) ) {
                $this->roles()->attach($roleId, [
                    'model'        => $model,
                    'reference_id' => $reference_id
                ]);

                // Reset caches.
                \Cache::forget("acl.getRolesById_{$id}");

                return $role;
            }

            return false;
        });
    }

    /**
     * Revokes the given role from the user.
     *
     * @param collection|object|array|string|int $role
     * @param string                             $model
     * @param int                                $reference_id
     *
     * @return bool|array
     */
    public function revokeRole($role, $model = '', $reference_id = 0)
    {
        $id = $this->id;
        return $this->mapArray($role, function ($role) use ($model, $reference_id, $id) {

            $roleId = $this->parseRoleId($role);

            $result = $this->roles()->newPivotStatementForId($roleId)
            // Laravel couldn't detach pivot records with provided constraints, but this should work:
                ->where('model', $model)
                ->where('reference_id', $reference_id)
                ->delete();
            // Update when Laravel would support this.
            //->detach($roleId, ['model' => $model, 'reference_id' => $reference_id]);

            // Reset caches.
            \Cache::forget("acl.getRolesById_{$id}");
            return $result;

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
     * @param        $slug
     * @param        $roles
     * @param string $model
     * @param int    $reference_id
     *
     * @return bool
     */
    protected function isWithAnd($slug, $roles, $model = '', $reference_id = 0)
    {
        foreach ($slug as $check) {
            if ( ! $this->checkRole($check, $roles, $model, $reference_id)) {
                return false;
            }
        }

        return true;
    }


    /**
     * @param        $slug
     * @param        $roles
     * @param string $model
     * @param int    $reference_id
     *
     * @return bool
     */
    protected function isWithOr($slug, $roles, $model = '', $reference_id = 0)
    {
        foreach ($slug as $check) {
            if ($this->checkRole($check, $roles, $model, $reference_id)) {
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
            $role         = substr($method, 2);
            $model        = empty($arguments[0]) ? ''   : $arguments[0];
            $reference_id = empty($arguments[1]) ? 0    : $arguments[1];
            $operator     = empty($arguments[2]) ? null : $arguments[2];

            return $this->hasRole($role, $model, $reference_id, $operator);
        }

        // Handle canDoSomething() methods
        if ( starts_with($method, 'can') and $method !== 'can' and ! starts_with($method, 'canWith') ) {
            $permission   = substr($method, 3);
            $permission   = snake_case($permission, '.');
            $model        = empty($arguments[0]) ? ''   : $arguments[0];
            $reference_id = empty($arguments[1]) ? 0    : $arguments[1];
            $operator     = empty($arguments[2]) ? null : $arguments[2];

            return $this->can($permission, $model, $reference_id, $operator);
        }

        return parent::__call($method, $arguments);
    }


    /**
     * Get sorted map from roles.
     * @return array
     */
    private function getRolesMap($roles)
    {
        if (empty($roles)) {
            return [];
        }
        $roles = $roles->map(function ($r) {
            return [
                'id'           => $r->id,
                'slug'         => $r->slug,
                'model'        => $r->pivot->model,
                'reference_id' => $r->pivot->reference_id,
            ];
        });
        $roles = $this->collectionAsArray($roles);

        $map = [];
        array_walk($roles, function ($role) use (&$map) {
            $id   = $role['id'];
            $slug = $role['slug'];
            if (empty($map[$id])) {
                $map[$id] = [];
            }
            $model_reference_keys = [
                $role['model'],
                // Do not count reference_id == 0 as model id.
                $role['reference_id'] ? $role['reference_id'] : ''
            ];
            $model_reference_keys = array_filter($model_reference_keys, function ($key) {
                return $key !== '';
            });
            $model_reference_key  = join(':', $model_reference_keys);

            $map[$id][] = $model_reference_key ? "{$slug}:{$model_reference_key}" : "{$slug}";
        });

        foreach ($map as $role_id => $roles) {
            sort($roles);
            $map[$role_id] = $roles;
        }

        return $map;
    }
}

$laravel = app();
if (version_compare($laravel::VERSION, '5.3', '<')) {
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
