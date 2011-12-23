<?php

/**
 * @brief Class of member value object
 * @developer NHN (developers@xpressengine.com)
 */
class MemberVO
{
	var $memberInfo; //stdclass
	
	var $memberSrl;
	var $allowMailing;
	var $allowMessage;
	var $denied;
	var $limitDate;
	var $regdate;
	var $lastLogin;
	var $isAdmin;
	var $description;

	/**
	 * @brief Get display name
	 * @access public
	 * @return String
	 * @developer NHN (developers@xpressengine.com)
	 */
	function getDisplayName()
	{
		return 'anonymous';	
	}
}

/* End of file MemberVO.php */
/* Location: ./modules/member/classes/MemberVO.php */
