<?php
namespace Hirest\Core;

class View{

	public static $view_path;
	public static $layout;
	public static $content_variable = 'content';
	protected static $scripts = '';

	protected $view_name;
	protected $data;
	protected $rendered;


	public function __construct($view_name, $data = null) {

		$this->view_name = $view_name;
		$this->data = $data;

		if(!file_exists($this->getViewPath())){
			throw new \Exception('View '. $view_name .' not found');
		}
	}

	public function __toString() {
		if($this->data){
			extract($this->data);
		}
		ob_start();
		include $this->getViewPath();
		$this->rendered = ${static::$content_variable} = static::parseScripts(ob_get_clean());

		if(static::$layout){
			ob_start();
			include $this->getLayoutPath();
			$this->rendered = ob_get_clean();
		}

		if(preg_match('~@scripts~u', $this->rendered)){
			$this->rendered = preg_replace('~@scripts~u', static::$scripts, $this->rendered);
		}
		return $this->rendered;
	}

	public function getLayoutPath(){
		return static::$view_path
			.static::$layout
			.'.php';
	}

	public function getViewPath(){
		return static::$view_path
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
			.$view_name
			.'.php';

		return static::parseScripts(ob_get_clean());
	}


	protected function parseScripts($html){
		preg_match_all('~@script(.*)@endscript~Uis', $html, $scripts);
		if(count($scripts)){
			static::$scripts .= implode(PHP_EOL, $scripts[1]);
		}

		return preg_replace('~@script(.*)@endscript~Uis', '', $html);
	}


}