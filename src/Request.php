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


	public static function isMethod($method){
		return  self::method() == strtolower($method);
	}

	public static function method(){
		return strtolower($_SERVER['REQUEST_METHOD']);
	}

	/**
	 * Return request uri depends on type of request and rewrite settings
	 * @return mixed
	 * @throws \Exception
	 */
	public static function getUri(){
		if (isset($_SERVER['REQUEST_URI'])) {
			if(Hirest::isRewriteEnabled()){
				$request_uri = $_SERVER['REQUEST_URI'];
			}else{
				$request_uri = $_SERVER['QUERY_STRING'];
			}
		} elseif (isset($_SERVER['argv'][1])) {
			$request_uri = $_SERVER['argv'][1];
		} else {
			throw new \Exception('Invalid request: uri does not existed');
		}
		if(empty($request_uri)){
			$request_uri = '/';
		}
		return $request_uri;
	}




}