<?php namespace Kodeine\Acl\Helper;

use Illuminate\Support\Collection;

trait Helper
{
    /*
    |----------------------------------------------------------------------
    | Collection methods compatible with L5.1.
    |----------------------------------------------------------------------
    |
    */

    /**
     * Lists() method in l5.1 returns collection.
     * This method fixes that issue for backward
     * compatibility.
     *
     * @param $data
     * @return mixed
     */
    protected function collectionAsArray($data)
    {
        return ($data instanceof Collection)
            ? $data->toArray()
            : $data;
    }

    /*
    |----------------------------------------------------------------------
    | Slug Permission Related Protected Methods
    |----------------------------------------------------------------------
    |
    */

    protected function toDotPermissions(array $permissions)
    {
        $data = [];
        //$permissions = $this->permissions->lists('slug', 'name');
        foreach ($permissions as $alias => $perm) {
            if ( ! is_array($perm) ) continue;
            foreach ($perm as $key => $value) {
                //if ( (bool) $value == false ) continue;
                $slug = $key . '.' . $alias;
                $data[$slug] = $value;
                //$data[] = $slug;
            }
        }

        return $data;
    }

    /*
    |----------------------------------------------------------------------
    | Protected Methods
    |----------------------------------------------------------------------
    |
    */

    protected function parseOperator($str)
    {
        // if its an array lets use
        // and operator by default
        if ( is_array($str) ) {
            $str = implode(',', $str);
        }

        if ( preg_match('/([,|])(?:\s+)?/', $str, $m) ) {
            return $m[1] == '|' ? 'or' : 'and';
        }

        return false;
    }

    /**
     * Converts strings having comma
     * or pipe to an array in
     * lowercase
     *
     * @param $str
     * @return array
     */
    protected function hasDelimiterToArray($str)
    {
        if ( is_string($str) && preg_match('/[,|]/is', $str) ) {
            return preg_split('/ ?[,|] ?/', strtolower($str));
        }

        return is_array($str) ? 
            array_filter($str, 'strtolower') : 
            (is_object($str) ? $str : strtolower($str));
    }

    /**
     * @param          $item
     * @param callable $closure
     * @return array
     */
    protected function mapArray($item, \Closure $closure)
    {
        $item = $this->hasDelimiterToArray($item);

        // item is a collection.
        if ($item instanceof Collection) {
            $item = $this->collectionAsArray(
                method_exists($item, 'pluck') ? $item->pluck('name') : $item->lists('name')
            );
        }

        // multiple items
        if ( is_array($item) ) {
            // is an array of One Role/Permission
            // its an array containing id
            // we dont have to loop through
            if ( isset($item['id']) )
                return $closure((int) $item['id']);

            // is an array of slugs
            return array_map($closure, $item);
        }

        // single item
        return $closure($item);
    }

}
