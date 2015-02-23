<?php namespace Kodeine\Acl;

use Config;
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
        return $this->belongsToMany(Config::get('auth.model'))->withTimestamps();
    }

    /**
     * List all permissions
     *
     * @return mixed
     */
    public function getPermissions()
    {
        return $this->toDotPermissions();
    }

    /**
     * Checks if the role has the given permission.
     *
     * @param string $permission
     * @param array  $mergePermissions
     * @return bool
     */
    /*public function can($permission, $mergePermissions = [])
    {
        $permissions = $this->getPermissions() + $mergePermissions;

        if ( is_array($permission) ) {
            $intersect = array_intersect($permissions, $permission);

            return count($permission) == count($intersect);
        }

        return in_array($permission, $permissions);
    }*/

    public function can($permission, $mergePermissions = [])
    {
        $permission = $this->hasDelimiterToArray($permission);
        $permissions = $this->getPermissions() + $mergePermissions;

        if ( is_array($permission) ) {
            $diff = array_intersect_key($permissions, array_flip($permission));

            return count($permission) == count($diff);
        }

        return isset($permissions[$permission]) && $permissions[$permission] == true;
    }

}