<?php namespace Kodeine\Acl\Middleware;

use Closure;

class HasPermission
{
    protected $crud = [
        'restful'   => [
            'create' => ['POST'],
            'read'   => ['GET', 'HEAD', 'OPTIONS'],
            'view'   => ['GET', 'HEAD', 'OPTIONS'],
            'update' => ['PUT', 'PATCH'],
            'delete' => ['DELETE'],
        ],
        'resource'  => [
            'create' => ['create', 'store'],
            'store'  => ['create', 'store'],
            'read'   => ['index', 'show'],
            'view'   => ['index', 'show'],
            'edit'   => ['edit', 'update'],
            'update' => ['edit', 'update'],
            'delete' => ['destroy'],
        ],
        'resources' => [
            'index', 'create', 'store',
            'show', 'edit', 'update', 'destroy',
        ],
    ];

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->request = $request;

        if (! $this->routeHasAcl()) {
            return $next($request);
        }

        if ($request->isJson() || $request->wantsJson()) {
            return response()->json([
                'error' => [
                    'status_code' => 401,
                    'code'        => 'INSUFFICIENT_PERMISSIONS',
                    'description' => 'You are not authorized to access this resource.',
                ],
            ], 401);
        }

        return abort(401, 'You are not authorized to access this resource.');
    }

    /**
     * Check if user has requested route role.
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
     * Check if user has requested route permissions.
     *
     * @return bool
     */
    protected function hasPermission()
    {
        $request = $this->request;
        $do = $this->getAction('can');

        return ! $this->forbiddenRoute() && $request->user()->can($do);
    }

    /**
     * Protect Crud functions of controller.
     *
     * @return string
     */
    protected function protectMethods()
    {
        $request = $this->request;

        // available methods for resources.
        $resources = $this->crud['resources'];

        // protection methods being passed in a route.
        $methods = $this->getAction('protect_methods');
        $caller = $this->getControllerMethod();
        $called = $this->defineHttpMethod($caller, $resources, $request);

        // if controller is a resource or closure
        // and does not have methods like
        // UserController@index but only
        // UserController we use crud restful.
        $methods = is_array($methods) ? $methods :
            in_array($caller, $resources) ?
                $this->crud['resource'] : $this->crud['restful'];

        // determine crud method we're trying to protect
        $crud = array_where($methods, function ($k, $v) use ($called) {
            return in_array($called, $v);
        });

        // crud method is read, view, delete etc
        // match it against our permissions
        // view.user or delete.user
        $permission = last(array_keys($crud)).'.'.$this->parseAlias();

        return ! $this->forbiddenRoute() && $request->user()->can($permission);
    }

    /**
     * Check if current route is hidden to current user role.
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
     * Extract required action from requested route.
     *
     * @param string $key action name
     *
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
     * create.user etc.
     *
     * @return string
     */
    protected function parseAlias()
    {
        if ($alias = $this->getAction('protect_alias')) {
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
     * Extract controller method.
     *
     * @return string
     */
    protected function getControllerMethod()
    {
        $action = $this->request->route()->getActionName();

        // parse index, store, create etc
        if (preg_match('/@([^\s].+)$/is', $action, $m)) {
            $controller = $m[1];
            if ($controller != 'Closure') {
                return $controller;
            }
        }

        return false;
    }

    /**
     * Check if the route has ACL.
     *
     * @return bool
     */
    private function routeHasAcl()
    {
        return ($this->getAction('is') || $this->hasRole())
        && ($this->getAction('can') || $this->hasPermission())
        && ($this->getAction('protect_alias') ||  $this->protectMethods());
    }

    /**
     * Determine if we use resource or restful http method to protect crud.
     *
     * @param $caller
     * @param $resources
     * @param $request
     *
     * @return mixed
     */
    private function defineHttpMethod($caller, $resources, $request)
    {
        return in_array($caller, $resources) ? $caller : $request->method();
    }
}
