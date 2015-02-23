<?php namespace App\Http\Middleware;

use Closure;

class HasPermission
{
    protected $crud = [
        'restful' => [
            'create' => ['POST'],
            'read'   => ['GET', 'HEAD', 'OPTIONS'],
            'view'   => ['GET', 'HEAD', 'OPTIONS'],
            'update' => ['PUT', 'PATCH'],
            'delete' => ['DELETE']
        ],
        /*'resource' => [
            'create' => ['create', 'store'],
            'read'   => ['index', 'show'],
            'view'   => ['index', 'show'],
            'update' => ['edit', 'update'],
            'delete' => ['destroy']
        ]*/
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->request = $request;

        if ( ( ! $this->getAction('is') or $this->hasRole())
            and ( ! $this->getAction('can') and ! $this->getAction('protect_alias')
                or $this->hasPermission())
        ) {
            return $next($request);
        }

        if ( $request->isJson() || $request->wantsJson() ) {
            return response()->json([
                'error' => [
                    'status_code' => 401,
                    'code'        => 'INSUFFICIENT_PERMISSIONS',
                    'description' => 'You are not authorized to access this resource.'
                ]
            ], 401);
        }

        return abort(401, 'You are not authorized to access this resource.');
    }

    /**
     * Check if user has requested route role
     *
     * @return bool
     */
    protected function hasRole()
    {
        $request = $this->request;
        $role = $this->getAction('is');

        return ! $this->forbiddenRoute() && $request->user()->is($role);
    }

    /**
     * Check if user has requested route permissions
     *
     * @return bool
     */
    protected function hasPermission()
    {
        $request = $this->request;
        $do = $this->getAction('can');

        // if method protection is needed.
        if ( ! $do && $this->getAction('protect_alias') ) {
            return $this->protectMethods();
        }

        return ! $this->forbiddenRoute() && $request->user()->can($do);
    }

    /**
     * Protect Crud functions of controller
     *
     * @return string
     */
    protected function protectMethods()
    {
        $request = $this->request;

        $methods = $this->getAction('protect_methods');

        $called = is_array($methods) ? $this->parseMethod() : $request->method();
        $methods = is_array($methods) ? $methods : $this->crud['restful'] /*$this->crud['resource'] */
        ;

        // if controller is not like UserController@index
        // and is UserController we use restful crud
        if ( ! $this->parseMethod() ) {
            $methods = $this->crud['restful'];
        }

        // determine crud method we're trying to protect
        $crud = array_where($methods, function ($k, $v) use ($called) {
            return in_array($called, $v);
        });

        // crud method is read, view, delete etc
        // match it against our permissions
        // view.user or delete.user
        $permission = last(array_keys($crud)) . '.' . $this->parseAlias();

        return ! $this->forbiddenRoute() && $request->user()->can($permission);
    }

    /**
     * Check if current route is hidden to current user role
     *
     * @return bool
     */
    protected function forbiddenRoute()
    {
        /*$action = $request->route()->getAction();
        if ( isset($action['except']) ) {
            dd($request->user()->roles->lists('slug'));
            dd($request->user()->getPermissions());
            //return $action['except'] == $request->user()->role->slug;
        }*/

        return false;
    }

    /**
     * Extract required action from requested route
     *
     * @param string $key action name
     * @return string
     */
    protected function getAction($key)
    {
        $action = $this->request->route()->getAction();

        return isset($action[$key]) ? $action[$key] : false;
    }

    /**
     * Extract controller name, make it singular
     * to match it with model name to be able
     * to match against permissions view.user,
     * create.user etc
     *
     * @return string
     */
    protected function parseAlias()
    {
        if ( $alias = $this->getAction('protect_alias') ) {
            return $alias;
        }

        $action = $this->request->route()->getActionName();

        $ctrl = preg_match('/([^@]+)+/is', $action, $m) ? $m[1] : $action;
        $name = last(explode('\\', $ctrl));
        $name = str_replace('controller', '', strtolower($name));
        $name = str_singular($name);

        return $name;
    }

    /**
     * Extract controller method
     *
     * @return string
     */
    protected function parseMethod()
    {
        $action = $this->request->route()->getActionName();

        // parse index, store, create etc
        if ( preg_match('/@([^\s].+)$/is', $action, $m) ) {
            $controller = $m[1];
            if ( $controller != 'Closure' ) {
                return $controller;
            }
        }

        return false;
    }

}
