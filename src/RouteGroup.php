<?php

namespace Hirest\Core;

/**
 * Class RouteGroup
 *
 * @package Hirest\Core
 */
class RouteGroup {

	/**
	 * Part of route that mathiching before inner routes
	 *
	 * @var null|string
	 */
	public $prefix = null;
	/**
	 * Group specified middlewares
	 *
	 * @var array
	 */
	public $middlewares = [];

	/**
	 * Parent route group
	 *
	 * @var null|RouteGroup
	 */
	public $routeGroup = null;


	/**
	 * Open a new route group
	 *
	 * @param array $parametres (prefix, middleware)
	 * @param \Closure $body
	 * @return $this
	 */
	public function make(array $parametres, \Closure $body) {

		if (isset($parametres['prefix'])) {
			$this->prefix = $parametres['prefix'];
		}

		// Group specified midllewares
		if (isset($parametres['middleware'])) {
			if (is_array($parametres['middleware'])) {
				$this->middlewares = array_merge($this->middlewares, $parametres['middleware']);
			} else {
				$this->middlewares[] = $parametres['middleware'];
			}
		}

		call_user_func($body);

		// Finish handle this route group
		// next routes will added in hirest root
		hirest()->closeCurrentRouteGroup();

		return $this;
	}


}