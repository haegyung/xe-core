<?php

/**
 * @brief Super class of member dirvers
 * @developer NHN (developers@xpressengine.com)
 */
abstract class MemberDriver extends Driver
{
	/**
	 * @brief Get instance of driver
	 * @access public
	 * @return Instance of driver class
	 * @developer NHN (developers@xpressengine.com)
	 */
	function getInstance()
	{
	}

	/**
	 * @brief Check update for driver
	 * @access public
	 * @return boolean
	 * @developer NHN (developers@xpressengine.com)
	 */
	function checkUpdate()
	{
	}

	/**
	 * @brief Process of update
	 * @access public
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	function driverUpdate()
	{
		debugPrint("super driverUpdate");
	}

	/**
	 * @brief Get MemberInfo
	 * @access public
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	function getMemberInfo()
	{
	}

	/**
	 * @brief Insert member
	 * @access public
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	function insertMember()
	{
	}

	/**
	 * @brief Delete member
	 * @access public
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	function deleteMember()
	{
	}

	/**
	 * @brief Update member info
	 * @access public
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	function updateMember()
	{
	}

	/**
	 * @brief Validate Login Info
	 * @access public
	 * @return boolean
	 * @developer NHN (developers@xpressengine.com)
	 */
	function validateLoginInfo()
	{
	}
}

/* End of file MemberDriver.php */
/* Location: ./modules/member/classes/MemberDriver.php */
