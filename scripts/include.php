<?php
ini_set('include_path', '.:classes/');

date_default_timezone_set('America/New_York');

spl_autoload_register( function ($class) {
	include $class . '.php';
});
