<?php
use Hirest\Core\Hirest;


if (file_exists(Hirest::getRootDir() . '.env')) {
	$dotenv = new Dotenv\Dotenv(Hirest::getRootDir());
	$dotenv->load();
}


