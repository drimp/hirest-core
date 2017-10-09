<?php

namespace Hirest\Core;


class Hirest {

	public         $routes                   = array();
	public         $request                  = null;
	private static $instance;
	private        $responseHandlerFunctions = [];
	private        $middlewareFunctions      = [];
	private        $currentRouteGroup        = null;

	/**
	 * Singleton
	 *
	 * @return Hirest
	 */
	public static function getInstance() {
		if (!is_object(self::$instance)) {
			$c              = __CLASS__;
			self::$instance = new $c();
		}

		return self::$instance;
	}


	/**
	 * Add response handler function
	 *
	 * @param callable $function
	 * @return $this
	 */
	public function addResponseHandler(callable $function) {
		$this->responseHandlerFunctions[] = $function;

		return $this;
	}

	/**
	 * @param $response
	 * @return response handled with defined functions
	 */
	public function responseHandle($response) {
		if (!empty($this->responseHandlerFunctions)) {
			foreach ($this->responseHandlerFunctions AS $handler) {
				if (is_array($handler)) {
					$handler[0] = new $handler[0];
				}
				$response = call_user_func($handler, $response);
			}
		}

		return $response;
	}

	/**
	 * Add a middleware - a function before action
	 *
	 * @param callable $function
	 * @return $this
	 */
	public function addMiddleware(callable $function) {
		$this->middlewareFunctions[] = $function;

		return $this;
	}


	/**
	 * Processing of specified functions
	 *
	 * @return bool
	 */
	public function middlewareHandle() {
		if (empty($this->middlewareFunctions)) {
			return true;
		}
		foreach ($this->middlewareFunctions AS $handler) {
			if (is_array($handler)) {
				$handler[0] = new $handler[0];
			}
			if (call_user_func($handler) === false) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Add route rule
	 *
	 * @param $regex
	 * @param $action
	 * @param $allowed_methods
	 * @throws \Exception
	 * @return Route
	 */
	public function route($regex = null, $action = null) {
		$route          = new Route($regex, $action);
		$this->routes[] = $route;

		return $route;
	}


	public function post($regex = null, $action = null) {
		return $this->route($regex, $action)->method('POST');
	}

	public function get($regex = null, $action = null) {
		return $this->route($regex, $action)->method('GET');
	}

	public function group($parametres, $body) {
		$routeGroup = new RouteGroup();
		if ($this->currentRouteGroup) {
			$routeGroup->routeGroup = $this->currentRouteGroup;
		}
		$this->currentRouteGroup = $routeGroup;
		$routeGroup->make($parametres, $body);

		return $routeGroup;
	}

	public function getCurrentRouteGroup() {
		return $this->currentRouteGroup;
	}

	public function closeCurrentRouteGroup() {
		$this->currentRouteGroup = null;
	}


	/**
	 * Require file from app dir
	 *
	 * @param $file_name
	 * @return $this
	 */
	public function load($file_name) {
		require_once APP_PATH . $file_name . '.php';

		return $this;
	}


	/**
	 * parse URI and handle it
	 *
	 * @return response
	 */
	public function run() {

		if (isset($_SERVER['REQUEST_URI'])) {
			$request_uri = $_SERVER['REQUEST_URI'];
		} elseif (isset($_SERVER['argv'][1])) {
			$request_uri = $_SERVER['argv'][1];
		} else {
			exit('invalid request');
		}
		preg_match('~^([^\?]+)~ui', $request_uri, $uri);

		$uri           = $uri[0];
		$route_founded = false;
		foreach ($this->routes AS $route) {
			$pattern = $route->regex;

			if ($route->routeGroup) {
				$parentRouteGroup = $route;
				while ($parentRouteGroup->routeGroup) {
					$pattern          = $parentRouteGroup->routeGroup->prefix . $pattern;
					$parentRouteGroup = $parentRouteGroup->routeGroup;
				}
			}

			if (preg_match('~^/?' . $pattern . '[/]?$~iu', $uri, $params)) {

				// If route have group and group have middlewares do register it
				$parentRouteGroup = $route;
				while ($parentRouteGroup->routeGroup) {
					if (count($parentRouteGroup->routeGroup->middlewares)) {
						foreach ($parentRouteGroup->routeGroup->middlewares as $middleware) {
							$this->addMiddleware($middleware);
						}
					}
					$parentRouteGroup = $parentRouteGroup->routeGroup;
				}
				/*if($route->routeGroup && count($route->routeGroup->middlewares)){
					foreach ($route->routeGroup->middlewares as $middleware) {
						$this->addMiddleware($middleware);
					}
				}*/
				// If route have a middlewares do register it
				if (count($route->middlewares)) {
					foreach ($route->middlewares as $middleware) {
						$this->addMiddleware($middleware);
					}
				}


				if (count($route->allowed_methods)
					&& (
						!isset($_SERVER['REQUEST_METHOD'])
						|| !in_array($_SERVER['REQUEST_METHOD'], $route->allowed_methods)
					)) {
					continue;
				}
				$route_founded = true;
				foreach ($params AS $key => $value) {
					if (is_numeric($key)) {
						unset($params[ $key ]);
					}
				}
				$this->request = [
					'URI'   => $uri,
					'route' => $route
				];
				break;
			}
		}
		if ($route_founded == false) {
			http_response_code(404);
			exit();
		}


		if ($this->middlewareHandle()) {
			$action = $route->action;
			if (is_array($action)) {
				$action[0] = new $action[0];
			}
			$response = call_user_func_array($action, $params);
			echo $this->responseHandle($response);
		}

		return;
	}

}

