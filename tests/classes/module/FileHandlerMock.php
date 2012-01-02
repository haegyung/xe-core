<?php

class FileHandler
{
	public static function getRealPath($source)
	{
		$temp = explode('/', $source);
		if($temp[0] == '.') $source = _XE_PATH_.'tests/classes/module/'.substr($source, 2);
		return $source;
	}
}
