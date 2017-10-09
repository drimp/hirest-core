<?php
/**
 * Some useful functions
 */


/**
 * Return Hirest instance
 *
 * @return \Hirest\Core\Hirest
 */
function hirest() {
	return \Hirest\Core\Hirest::getInstance();
}


if (!function_exists('dd')) {

	/**
	 * Dump & die
	 */
	function dd() {
		echo '<pre>';
		var_dump(func_get_args());
		echo '</pre>';
		die(1);
	}

}

if (!function_exists('e')) {
	/**
	 * Escape HTML special characters
	 *
	 * @param string $value
	 * @return string
	 */
	function e($value) {
		if (!is_string($value)) {
			return print_r($value);
		}

		return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
	}

}


if (!function_exists('env')) {
	function env($varname, $default = false){
		$var = getenv($varname);
		if($var === false){
			return $default;
		}
		return $var;
	}
}
