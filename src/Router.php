<?php
namespace Alex;

class Router {
    private $routes = [];

    private function add($method, $path, $task) {
        if (!array_key_exists($method, $this->routes)) {
            $this->routes[$method] = [];
        }
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

    public function get($path, $task) {
        $dispatch = $task;
        if (!is_callable($task)) {
            $dispatch = function() use ($task) {
                return $task;
            };
        }
        $this->add('GET', $path, $dispatch);
    }

    public function go() {
        $response = null;
        $method = $_SERVER['REQUEST_METHOD'];
        
        if (array_key_exists($method, $this->routes)) {
            $routes = $this->routes[$method];

            foreach ($routes as $k => $v) {
                $uri = $_SERVER['REQUEST_URI'];
                if ($v['is_reg']) {
                    if (preg_match($v['pattern'], $uri, $matches) === 1) {
                        $args = array_slice($matches, 1);
                        $params = [];
                        foreach ($v['params'] as $param) {
                            $params[] = $args[$param];
                        }
                        // $body = call_user_func_array($v['task'], $params);
                        // $response = new JsonResponse(200, $body);
                        $response = call_user_func_array($v['task'], $params);
                        break;
                    }
                } elseif ($k === $uri) {
                    $response = $v['task']();
                    //$body = $v['task']();
                    //$response = new JsonResponse(200, $body);
                }
            }
        }
        if ($response === null) {
            $response = new JsonResponse(404);
        }

        echo $response;
    }
}