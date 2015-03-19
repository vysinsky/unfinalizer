<?php

spl_autoload_register(function ($classname) {
	if (substr($classname, 0, 20) === 'DotBlue\\Unfinalizer\\') {
		$filename = str_replace('\\', DIRECTORY_SEPARATOR, $classname) . '.php';
		$filename = str_replace('DotBlue' . DIRECTORY_SEPARATOR . 'Unfinalizer', 'src', $filename);
		$path = __DIR__ . DIRECTORY_SEPARATOR . $filename;
		if (file_exists($path)) {
			require_once $path;
		}
	}
});
