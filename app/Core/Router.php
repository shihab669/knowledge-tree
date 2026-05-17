<?php

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $middlewares = [];

    public function get(string $path, callable|array $handler): self
    {
        $this->addRoute('GET', $path, $handler);
        return $this;
    }

    public function post(string $path, callable|array $handler): self
    {
        $this->addRoute('POST', $path, $handler);
        return $this;
    }

    public function put(string $path, callable|array $handler): self
    {
        $this->addRoute('PUT', $path, $handler);
        return $this;
    }

    public function delete(string $path, callable|array $handler): self
    {
        $this->addRoute('DELETE', $path, $handler);
        return $this;
    }

    public function middleware(string $name, callable $handler): self
    {
        $this->middlewares[$name] = $handler;
        return $this;
    }

    private function addRoute(string $method, string $path, callable|array $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'pattern' => $this->buildPattern($path)
        ];
    }

    private function buildPattern(string $path): string
    {
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        // Handle PUT/DELETE from forms
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Run before middlewares
                foreach ($this->middlewares as $middleware) {
                    $result = $middleware();
                    if ($result === false) {
                        return;
                    }
                }

                $handler = $route['handler'];

                if (is_array($handler)) {
                    [$controllerClass, $action] = $handler;
                    $controller = new $controllerClass();
                    $controller->$action(...array_values($params));
                } else {
                    $handler(...array_values($params));
                }

                return;
            }
        }

        // 404
        http_response_code(404);
        if (str_starts_with($uri, '/api')) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Not found']);
        } else {
            include APP_ROOT . '/app/Views/404.php';
        }
    }
}
