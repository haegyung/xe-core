<?php

/**
 * @brief Class of member value object
 * @developer NHN (developers@xpressengine.com)
 */
class MemberVoDefault extends MemberVO
{
	private $userId;
	private $emailAddress;
	private $password;
	private $emailId;
	private $emailHost;
	private $userName;
	private $findAccountQuestion;
	private $findAccountAnswer;
	private $homepage;
	private $blog;
	private $birthday;
	private $changePasswordDate;
	private $extraVariable;
	private $nickName;

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
		parent::setMemberInfo($memberInfo);

		$this->userId = $memberInfo->user_id;
		$this->emailAddress = $memberInfo->email_address;
		$this->password = $memberInfo->password;
		$this->emailId = $memberInfo->email_id;
		$this->emailHost = $memberInfo->email_host;
		$this->userName = $memberInfo->user_name;
		$this->findAccountQuestion = $memberInfo->find_account_question;
		$this->findAccountAnswer = $memberInfo->find_account_answer;
		$this->homepage = $memberInfo->homepage;
		$this->blog = $memberInfo->blog;
		$this->birthday = $memberInfo->birthday;
		$this->changePasswordDate = $memberInfo->change_password_date;
		$this->nickName = $memberInfo->nick_name;
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
		return $this->getNickName();
	}

	/**
	 * @brief get user id
	 * @access public
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getUserId()
	{
		return $this->userId;
	}

	/**
	 * @brief get email address
	 * @access public
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getEmailAddress()
	{
		return $this->emailAddress;
	}

	/**
	 * @brief get password
	 * @access public
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * @brief get email id
	 * @access public
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getEmailId()
	{
		return $this->emailId;
	}

	/**
	 * @brief get email host
	 * @access public
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getEmailHost()
	{
		return $this->emailHost;
	}

	/**
	 * @brief get user name
	 * @access public
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getUserName()
	{
		return $this->userName;
	}

	/**
	 * @brief get find account question
	 * @access public
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getFindAccountQuestion()
	{
		return $this->findAccountQuestion;
	}

	/**
	 * @brief get find account answer
	 * @access public
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getFindAccountAnswer()
	{
		return $this->findAccountAnswer;
	}

	/**
	 * @brief get homepage
	 * @access public
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getHomepage()
	{
		return $this->homepage;
	}

	/**
	 * @brief get blog
	 * @access public
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getBlog()
	{
		return $this->blog;
	}

	/**
	 * @brief get birthday
	 * @access public
	 * @param $format date format
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getBirthday($format = 'Ymd')
	{
		return zdate($this->birthday, $format);
	}

	/**
	 * @brief get change password date
	 * @access public
	 * @param $format date format
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getChangePasswordDate($format = 'Ymd')
	{
		return zdate($this->changePasswordDate, $format);
	}

	/**
	 * @brief get nickName
	 * @access public
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getNickName()
	{
		return $this->nickName;
	}

	/**
	 * @brief get extra variable
	 * @access public
	 * @param $name
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getExtraVariable($name)
	{
		return $this->extraVariable->{$name};
	}
}

/* End of file MemberVO.php */
/* Location: ./modules/member/classes/MemberVO.php */
