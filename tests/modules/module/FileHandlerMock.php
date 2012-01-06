<?php

class FileHandler
{
	public static function getRealPath($source)
	{
		$temp = explode('/', $source);
		if($temp[0] == '.') $source = _XE_PATH_.'tests/modules/module/'.substr($source, 2);
		return $source;
	}

	function readFile($fileName)
	{
		return file_get_contents($fileName);
	}
}
