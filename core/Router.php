<?php
namespace Core;

class Router {
    private $routes = [];

    // Adiciona uma nova rota mapeada a um Controller@Method
    public function add($method, $uri, $controllerAction) {
        $uri = rtrim($uri, '/');
        if ($uri === '') $uri = '/';

        $this->routes[] = [
            'method' => strtoupper($method),
            'uri' => $uri,
            'controllerAction' => $controllerAction
        ];
    }

    // Processa a URI atual e executa o Controller respectivo
    public function dispatch($uri, $requestMethod) {
        $uri = rtrim($uri, '/');
        // Trata o root (/)
        if ($uri === '') {
            $uri = '/';
        }

        foreach ($this->routes as $route) {
            // Verifica se a rota casou
            if ($route['method'] === strtoupper($requestMethod) && $route['uri'] === $uri) {
                
                list($controllerName, $action) = explode('@', $route['controllerAction']);
                $controllerClass = "App\\Controllers\\" . $controllerName;

                if (class_exists($controllerClass)) {
                    $controllerInstance = new $controllerClass();
                    
                    if (method_exists($controllerInstance, $action)) {
                        http_response_code(200);
                        call_user_func([$controllerInstance, $action]);
                        return;
                    }
                }
            }
        }
        
        // Rota não encontrada
        http_response_code(404);
        echo "404 - Página não encontrada. A rota '$uri' não está mapeada.";
    }
}

