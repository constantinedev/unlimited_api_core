<?php
	class Request {
		public $method;
		public $path;

		public function __construct() {
			$this->method = $_SERVER['REQUEST_METHOD'];
			$this->path = $_SERVER['REQUEST_URI'];
		}
	}

	class Response {
		public $status;
		public $body;

		public function __construct($status = 200, $body = '') {
			$this->status = $status;
			$this->body = $body;
		}
		public function send() {
			http_response_code($this->status);
			echo $this->body;
		}
	}

	class Route {
		public $path;
		public $handler;

		public function __construct($path, $handler) {
			$this->path = $path;
			$this->handler = $handler;
		}
	}

	class Application {
		private $routes = [];

		public function __construct() {
			$this->routes = [
				'GET' => [],
				'POST' => [],
			];
		}

		public function route($path) {
			return new Route($path, null);
		}

		public function get($path, $handler) {
			$route = new Route($path, $handler);
			$this->routes['GET'][] = $route;
			return $route;
		}

		public function post($path, $handler) {
			$route = new Route($path, $handler);
			$this->routes['POST'][] = $route;
			return $route;
		}

		public function run() {
			$request = new Request();
			$method = $request->method;
			$path = $request->path;

			$routes = $this->routes[$method] ?? [];

			foreach ($routes as $route) {
				if ($route->path === $path) {
					$response = new Response();
					$handler = $route->handler;
					$handler($request, $response);
					$response->send();
					return;
				}
			}

			$response = new Response(404, 'Not Found');
			$response->send();
		}
	}

	$app = new Application();

	$app->get('/', function ($request, $response) {
		$response->body = 'Hello World From PHP ';
	});

	$app->get('/about', function ($request, $response) {
		$dummy_json = [];
		$jsonResonse = json_encode($dummy_json);
		$response->status = 200;
		$response->handlers['Content-Type'] = 'application/json';

		$response->body = $jsonResonse;
	});

	$app->run();
?>
