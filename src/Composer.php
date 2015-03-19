<?php

namespace DotBlue\Unfinalizer;


class Composer implements Driver
{

	/** @var string */
	private $vendorDir;



	/**
	 * @param  string Absolute path to vendor directory
	 */
	public function __construct($vendorDir)
	{
		$this->vendorDir = $vendorDir;
	}



	/**
	 * {@inheritdoc}
	 */
	public function setup(Unfinalizer $unfinalizer)
	{
		$tempDirectory = $unfinalizer->getTempDirectory()
			. '/composer-'
			. $this->getComposerHash();

		@mkdir($tempDirectory, 0777, TRUE);
		@mkdir($tempDirectory . '/composer', 0777, TRUE);

		file_put_contents(
			$tempDirectory . '/composer/autoload_real.php',
			$this->createAutoloadReal($tempDirectory)
		);

		file_put_contents(
			$tempDirectory . '/composer/ClassLoader.php',
			$this->createClassLoader()
		);

		file_put_contents(
			$tempDirectory . '/autoload.php',
			file_get_contents($this->vendorDir . '/autoload.php')
		);

		return require_once $tempDirectory . '/autoload.php';
	}



	/**
	 * @return string
	 */
	private function getComposerHash()
	{
		$composerLock = file_get_contents($this->vendorDir . '/../composer.lock');
		return json_decode($composerLock)->hash;
	}



	/**
	 * @param  string
	 * @return string
	 */
	private function createAutoloadReal($tempDirectory)
	{
		$autoloadReal = file_get_contents($this->vendorDir . '/composer/autoload_real.php');
		$autoloadReal = str_replace('__DIR__ . \'/autoload_', '\'' . $this->vendorDir . '/composer/autoload_', $autoloadReal);
		$autoloadReal = str_replace('Composer\Autoload\ClassLoader', 'DotBlue\Unfinalizer\Composer\ClassLoader', $autoloadReal);
		$autoloadReal = str_replace('ClassLoader()', 'ClassLoader(\'' . $this->vendorDir . '\', \'' . $tempDirectory . '\')', $autoloadReal);
		return $autoloadReal;
	}



	/**
	 * @return string
	 */
	private function createClassLoader()
	{
		$classLoader = file_get_contents(__DIR__ . '/Composer/ClassLoader.php');
		$classLoader = str_replace(
			'require_once __DIR__ . \'/composer/ClassLoader.php\';',
			'require_once \'' . $this->vendorDir . '\' . \'/composer/ClassLoader.php\';',
			$classLoader
		);
		return $classLoader;
	}

}
