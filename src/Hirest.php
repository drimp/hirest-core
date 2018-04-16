<?php

namespace Hirest\Core;



class Hirest {

	public         $routes                   = array();
	public         $request                  = null;
	private static $instance;
	private        $responseHandlerFunctions = [];
	private        $middlewareFunctions      = [];
	private        $currentRouteGroup        = null;
	private static $rootDir					 = null;
	private static $appDir					 = null;
	private static $rewriteEnabled   		 = true;

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
	 * return root directory path
	 * @return null
	 */
	public static function getRootDir(){
		if(static::$rootDir === null){
			return getcwd().DIRECTORY_SEPARATOR;
		}
		return static::$rootDir;
	}

	/**
	 * return application directory path
	 * @return null
	 */
	public static function getAppDir(){
		if(static::$appDir === null){
			return static::getRootDir();
		}
		return static::$appDir;
	}


	/**
	 * Return is URI rewrite enabled
	 * @return bool
	 */
	public static function isRewriteEnabled(){
		return static::$rewriteEnabled;
	}


	/**
	 * Applying run settings to Hirest environment
	 * @param $settings
	 * @return bool
	 * @throws \Exception
	 */
	private function applyRunSettings($settings){
		if(empty($settings)){
			return true;
		}

		//TODO: make this via iteration method

		// Set root directory
		if(isset($settings['root_dir'])){
			if(is_dir($settings['root_dir'])){
				static::$rootDir = $settings['root_dir'];
			}else{
				throw new \Exception('Root directory set in the run config is not exists: '.$settings['root_dir']);
			}
		}

		// Set application directory
		if(isset($settings['app_dir'])){
			if(is_dir($settings['app_dir'])){
				static::$appDir = $settings['app_dir'];
			}else{
				throw new \Exception('Application directory set in the run config is not exists: '.$settings['app_dir']);
			}
		}

		// Set views directory
		if(isset($settings['views_dir'])){
			if(is_dir($settings['views_dir'])){
				View::$view_path = $settings['views_dir'];
			}else{
				throw new \Exception('Views directory set in the run config is not exists: '.$settings['views_dir']);
			}
		}

		// Set views layout file
		if(isset($settings['views_layout'])){
			View::$layout = $settings['views_layout'];
		}

		// Enable/disable route rewrites
		if(isset($settings['rewrite_enabled'])){
			static::$rewriteEnabled = (bool) $settings['rewrite_enabled'];
		}

		// Preset routes
		if(isset($settings['routes'])){
			foreach ($settings['routes'] AS $regex => $action){
				$this->route($regex,$action);
			}
		}

		// Set Hiorm connection
		if(isset($settings['pdo_connection'])){
			if(class_exists('Hirest\Hiorm\Model')){
				\Hirest\Hiorm\Model::$connection = $settings['pdo_connection'];

				// If connection set debug can be enabled
				if(isset($settings['pdo_debug'])){
					\Hirest\Hiorm\Model::$debug_errors = (bool) $settings['pdo_debug'];
				}
			}else{
				throw new \Exception('Hirest ORM is not found. Please install hirest/hiorm package');
			}
		}


		return true;
	}


	/**
	 * Enable PHP session
	 * @return bool
	 */
	private static function checkSessionStarted(){
		if (session_status() == PHP_SESSION_NONE) {
			session_start();
		}
		return true;
	}




	/**
	 * parse URI and handle it
	 *
	 * @return response
	 */
	public function run( $settings = [] ) {

		self::checkSessionStarted();

		// Apply settings
		$this->applyRunSettings($settings);

		$request_uri = Request::getUri();

		preg_match('~^([^\?&]+)~ui', $request_uri, $uri);

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

