<?php namespace Kodeine\Acl\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Exception;

class Permission extends Model
{
    /**
     * The attributes that are fillable via mass assignment.
     *
     * @var array
     */
    protected $fillable = ['name', 'slug', 'description', 'inherit_id'];

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
        $model = config('acl.role', 'Kodeine\Acl\Models\Eloquent\Role');

        return $this->belongsToMany($model)->withTimestamps();
    }

    /**
     * Permissions can belong to many users.
     *
     * @return Model
     */
    public function users()
    {
        return $this->belongsToMany(config('auth.providers.users.model', config('auth.model')))->withTimestamps();
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
     *
     * @throws Exception
     */
    public function setSlugAttribute($value)
    {
        // if nothing being set, clear slug
        if (empty($value)) {
            $this->attributes['slug'] = '[]';
            return ;
        }

        $value = is_array($value) ? $value : [$value => true];

        // if attribute is being updated.
        if ( isset($this->original['slug']) ) {
            $value = $value + json_decode($this->original['slug'], true);

            // sort by key
            ksort($value);
        }

        // remove null values.
        $value = array_filter($value, 'is_bool');

        // store as json.
        $json = json_encode($value);
        if (strlen($json) > 1024) {
            throw new Exception('Slug max length (1024) is exceeded. Consider reducing amount ' .
                            'of slug items and/or move them into different permission.');
        }
        $this->attributes['slug'] = $json;
    }

}
