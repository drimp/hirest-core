<?php
namespace Hirest\Core;

class Route{

	protected $rules = [
		'regex' => null,
		'action' => null,
		'allowed_methods' => null
	];

	public function __construct($regex = null, $action = null) {
			$this->rules['regex'] = $regex;
			$this->rules['action'] = $action;
			return $this;
	}

	public function methods($allowed_methods){
		$this->rules['allowed_methods'] = $allowed_methods;
		return $this;
	}

	public function action($action){
		$this->rules['action'] = $action;
		return $this;
	}



	/**
	 * @param null $regex
	 * @param null $action
	 * @return Route
	 */
	public function route($regex = null, $action = null){
		return Hirest::getInstance()->route($regex, $action);
	}


	public function __get($name) {
		if(isset($this->rules[$name])){
			return $this->rules[$name];
		}
		return null;
	}

}