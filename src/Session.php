<?php
namespace Hirest\Core;

class Session{

	public static function set($var, $value){
		$_SESSION[$var] = $value;
	}

	public static function has($var){
		return isset($_SESSION[$var]);
	}

	public static function get($var, $default = null){
		if(!static::has($var)){
			return $default;
		}

		return $_SESSION[$var];
	}

	public static function flash($var, $default = null){
		if(!static::has($var)){
			return $default;
		}
		$result = $_SESSION[$var];
		unset($_SESSION[$var]);
		return $result;
	}


}