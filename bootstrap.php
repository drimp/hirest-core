<?php


define('APP_PATH', __DIR__.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR);



/**
 * Return Hirest instance
 * @return \Hirest\Core\Hirest
 */
function hirest(){
	return \Hirest\Core\Hirest::getInstance();
}