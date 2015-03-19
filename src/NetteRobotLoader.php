<?php

namespace DotBlue\Unfinalizer;

use Nette;
use ReflectionClass;


class NetteRobotLoader extends Nette\Loaders\RobotLoader implements Driver
{

	/** @var Unfinalizer */
	private $unfinalizer;

	/** @var array */
	private $directories = [];



	/**
	 * {@inheritdoc}
	 */
	public function tryLoad($type)
	{
		$reflection = new ReflectionClass(Nette\Loaders\RobotLoader::class);
		$classesReflection = $reflection->getProperty('classes');
		$classesReflection->setAccessible(TRUE);
		$classes = $classesReflection->getValue($this);

		$missingReflection = $reflection->getProperty('missing');
		$missingReflection->setAccessible(TRUE);
		$missing = $missingReflection->getValue($this);

		$rebuiltReflection = $reflection->getProperty('rebuilt');
		$rebuiltReflection->setAccessible(TRUE);
		$rebuilt = $rebuiltReflection->getValue($this);

		$commonPath = $this->detectCommonPath();

		$type = $orig = ltrim($type, '\\'); // PHP namespace bug #49143
		$type = strtolower($type);
		$info = &$classes[$type];
		if (isset($missing[$type]) || (is_int($info) && $info >= self::RETRY_LIMIT)) {
			return;
		}
		if ($this->autoRebuild) {
			if (!is_array($info) || !is_file($info['file'])) {
				$info = is_int($info) ? $info + 1 : 0;
				if ($rebuilt) {
					$this->getCache()->save($this->getKey(), $classes);
				} else {
					$this->rebuild();
				}
			} elseif (!$rebuilt && filemtime($info['file']) !== $info['time']) {
				$this->updateFile($info['file']);
				if (!isset($classes[$type])) {
					$classes[$type] = 0;
				}
				$this->getCache()->save($this->getKey(), $classes);
			}
		}
		if (isset($classes[$type]['file'])) {
			if ($classes[$type]['orig'] !== $orig) {
				trigger_error("Case mismatch on class name '$orig', correct name is '{$classes[$type]['orig']}'.", E_USER_WARNING);
			}
			$dirname = $this->unfinalizer->getTempDirectory() . '/foo';
			$dirname = pathinfo($dirname, PATHINFO_DIRNAME) . '/robotLoader-' . md5(serialize($this->getKey()));
			if (!is_dir($dirname)) {
				mkdir($dirname, 0777, TRUE);
			}

			$classes[$type]['file'] = realpath($classes[$type]['file']);

			$outputFile = $dirname . '/' . str_replace($commonPath, NULL, $classes[$type]['file']);
			$outputFile = str_replace('//', '/', $outputFile);
			if (Unfinalizer::unfinalize($classes[$type]['file'], $outputFile)) {
				$classes[$type]['file'] = $outputFile;
			}
			$classesReflection->setValue($this, $classes);
			call_user_func(function ($file) { require $file; }, $classes[$type]['file']);
		} else {
			$missing[$type] = TRUE;
			$missingReflection->setValue($this, $missing);
		}
	}



	/**
	 * {@inheritdoc}
	 */
	public function setup(Unfinalizer $unfinalizer)
	{
		$this->unfinalizer = $unfinalizer;
		return $this;
	}



	public function addDirectory($path)
	{
		parent::addDirectory($path);
		$this->directories[] = realpath($path);
		return $this;
	}



	private function detectCommonPath()
	{
		usort($this->directories, function ($a, $b) {
			return mb_strlen($a) > mb_strlen($b);
		});

		$commonPath = '';
		foreach (str_split($this->directories[0]) as $index => $character) {
			$characterPassed = FALSE;
			foreach ($this->directories as $directory) {
				if ($directory[$index] === $character) {
					$characterPassed = TRUE;
				} else {
					$characterPassed = FALSE;
				}
			}
			if ($characterPassed) {
				$commonPath .= $character;
			}
		}

		$commonPath = join('/', array_slice(explode('/', $commonPath), 0, -1));
		return $commonPath;
	}

}
