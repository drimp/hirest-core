<?php

namespace Hirest\Core;

/**
 * Class Route
 *
 * @package Hirest\Core
 */
class Route {

	/** Route rules
	 * @var array
	 */
	protected $rules = [
		'regex'           => null,
		'action'          => null,
		'allowed_methods' => [],
		'middlewares'     => []
	];

	/**
	 * If route is a part of a group of routes
	 *
	 * @var null|RouteGroup
	 */
	public $routeGroup = null;

	/**
	 * Route constructor.
	 *
	 * @param string $regex    Regular expression for URI matching
	 * @param callable $action Execute on route match
	 */
	public function __construct($regex = null, callable $action = null) {
		$this->rules['regex']  = $regex;
		$this->rules['action'] = $action;

		$this->routeGroup = hirest()->getCurrentRouteGroup();

		return $this;
	}

	/**
	 * Adding an allowed HTTP method (e.g. GET or POST)
	 *
	 * @param $allowed_methods string
	 * @return $this
	 */
	public function method($allowed_methods) {
		$this->rules['allowed_methods'][] = $allowed_methods;

		return $this;
	}

	/**
	 * @param callable $action
	 * @return $this
	 */
	public function action(callable $action) {
		$this->rules['action'] = $action;

		return $this;
	}


	/**
	 * Adding a middleware to current route
	 *
	 * @param callable $middleware
	 * @return $this
	 */
	public function middleware(callable $middleware) {
		$this->rules['middlewares'][] = $middleware;

		return $this;
	}

	public function name($name){

	}


	/**
	 * Shorthand method return a Route with allowed method GET
	 *
	 * @param null $regex
	 * @param callable|null $action
	 * @return $this
	 */
	public static function get($regex = null, callable $action = null) {
		return Hirest::getInstance()->get($regex, $action);
	}

	/**
	 * Shorthand method return a Route with allowed method POST
	 *
	 * @param null $regex
	 * @param callable|null $action
	 * @return $this
	 */
	public static function post($regex = null, callable $action = null) {
		return Hirest::getInstance()->post($regex, $action);
	}


	/**
	 * Open a new route group
	 *
	 * @param array $params
	 * @param \Closure $body inside add a new routes or inner groups
	 * @return RouteGroup
	 */
	public static function group(array $params, \Closure $body) {
		return Hirest::getInstance()->group($params, $body);
	}


	/**
	 * Rules getter
	 *
	 * @param $name
	 * @return mixed|null
	 */
	public function __get($name) {
		if (isset($this->rules[ $name ])) {
			return $this->rules[ $name ];
		}

		return null;
	}

}