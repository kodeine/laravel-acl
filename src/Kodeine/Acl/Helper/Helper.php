<?php namespace Kodeine\Acl\Helper;


trait Helper
{

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
        if (is_array($str)) {
            $str = implode(',', $str);
        }

        if (preg_match('/([,|])(?:\s+)?/', $str, $m)) {
            return $m[1] == '|' ? 'or' : 'and';
        }

        return false;
    }

    /**
     * Converts strings having comma
     * or pipe to an array
     *
     * @param $str
     * @return array
     */
    protected function hasDelimiterToArray($str)
    {
        if ( is_string($str) && preg_match('/[,|]/is', $str) ) {
            $str = preg_split('/ ?[,|] ?/', $str);
        }

        return $str;
    }

    /**
     * @param          $item
     * @param callable $closure
     * @return array
     */
    protected function mapArray($item, \Closure $closure)
    {
        $item = $this->hasDelimiterToArray($item);

        // multiple items
        if ( is_array($item) ) {
            // is an array of One Role
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