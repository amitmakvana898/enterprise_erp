<?php

namespace App\Core;

class Router {
    protected array $routes = [];
    public Request $request;
    public Response $response;

    public function __construct(Request $request, Response $response) {
        $this->request = $request;
        $this->response = $response;
    }

    public function get(string $path, $callback, array $middlewares = []): void {
        $this->routes['GET'][$this->normalizePath($path)] = [
            'callback' => $callback,
            'middlewares' => $middlewares
        ];
    }

    public function post(string $path, $callback, array $middlewares = []): void {
        $this->routes['POST'][$this->normalizePath($path)] = [
            'callback' => $callback,
            'middlewares' => $middlewares
        ];
    }

    protected function normalizePath(string $path): string {
        return '/' . trim($path, '/');
    }

    public function resolve() {
        $path = $this->request->getPath();
        $method = $this->request->getMethod();

        // Direct exact match search
        $route = $this->routes[$method][$path] ?? null;

        // Regex parameterized route matching (e.g. /products/edit/{id})
        if (!$route) {
            foreach ($this->routes[$method] ?? [] as $routePath => $target) {
                $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $routePath);
                if (preg_match("#^" . $pattern . "$#", $path, $matches)) {
                    array_shift($matches); // Remove full match
                    $route = $target;
                    $route['params'] = $matches;
                    break;
                }
            }
        }

        if (!$route) {
            $this->response->setStatusCode(404);
            return View::render('errors/404', ['title' => 'Page Not Found']);
        }

        // Execute Middlewares
        foreach ($route['middlewares'] ?? [] as $middlewareClass) {
            $middleware = new $middlewareClass();
            $middleware->execute($this->request, $this->response);
        }

        $callback = $route['callback'];
        $params = $route['params'] ?? [];

        if (is_array($callback)) {
            $controller = new $callback[0]();
            $controller->action = $callback[1];
            return call_user_func_array([$controller, $callback[1]], $params);
        }

        if (is_callable($callback)) {
            return call_user_func_array($callback, $params);
        }
    }
}
