<?php
namespace Hirest\Core;

class Request{

	public static function all(){
		return (object) $_REQUEST;
	}

	public static function get($var, $default = null){
		if(!static::has($var)){
			return $default;
		}
		return $_REQUEST[$var];
	}

	public static function has($var){
		return isset($_REQUEST[$var]);
	}




}