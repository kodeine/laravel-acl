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
        return $this->belongsToMany(config('auth.providers.users.model', config('auth.model')))->withTimestamps();
    }

    /**
     * List all permissions
     *
     * @return mixed
     */
    public function getPermissions()
    {
        return \Cache::remember(
            'acl.getPermissionsInheritedById_'.$this->id,
            config('acl.cacheMinutes'),
            function () {
                return $this->getPermissionsInherited();
            }
        );
    }

    /**
     * Checks if the role has the given permission.
     *
     * @param string $permission
     * @param string $model
     * @param int    $reference_id
     * @param string $operator
     * @param array  $mergePermissions
     * @return bool
     */
    public function can($permission, $model = '', $reference_id = 0, $operator = null, $mergePermissions = [])
    {
        $operator = is_null($operator) ? $this->parseOperator($permission) : $operator;

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

            return $this->$call($permission, $permissions, $model, $reference_id);
        }

        // Validate single permission.
        $permission_model     = "{$permission}:{$model}";
        $permission_reference = "{$permission}:{$model}:{$reference_id}";
        $checks           = [
            // If user have global permission.
            $permission,
            // If user have permission to this model.
            $permission_model,
            // If user have permission to this model and reference_id.
            $permission_reference
        ];
        foreach ($checks as $c) {
            if (isset($permissions[$c]) && $permissions[$c] == true) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $permission
     * @param array  $permissions
     * @param string $model
     * @param int    $reference_id
     *
     * @return bool
     */
    protected function canWithAnd($permission, $permissions, $model = '', $reference_id = 0)
    {
        foreach ($permission as $check) {
            $permission_ok        = false;
            $permission_model     = "{$check}:{$model}";
            $permission_reference = "{$check}:{$model}:{$reference_id}";
            $checks           = [
                // If user have global permission.
                $check,
                // If user have permission to this model.
                $permission_model,
                // If user have permission to this model and reference_id.
                $permission_reference
            ];
            foreach ($checks as $c) {
                if (isset($permissions[$c]) && $permissions[$c] == true) {
                    $permission_ok = true;
                }
            }
            if ( ! $permission_ok) {
                return false;
            }
        }

        return true;
    }


    /**
     * @param string $permission
     * @param array  $permissions
     * @param string $model
     * @param int    $reference_id
     *
     * @return bool
     */
    protected function canWithOr($permission, $permissions, $model = '', $reference_id = 0)
    {
        foreach ($permission as $check) {
            $permission_model     = "{$check}:{$model}";
            $permission_reference = "{$check}:{$model}:{$reference_id}";
            $checks           = [
                // If user have global permission.
                $check,
                // If user have permission to this model.
                $permission_model,
                // If user have permission to this model and reference_id.
                $permission_reference
            ];
            foreach ($checks as $c) {
                if (isset($permissions[$c]) && $permissions[$c] == true) {
                    return true;
                }
            }
        }

        return false;
    }

}