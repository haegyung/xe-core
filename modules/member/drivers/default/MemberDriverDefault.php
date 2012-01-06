<?php

/**
 * @brief class of XE driver
 * @developer NHN (developers@xpressengine.com)
 */
class MemberDriverDefault extends MemberDriver
{
	/**
	 * @brief Check update for driver
	 * @access public
	 * @return boolean
	 * @developer NHN (developers@xpressengine.com)
	 */
	function checkUpdate()
	{
		return TRUE;
	}

	/**
	 * @brief Update for driver
	 * @access public
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	function updateDriver()
	{
		return new Object();
	}
}
/* End of file MemberDriverDefault.php */
/* Location: ./modules/member/driver/xe/MeberDriverDefault.php */
