<?php

namespace DotBlue\Unfinalizer;


class Unfinalizer
{

	/** @var string */
	private $tempDirectory;



	/**
	 * @return string
	 */
	public function getTempDirectory()
	{
		return $this->tempDirectory;
	}



	/**
	 * @param  string
	 * @return self
	 */
	public function setTempDirectory($tempDirectory)
	{
		$this->tempDirectory = $tempDirectory;
		return $this;
	}



	/**
	 * @param  Driver
	 * @return mixed
	 */
	public function register(Driver $driver)
	{
		return $driver->setup($this);
	}



	/**
	 * @param  string
	 * @param  string
	 * @return bool
	 */
	public static function unfinalize($filename, $outputFile)
	{
		$source = file_get_contents($filename);
		$tokens = token_get_all($source);
		$unfinalized = '';

		foreach ($tokens as $token) {
			if (!is_array($token)) {
				$unfinalized .= $token;
				continue;
			} else {
				if ($token[0] !== T_FINAL) {
					$unfinalized .= $token[1];
				}
			}
		}

		if ($source === $unfinalized) {
			return FALSE;
		}

		$directory = dirname($outputFile);
		if (is_dir($directory) || @mkdir($directory, 0777, TRUE)) {
			return file_put_contents($outputFile, $unfinalized);
		}
		return FALSE;
	}

}
