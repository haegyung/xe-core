<?php

require_once _XE_PATH_ . 'modules/member/drivers/MemberDriverException.php';

/**
 * @brief Super class of member dirvers
 * @developer NHN (developers@xpressengine.com)
 */
abstract class MemberDriver extends Driver
{
	private static $commonExtractVars = array('allow_mailing', 'allow_message', 'denied', 'limit_date', 'regdate', 'last_login', 'is_admin', 'description', 'extra_vars', 'list_order', 'mid', 'error_return_url', 'success_return_url', 'ruleset', 'captchaType', 'secret_text', 'group_srl_list', 'body', 'accept_agreement', 'signature', 'password', 'password2', 'module', 'driver', 'member_srl', 'act');

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
	 * @brief Get MemberVo
	 * @access public
	 * @param $memberSrl
	 * @return MemberVo
	 * @developer NHN (developers@xpressengine.com)
	 */
	abstract public function getMemberVo($memberSrl);

	/**
	 * @brief Insert member
	 * @access public
	 * @param $memberInfo insert member information (type of stdClass)
	 * @param $passwordIsHashed
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
	 * @param $loginInfo login information
	 * @return memberVo
	 * @developer NHN (developers@xpressengine.com)
	 */
	abstract public function validateLoginInfo($loginInfo);

	/**
	 * @brief get member signup form format
	 * @access public
	 * @param $memberInfo (when modify member_info of modified target member)
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	abstract public function getSignupFormInfo($memberInfo = NULL);

	/**
	 * @brief get member modify form format
	 * @access public
	 * @param $memberSrl (when modify member_info of modified target member)
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	abstract public function getModifyFormInfo($memberSrl);

	/**
	 * @brief Get member list tpl
	 * @access public
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getListTpl()
	{
		return $this->getTpl('list.html');
	}

	/**
	 * @brief Get member info tpl
	 * @access public
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getInfoTpl()
	{
		return $this->getTpl('info.html');
	}

	/**
	 * @brief Get member insert tpl
	 * @access public
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getInsertTpl()
	{
		return $this->getTpl('insert.html');
	}

	/**
	 * @brief Get login preview tpl
	 * @access public
	 * @param $type login type (form, button ...)
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getPreviewTpl($type)
	{
		$tplFile = sprintf('preview.%s.html', $type);
		return $this->getTpl($tplFile);
	}

	/**
	 * @brief Get signin tpl
	 * @access public
	 * @param $type signin type (form, button ...)
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getSignInTpl($type)
	{
		$tplFile = sprintf('%s.html', $type);
		return $this->getTpl($tplFile);
	}

	/**
	 * @brief Get signup header tpl
	 * @access public
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getSignUpHeaderTpl()
	{
		return $this->getTpl('signup.header.html');
	}

	/**
	 * @brief Get signup footer tpl
	 * @access public
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getSignUpFooterTpl()
	{
		return $this->getTpl('signup.footer.html');
	}

	/**
	 * @brief Get member info tpl
	 * @access public
	 * @param $memberSrl
	 * @return stdClass
	 *	memberInfo
	 *	signupForm
	 *	extend_form_list
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getMemberInfoWithSignupForm($memberSrl)
	{
		return new stdClass();
	}

	/**
	 * @brief Get member info header tpl
	 * @access public
	 * @param $memberSrl
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getInfoHeaderTpl($memberSrl)
	{
		Context::set('memberSrl', $memberSrl);
		return $this->getTpl('info.header.html', FALSE);
	}

	/**
	 * @brief Get member info footer tpl
	 * @access public
	 * @param $memberSrl
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getInfoFooterTpl($memberSrl)
	{
		Context::set('memberSrl', $memberSrl);
		return $this->getTpl('info.footer.html', FALSE);
	}

	/**
	 * @brief Get member modify header tpl
	 * @access public
	 * @param $memberSrl
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getModifyInfoHeaderTpl($memberSrl)
	{
		Context::set('memberSrl', $memberSrl);
		return $this->getTpl('modifyInfo.header.html', FALSE);
	}

	/**
	 * @brief Get member modify footer tpl
	 * @access public
	 * @param $memberSrl
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getModifyInfoFooterTpl($memberSrl)
	{
		Context::set('memberSrl', $memberSrl);
		return $this->getTpl('modifyInfo.footer.html', FALSE);
	}

	/**
	 * @brief Get compiled template
	 * @access protected
	 * @param $tplFile
	 * @param $required if set 'false', return '' when not exist template file
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	protected function getTpl($tplFile, $required = TRUE)
	{
		$tplPath = sprintf('%stpl/', $this->getDriverPath());

		if(!$required && !is_readable(sprintf('%s%s', $tplPath, $tplFile)))
		{
			return '';
		}

		$oTemplate = TemplateHandler::getInstance();
		return $oTemplate->compile($tplPath, $tplFile);
	}

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

	/**
	 * @brief Get driver config view tpl
	 * @access public
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getConfigTpl()
	{
		$tplPath = sprintf('%stpl/', $this->getDriverPath());
		$tplFile = 'config.html';

		$oTemplate = TemplateHandler::getInstance();
		return $oTemplate->compile($tplPath, $tplFile);
	}

	/**
	 * @brief Get signup ruleset name
	 * @access public
	 * @param $noCheckFile if TRUE, no check file readable
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	final public function getSignupRuleset($noCheckFile = FALSE)
	{
		$ruleset = sprintf('member_driver_%s_signup', $this->getDriverName());
		$filePath = sprintf('./files/ruleset/%s.xml', $ruleset);
		if(!$noCheckFile && !is_readable($filePath))
		{
			$this->createSignupRuleset();
		}

		return $ruleset;
	}

	/**
	 * @brief Create signup ruleset
	 * @access protected
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	abstract protected function createSignupRuleset();

	/**
	 * @brief Get signin ruleset name
	 * @access public
	 * @param $noCheckFile if TRUE, no check file readable
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	final public function getSigninRulset($noCheckFile = FALSE)
	{
		$ruleset = sprintf('member_driver_%s_signin', $this->getDriverName());
		$filePath = sprintf('./files/ruleset/%s.xml', $ruleset);
		if(!$noCheckFile && !is_readable($filePath))
		{
			$this->createSigninRuleset();
		}

		return $ruleset;
	}

	/**
	 * @brief Create signin ruleset
	 * @access protected
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	abstract protected function createSigninRuleset();
	/**
	 * @brief Get admin insert member ruleset name
	 * @access public
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	final public function getAdminInsertRuleset($noCheckFile = FALSE)
	{
		$ruleset = sprintf('member_driver_%s_admininsert', $this->getDriverName());
		$filePath = sprintf('./files/ruleset/%s.xml', $ruleset);
		if(!$noCheckFile && !is_readable($filePath))
		{
			$this->createAdminInsertRuleset();
		}

		return $ruleset;
	}

	/**
	 * @brief Create admin insert ruleset
	 * @access protected
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	abstract protected function createAdminInsertRuleset();

	/**
	 * @brief Check ruleset
	 * @access protected
	 * @param $ruleset
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	protected function checkRuleset($ruleset)
	{
		$rulesetFile = sprintf('%sruleset/%s.xml', $this->getDriverPath(), $ruleset);
		if(!is_readable($rulesetFile))
		{
			return new Object();
		}

		$oValidator = new Validator($rulesetFile);
		$result = $oValidator->validate();

		if($result)
		{
			return new Object();
		}
		else
		{
			$lastError = $oValidator->getLastError();
			return new Object(-1, $lastError['msg']);
		}
	}

	/**
	 * @brief Get interface
	 * @access public
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	abstract public function getInterfaceNames();

	/**
	 * @brief insert join form
	 * @access public
	 * @param $args (form information)
	 * @param $isInsert (TRUE : insert, FALSE : update)
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function afterInsertJoinForm($args, $isInsert)
	{
		return new Object();
	}

	/**
	 * @brief delete join form
	 * @access public
	 * @param $args (form information)
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function afterDeleteJoinForm($args)
	{
		return new Object();
	}

	/**
	 * @brief get trigger object
	 * @access public
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getBeforeLoginTriggerObj()
	{
		$triggerObj = new stdClass();
		$triggerObj->driverName = $this->driverName;
		return $triggerObj;
	}


	/**
	 * @brief do sign up
	 * @access public
	 * @return boolean
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function doSignup()
	{
		return FALSE;
	}

	/**
	 * @brief Add member info from extra_vars and other information
	 * @access public
	 * @param $info object of member info
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function arrangeMemberInfo($info)
	{
		$info->profile_image = $this->getProfileImage($info->member_srl);
		$info->image_name = $this->getImageName($info->member_srl);
		$info->image_mark = $this->getImageMark($info->member_srl);
		$info->signature = $this->getSignature($info->member_srl);

		$extra_vars = unserialize($info->extra_vars);
		unset($info->extra_vars);
		if($extra_vars)
		{
			foreach($extra_vars as $key => $val)
			{
				if(!is_array($val) && preg_match('/\|\@\|/i', $val))
				{
					$val = explode('|@|', $val);
				}

				if(!$info->{$key})
				{
					$info->{$key} = $val;
				}
			}
		}

		return $info;
	}

	/**
	 * @brief getProfileImage
	 * @access public
	 * @param $memberSrl member srl
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getProfileImage($memberSrl)
	{
		return NULL;
	}

	/**
	 * @brief Get Image Name
	 * @access public
	 * @param $memberSrl member srl
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getImageName($memberSrl)
	{
		return NULL;
	}

	/**
	 * @brief Get Signature
	 * @access public
	 * @param $memberSrl member srl
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getSignature($memberSrl)
	{
		return NULL;
	}

	/**
	 * @brief Check values when member joining
	 * @access public
	 * @param $name
	 * @param $value
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function isValidateValue($name, $value)
	{
		return new Object();
	}

	/**
	 * @brief Check values when member insert
	 * @access public
	 * @param $args
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function isValidateAdminInsert($args)
	{
		return new Object();
	}
	/**
	 * @brief disp remove member info
	 * @access public
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function dispUnregister()
	{
		return new Object();
	}

	/**
	 * @brief check validate unregister account before
	 * @access public
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function isValidateUnregister($args)
	{
		return new Object();
	}

	/**
	 * @brief create ruleset
	 * @access protected
	 * @param $name name of ruleset
	 * @param $fields array of <field> node
	 * @param $customrules array of <customrule> node
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	final protected function createRuleset($name, $fields, $customrules = array())
	{
		$xmlFile = sprintf('./files/ruleset/%s.xml', $name);
		$buff = $this->getCommonRulesetFormat();

		$buff = sprintf($buff, implode('', $customrules), implode('', $fields));
		Filehandler::writeFile($xmlFile, $buff);

		$validator   = new Validator($xmlFile);
		$validator->setCacheDir('files/cache');
		$validator->getJsPath();
	}

	/**
	 * @brief Get common ruleset format
	 * @access private
	 * @return String
	 * @developer NHN (developers@xpressengine.com)
	 */
	private function getCommonRulesetFormat()
	{
		$buff = '<?xml version="1.0" encoding="utf-8"?>'
				. '<ruleset version="1.5.0">'
				. '<customrules>%s</customrules>'
				. '<fields>%s</fields>'
				. '</ruleset>';

		return $buff;
	}
}

/* End of file MemberDriver.php */
/* Location: ./modules/member/classes/MemberDriver.php */
