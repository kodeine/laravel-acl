<?php namespace Kodeine\Acl\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Kodeine\Acl\Traits\HasPermission;

class Role extends Model
{
    use HasPermission;

    /**
     * The attributes that are fillable via mass assignment.
     *
     * @var array
     */
    protected $fillable = ['name', 'slug', 'description'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'roles';

    /**
     * Roles can belong to many users.
     *
     * @return Model
     */
    public function users()
    {
        return $this->belongsToMany(config('auth.model'))->withTimestamps();
    }

    /**
     * List all permissions
     *
     * @return mixed
     */
    public function getPermissions()
    {
        return $this->getPermissionsInherited();
    }

    /**
     * Checks if the role has the given permission.
     *
     * @param string $permission
     * @param array  $mergePermissions
     * @return bool
     */
    public function can($permission, $operator = null, $mergePermissions = [])
    {
        $operator = is_null(null) ? $this->parseOperator($permission) : $operator;

        $permission = $this->hasDelimiterToArray($permission);
        $permissions = $this->getPermissions() + $mergePermissions;

        // make permissions to dot notation.
        // create.user, delete.admin etc.
        $permissions = $this->toDotPermissions($permissions);

        // validate permissions array
        if ( is_array($permission) ) {

            if ( ! in_array($operator, ['and', 'or']) ) {
                $e = 'Invalid operator, available operators are "and", "or".';
                throw new \InvalidArgumentException($e);
            }

            $call = 'canWith' . ucwords($operator);

            return $this->$call($permission, $permissions);
        }

        // validate single permission
        return isset($permissions[$permission]) && $permissions[$permission] == true;
    }

    /**
     * @param $permission
     * @param $permissions
     * @return bool
     */
    protected function canWithAnd($permission, $permissions)
    {
        foreach ($permission as $check) {
            if ( ! in_array($check, $permissions) || $permissions[$check] != true ) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $permission
     * @param $permissions
     * @return bool
     */
    protected function canWithOr($permission, $permissions)
    {
        foreach ($permission as $check) {
            if ( in_array($check, $permissions) && $permissions[$check] == true ) {
                return true;
            }
        }

        return false;
    }

}