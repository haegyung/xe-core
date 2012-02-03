<?php

/**
 * @brief Class of member vlue object
 * @developer NHN (developers@xpressengine.com)
 */
class MemberVoMe2day extends MemberVO
{
	private $me2dayId;
	private $me2dayNickName;

	/**
	 * @brief set member info
	 * @param $memberInfo
	 * @access public
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function setMemberInfo($memberInfo)
	{
		parent::setMemberInfo($memberInfo);

		$this->me2dayId = $memberInfo->me2dayId;
		$this->me2dayNickName = $memberInfo->me2dayNickName;
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
		return $this->userMe2dayId;
	}

	/**
	 * @brief get nick name
	 * @access public
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getMe2dayNickName()
	{
		return $this->userMe2dayNickName;
	}
}
