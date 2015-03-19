<?php

namespace DotBlue\Unfinalizer;


interface Driver
{

	/**
	 * @param  Unfinalizer
	 * @return mixed
	 */
	function setup(Unfinalizer $unfinalizer);

}
