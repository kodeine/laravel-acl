<?php namespace Kodeine\Acl\Traits;

use Illuminate\Support\Collection;

trait HasUserPermission
{

    /*
    |----------------------------------------------------------------------
    | User Permission Trait Methods
    |----------------------------------------------------------------------
    |
    */

    public function addPermission($name, $permission = true)
    {
        $slugs = $this->permissions->keyBy('name');
        list($slug, $alias) = $this->extractAlias($name);

        if ( $slugs->has($alias) && is_null($slug) ) {
            return true;
        }

        if ( ! $slugs->has($alias) && is_null($slug) ) {
            return $this->addPermissionCrud($name);
        }

        $slug = is_array($permission)
            ? $permission : [$slug => (bool) $permission];

        $array = [
            'name' => $alias,
            'slug' => $this->addSlug($alias, $slug),
        ];

        if ( ! $slugs->has($alias) ) {
            return $this->permissions()->create($array);
        }

        return $slugs[$alias]->update($array['slug']);
    }

    public function removePermission($name)
    {
        $slugs = $this->permissions->keyBy('name');
        list($slug, $alias) = $this->extractAlias($name);

        // remove whole alias
        if ( $slugs->has($alias) && is_null($slug) ) {
            return $slugs[$alias]->delete();
        }

        // remove slug only.
        if ( $slugs->has($alias) && ! is_null($slug) ) {
            $slug = is_array($slug) ? $slug : [$slug];

            return $slugs[$alias]->update([
                'slug' => $this->removeSlug($alias, $slug)
            ]);
        }

        return true;
    }


    /*
    |----------------------------------------------------------------------
    | Slug Permission Related Protected Methods
    |----------------------------------------------------------------------
    |
    */

    protected function addPermissionCrud($name)
    {
        $slugs = $this->permissions->keyBy('name');
        list(, $alias) = $this->extractAlias($name);

        $hasCrud = isset($slugs->get($alias)->slug['create']);

        if ( $slugs->has($alias) && $hasCrud ) {
            return true;
        }

        $array = [
            'name' => $alias,
            'slug' => $this->refreshSlug($alias, [
                'create' => true, 'read' => true,
                'view'   => true, 'update' => true,
                'delete' => true
            ])
        ];

        if ( ! $slugs->has($alias) ) {
            return $this->permissions()->create($array);
        }

        return $slugs[$alias]->update($array['slug']);
    }

    protected function extractAlias($str)
    {
        preg_match('/([^.].*)[\._]([^\s].*?)$/i', $str, $m);

        return [
            isset($m[1]) ? $m[1] : null, //slug
            isset($m[2]) ? $m[2] : $str, //alias
        ];
    }

    protected function addSlug($alias, array $permissions)
    {
        $slugs = $this->permissions->lists('slug', 'name');
        $collection = new Collection($slugs);

        if ( $collection->has($alias) ) {
            $permissions = $permissions + $collection->get($alias);
        }

        $collection->put($alias, $permissions);

        return $collection->get($alias);
    }

    protected function removeSlug($alias, array $permissions)
    {
        $slugs = $this->permissions->lists('slug', 'name');
        $collection = new Collection($slugs);

        if ( $collection->has($alias) ) {
            $new = array_diff_key($collection->get($alias), array_flip($permissions));
            $collection->put($alias, $new);
        }

        return $collection->get($alias);
    }

}
