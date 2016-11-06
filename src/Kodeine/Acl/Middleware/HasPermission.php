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
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->request = $request;

        // override crud resources via config
        $this->crudConfigOverride();

        // if route has access
        if (( ! $this->getAction('is') or $this->hasRole()) and
            ( ! $this->getAction('can') or $this->hasPermission()) and
            ( ! $this->getAction('protect_alias') or $this->protectMethods())
        ) {
            return $next($request);
        }

        if ( $request->isJson() || $request->wantsJson() ) {
            return response()->json([
                'error' => [
                    'status_code' => 401,
                    'code'        => 'INSUFFICIENT_PERMISSIONS',
                    'description' => 'You are not authorized to access this resource.'
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

        return ! $this->forbiddenRoute() && $request->user()->hasRole($role);
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

        // get method being called on controller.
        $caller = $this->parseMethod();

        // determine if we use resource or restful http method to protect crud.
        $called = in_array($caller, $resources) ? $caller : $request->method();

        // if controller is a resource or closure
        // and does not have methods like
        // UserController@index but only
        // UserController we use crud restful.
        $methods = is_array($methods) ? $methods :
            (in_array($caller, $resources) ?
                $this->crud['resource'] : $this->crud['restful']);

        // determine crud method we're trying to protect
        $crud = $this->filterMethods($methods, function ($k, $v) use ($called) {
            return in_array($called, $v);
        });

        // crud method is read, view, delete etc
        // match it against our permissions
        // view.user or delete.user
        // multiple keys like create,store?
        // use OR operator and join keys with alias.
        $permission = implode('|', array_map(function ($e) {
            return $e . '.' . $this->parseAlias();
        }, array_keys($crud)));

        return ! $this->forbiddenRoute() && $request->user()->can($permission);
    }
    
    private function filterMethods($methods, $callback) {
        $filtered = [];

        foreach ($methods as $key => $value) {
            if (call_user_func($callback, $key, $value)) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
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

    /**
     * Override crud property via config.
     */
    protected function crudConfigOverride()
    {
        // Override crud restful from config.
        if ( ($restful = config('acl.crud.restful')) != null ) {
            $this->crud['restful'] = $restful;
        }

        // Override crud resource from config.
        if ( ($resource = config('acl.crud.resource')) != null ) {
            $this->crud['resource'] = $resource;
        }
    }

}
