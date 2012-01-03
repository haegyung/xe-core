<?php

class ChildDriver extends Driver
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

/* End of file ChildDriver.php */
/* Location: ./tests/classes/driver/ChildDriver.php */
