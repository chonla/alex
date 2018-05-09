<?php
namespace Alex;

class Router {
    private $routes = [];
    private $route_base = '';

    function __construct() {
        $this->route_base = pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_DIRNAME);
        $this->routes['GET'] = [];
        $this->routes['POST'] = [];
        $this->routes['PUT'] = [];
        $this->routes['DELETE'] = [];
    }

    private function add($method, $path, $task) {
        $path = $this->route_base . $path;
        $is_reg = false;
        if (preg_match('@:[^/]+@', $path) === 1) {
            $is_reg = true;
        }
        $f = new \ReflectionFunction($task);
        $params = [];
        foreach ($f->getParameters() as $param) {
            $params[] = $param->name;
        }
        $this->routes[$method][$path] = [
            'pattern' => '@^' . preg_replace('@:([^/]+)@', '(?<$1>[^/]+)', $path) . '$@',
            'is_reg' => $is_reg,
            'task' => $task,
            'params' => $params
        ];
    }

    private function create_dispatchable($task) {
        $dispatchable = $task;
        if (!is_callable($task)) {
            $dispatchable = function() use ($task) {
                return $task;
            };
        }
        return $dispatchable;
    }

    public function get($path, $task) {
        $dispatchable = $this->create_dispatchable($task);
        $this->add('GET', $path, $dispatchable);
    }

    public function post($path, $task) {
        $dispatchable = $this->create_dispatchable($task);
        $this->add('POST', $path, $dispatchable);
    }

    public function put($path, $task) {
        $dispatchable = $this->create_dispatchable($task);
        $this->add('PUT', $path, $dispatchable);
    }

    public function delete($path, $task) {
        $dispatchable = $this->create_dispatchable($task);
        $this->add('DELETE', $path, $dispatchable);
    }

    public function options($path, $task) {
        $dispatchable = $this->create_dispatchable($task);
        $this->add('OPTIONS', $path, $dispatchable);
    }

    public function go() {
        $response = null;
        $method = $_SERVER['REQUEST_METHOD'];
        
        $routes = $this->routes[$method];
        $response = new JsonResponse(404);
        foreach ($routes as $k => $v) {
            $uri = $_SERVER['REQUEST_URI'];
            if (!$v['is_reg'] && $k === $uri) {
                $response = $v['task']();
                break;
            } elseif ($v['is_reg'] && (preg_match($v['pattern'], $uri, $matches) === 1)) {
                $params = [];
                foreach ($v['params'] as $param) {
                    $params[] = $args[$param];
                }
                $response = call_user_func_array($v['task'], $params);
                break;
            }
        }
        echo $response;
    }
}