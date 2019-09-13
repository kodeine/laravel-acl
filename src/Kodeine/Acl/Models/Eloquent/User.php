<?php namespace Kodeine\Acl\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Authenticatable;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Auth\Passwords\CanResetPassword;
use Kodeine\Acl\Traits\HasRole;

class User extends Model
{
    use Authenticatable, CanResetPassword, HasRole, SoftDeletes;
    
    /**
     * The attributes that are fillable via mass assignment.
     *
     * @var array
     */
    protected $fillable = ['username', 'first_name', 'last_name', 'email', 'password',];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';
}
