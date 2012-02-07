<?php

/**
 * @brief Class of member value object
 * @developer NHN (developers@xpressengine.com)
 */
class MemberVO
{
	protected $memberInfo; //stdclass

	private $memberSrl;
	private $allowMailing;
	private $allowMessage;
	private $denied;
	private $limitDate;
	private $regdate;
	private $lastLogin;
	private $isAdmin;
	private $description;
	private $groupList;


	/**
	 * @brief Constractor
	 * @param $memberInfo
	 * @access public
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function __construct($memberInfo = null)
	{
		if($memberInfo)
		{
			$this->setMemberInfo($memberInfo);
		}
	}

	/**
	 * @brief set member info
	 * @param $memberInfo
	 * @access public
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function setMemberInfo($memberInfo)
	{
		$this->memberInfo = $memberInfo;

		$this->memberSrl = $memberInfo->member_srl;
		$this->allowMailing = $memberInfo->allow_mailing;
		$this->allowMessage = $memberInfo->allow_message;
		$this->denied = $memberInfo->denied;
		$this->limitDate = $memberInfo->limit_date;
		$this->regdate = $memberInfo->regdate;
		$this->lastLogin = $memberInfo->last_login;
		$this->isAdmin = $memberInfo->is_admin;
		$this->description = $memberInfo->description;

		$oMemberModel = getModel('member');
		$this->groupList = $oMemberModel->getMemberGroups($this->memberSrl);
		$this->memberInfo->group_list = $this->groupList;
	}

	/**
	 * @brief get member info
	 * @access public
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getMemberInfo()
	{
		return $this->memberInfo;
	}

	/**
	 * @brief Get display name
	 * @access public
	 * @return String
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getDisplayName()
	{
		return 'anonymous';
	}

	/**
	 * @brief set member srl
	 * @access public
	 * @param $memberSrl
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function setMemberSrl($memberSrl)
	{
		$this->memberSrl = $memberSrl;
	}

	/**
	 * @brief Get memberSrl
	 * @access public
	 * @return int
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getMemberSrl()
	{
		return $this->memberSrl;
	}

	/**
	 * @brief is allow mailing
	 * @access public
	 * @return Boolean
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function isAllowMailing()
	{
		return ($this->allowMailing == 'Y');
	}

	/**
	 * @brief is allow message
	 * @access public
	 * @return Boolean
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function isAllowMessage()
	{
		return ($this->allowMessage == 'Y');
	}

	/**
	 * @brief is denied
	 * @access public
	 * @return Boolean
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function isDenied()
	{
		return ($this->denied == 'Y');
	}

	/**
	 * @brief get limit date
	 * @access public
	 * @param $format format of date
	 * @return String (date format : YYYYmmddHHiiss)
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getLimitDate($format = 'YmdHis')
	{
		return zdate($this->limitDate, $format);
	}

	/**
	 * @brief get regdate
	 * @access public
	 * @param $format format of date
	 * @return String (date format : YYYYmmddHHiiss)
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getRegdate($format = 'YmdHis')
	{
		return zdate($this->regdate, $format);
	}

	/**
	 * @brief get last login
	 * @access public
	 * @param $format format of date
	 * @return String (date format : YYYYmmddHHiiss)
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getLastLogin($format = 'YmdHis')
	{
		return zdate($this->regdate, $format);
	}

	/**
	 * @brief is admin
	 * @access public
	 * @return Boolean
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function isAdmin()
	{
		return ($this->is_admin == 'Y');
	}

	/**
	 * @brief get desciption
	 * @access public
	 * @return String
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getDescription()
	{
		return $this->description;
	}
}

/* End of file MemberVO.php */
/* Location: ./modules/member/classes/MemberVO.php */
