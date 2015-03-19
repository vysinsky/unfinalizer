<?php

namespace DotBlue\Unfinalizer\Composer;

require_once __DIR__ . '/composer/ClassLoader.php';

use Composer;
use DotBlue\Unfinalizer\Unfinalizer;


class ClassLoader extends Composer\Autoload\ClassLoader
{

	/** @var string */
	private $vendorDir;

	/** @var string */
	private $tempDirectory;



	/**
	 * @param  string
	 * @param  string
	 */
	public function __construct($vendorDir, $tempDirectory)
	{
		$this->vendorDir = realpath($vendorDir);
		$this->tempDirectory = $tempDirectory;
	}



	/**
	 * {@inheritdoc}
	 */
	public function findFile($class)
	{
		$original = parent::findFile($class);
		if (!$original) {
			return FALSE;
		}

		$unfinalized = $this->tempDirectory
			. DIRECTORY_SEPARATOR
			. substr($original, mb_strlen($this->vendorDir));
		if (!file_exists($unfinalized) && !Unfinalizer::unfinalize($original, $unfinalized)) {
			return $original;
		}
		return $unfinalized;
	}

}
