<?php

class ModulenameDriverDrivername extends ModulenameDriver
{
	public function checkUpdate()
	{
		return FALSE;
	}

	public function updateDriver()
	{
		return new Object();
	}
}
