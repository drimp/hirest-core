<?php
namespace Hirest\Core;

class View{

	public static $view_path;
	public static $layout;
	public static $content_variable = 'content';

	protected $view_name;
	protected $data;
	protected $rendered;

	public function __construct($view_name, $data = null) {

		$this->view_name = $view_name;
		$this->data = $data;

		if(!file_exists($this->getViewPath())){
			dd($this->getViewPath());
			throw new \Exception('View '. $view_name .' not found');
		}
	}

	public function __toString() {
		if($this->data){
			extract($this->data);
		}
		ob_start();
		include $this->getViewPath();
		$this->rendered = ${static::$content_variable} = ob_get_clean();

		if(static::$layout){
			include $this->getLayoutPath();
			$this->rendered = ob_get_clean();
		}
		return $this->rendered;
	}

	public function getLayoutPath(){
		return static::$view_path
			.DIRECTORY_SEPARATOR
			.static::$layout
			.'.php';
	}

	public function getViewPath(){
		return static::$view_path
			.DIRECTORY_SEPARATOR
			.$this->view_name
			.'.php';
	}

	public static function make($view_name, $data = null){
		return new static($view_name, $data);
	}

	public static function part($view_name, $data = null){
		if($data){
			extract($data);
		}
		ob_start();

		include static::$view_path
			.DIRECTORY_SEPARATOR
			.$view_name
			.'.php';

		return ob_get_clean();
	}



}