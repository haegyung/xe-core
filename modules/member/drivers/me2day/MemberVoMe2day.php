<?php

/**
 * @brief Class of member vlue object
 * @developer NHN (developers@xpressengine.com)
 */
class MemberVoMe2day extends MemberVO
{
	private $me2dayId;
	private $me2dayNickName;
	private $face;

	/**
	 * @brief set member info
	 * @param $memberInfo
	 * @access public
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function setMemberInfo($memberInfo)
	{
		// make common variable
		$myInfo = array('user_id', 'nick_name', 'face');
		$commonInfo = clone $memberInfo;
		foreach($myInfo as $name)
		{
			unset($commonInfo->{$name});
		}

		parent::setMemberInfo($commonInfo);

		// set member variable
		$this->memberInfo->me2dayId = $this->me2dayId = $memberInfo->user_id;
		$this->memberInfo->me2dayNickName = $this->me2dayNickName = $memberInfo->nick_name;
		$this->memberInfo->face = $this->face = $memberInfo->face;
		$this->setExtraVars($memberInfo->extra_vars);
	}

	/**
	 * @brief Set extra variable
	 * @access private
	 * @param $extraVariable
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	private function setExtraVars($extraVariable)
	{
		$this->extraVariable = unserialize($extraVariable);
		foreach($this->extraVariable as $name => $variable)
		{
			$this->memberInfo->{$name} = $variable;
		}
	}

	/**
	 * @brief Get display name
	 * @access public
	 * @return String
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getDisplayName()
	{
		return $this->getMe2dayNickName();
	}

	/**
	 * @brief get user id
	 * @access public
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getMe2dayId()
	{
		return $this->me2dayId;
	}

	/**
	 * @brief get nick name
	 * @access public
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getMe2dayNickName()
	{
		return $this->me2dayNickName;
	}

	/**
	 * @brief get face image url
	 * @access public
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getFace()
	{
		return $this->face;
	}
}
