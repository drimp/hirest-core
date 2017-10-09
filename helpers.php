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
		var_dump(func_get_args());
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
