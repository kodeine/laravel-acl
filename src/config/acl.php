<?php

return [

    /**
     * Model definitions.
     * If you want to use your own model and extend it
     * to package's model. You can define your model here.
     */

    'role'       => 'Kodeine\Acl\Models\Eloquent\Role',
    'permission' => 'Kodeine\Acl\Models\Eloquent\Permission',

    /**
     * NTFS right, the more permissive wins
     * If you have multiple permission aliases assigned, each alias
     * has a common permission, view.house => false, but one alias
     * has it set to true. If ntfs right is enabled, true value
     * wins the race, ie the more permissive wins.
     */

    'ntfs'       => true,
];
