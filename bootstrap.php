<?php


if (file_exists(ROOT_PATH . '.env')) {
	$dotenv = new Dotenv\Dotenv(ROOT_PATH);
	$dotenv->load();
}


