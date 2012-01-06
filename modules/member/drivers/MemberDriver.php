<?php

/**
 * @brief Super class of member dirvers
 * @developer NHN (developers@xpressengine.com)
 */
abstract class MemberDriver extends Driver
{
	private static $commonExtractVars = array('allow_mailing', 'allow_message', 'denied', 'limit_date', 'regdate', 'last_login', 'is_admin', 'description', 'extra_vars', 'list_order', 'mid', 'error_return_url', 'success_return_url', 'ruleset', 'captchaType', 'secret_text', 'group_srl_list', 'body', 'accept_agreement', 'signature', 'password', 'password2');

	/**
	 * @brief Get common extract variables
	 * @access public
	 * @return array
	 * @developer NHN (developers@xpressengine.com)
	 */
	public static function getCommonExtractVars()
	{
		return self::$commonExtractVars;
	}

	/**
	 * @brief Get MemberInfo
	 * @access public
	 * @param $memberSrl
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	abstract public function getMemberInfo($memberSrl);

	/**
	 * @brief Insert member
	 * @access public
	 * @param $memberInfo insert member information (type of stdClass)
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	abstract public function insertMember($memberInfo, $passwordIsHashed = FALSE);


	/**
	 * @brief Delete member
	 * @access public
	 * @param $memberSrl
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	abstract public function deleteMember($memberSrl);
	
	/**
	 * @brief Update member info
	 * @access public
	 * @param $memberInfo update member information (type of stdClass) 
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	abstract public function updateMember($memberInfo);
	
	/**
	 * @brief Validate Login Info
	 * @access public
	 * @param $loginInfo login information (ex : user_id/email_address, password)
	 * @return boolean
	 * @developer NHN (developers@xpressengine.com)
	 */
	abstract public function validateLoginInfo($loginInfo);

	/**
	 * @brief Extract extra variables
	 * @access public
	 * @param $memberInfo member information
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function extractExtraVars($memberInfo)
	{
		$extraVars = clone $memberInfo;
		
		foreach(self::$commonExtractVars as $column)
		{
			unset($extraVars->{$column});
		}

		return $extraVars;
	}
}

/* End of file MemberDriver.php */
/* Location: ./modules/member/classes/MemberDriver.php */
