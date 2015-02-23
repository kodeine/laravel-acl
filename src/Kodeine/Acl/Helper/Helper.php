<?php namespace Kodeine\Acl\Helper;


trait Helper
{

    /*
    |----------------------------------------------------------------------
    | Slug Permission Related Protected Methods
    |----------------------------------------------------------------------
    |
    */

    protected function toDotPermissions()
    {
        $data = [];
        $list = $this->permissions->lists('slug', 'name');
        foreach ($list as $alias => $perm) {
            if ( ! is_array($perm) ) continue;
            foreach ($perm as $key => $value) {
                if ( (bool) $value == false ) continue;
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
            return array_map($closure, $item);
        }

        // single item
        return $closure($item);
    }

}