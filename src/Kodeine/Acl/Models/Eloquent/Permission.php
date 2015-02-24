<?php namespace Kodeine\Acl;

use Config;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
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
    protected $table = 'permissions';

    /**
     * Permissions can belong to many roles.
     *
     * @return Model
     */
    public function roles()
    {
        $model = Config::get('acl.role', 'Kodeine\Acl\Models\Eloquent\Role');
        return $this->belongsToMany($model)->withTimestamps();
    }

    /**
     * Permissions can belong to many users.
     *
     * @return Model
     */
    public function users()
    {
        return $this->belongsToMany(Config::get('auth.model'))->withTimestamps();
    }

    /**
     * @param $value
     * @return array
     */
    public function getSlugAttribute($value)
    {
        return json_decode($value, true);
    }

    /**
     * @param $value
     */
    public function setSlugAttribute($value)
    {
        $value = is_array($value) ? $value : [$value => true];

        // if attribute is being updated.
        if ( isset($this->original['slug']) ) {
            $value = $value + json_decode($this->original['slug'], true);
        }

        // remove null values.
        $value = array_filter($value, 'is_bool');

        // store as json.
        $this->attributes['slug'] = json_encode($value);
    }

}
