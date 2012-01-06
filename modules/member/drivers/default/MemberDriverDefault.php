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
	public function checkUpdate()
	{
		return FALSE;
	}

	/**
	 * @brief Update for driver
	 * @access public
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function updateDriver()
	{
		return new Object();
	}

	/**
	 * @brief get member information
	 * @access public
	 * @param $memberSrl
	 * @return memberVO
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getMemberInfo($memberSrl)
	{
		return new Object();
	}

	/**
	 * @brief Insert member
	 * @access public
	 * @param $memberInfo Information must have below variables.
	 *	email_address
	 *	password
	 *	nick_name
	 * @param $passwordIsHashed
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function insertMember($memberInfo, $passwordIsHashed = FALSE)
	{
		if(!isset($memberInfo->member_srl, $memberInfo->email_address, $memberInfo->password, $memberInfo->nick_name))
		{
			return new Object(-1, 'msg_missing_required_value');
		}

		// Terms and Conditions portion of the information set up by members reaffirmed
		$oModuleModel = getModel('module');
		$config = $oModuleModel->getDriverConfig('member', 'default');

		$oMemberModel = getModel('member');
		$commonConfig = $oMemberModel->getMemberConfig();

		// Set the user state as "denied" when using mail authentication
		if ($config->enable_confirm == 'Y') $args->denied = 'Y';
		// Add extra vars after excluding necessary information from all the requested arguments
		$extra_vars = delObjectVars($all_args, $args);
		$args->extra_vars = serialize($extra_vars);
		// Execute insert or update depending on the value of member_srl

		if (!$args->user_id) $args->user_id = 't'.$args->member_srl;
		if (!$args->user_name) $args->user_name = $args->member_srl;
		if (!$args->nick_name) $args->nick_name = $args->member_srl;

		// remove whitespace
		$checkInfos = array('user_id', 'nick_name', 'email_address');
		$replaceStr = array("\r\n", "\r", "\n", " ", "\t", "\xC2\xAD");
		foreach($checkInfos as $val){
			if(isset($args->{$val})){
				$args->{$val} = str_replace($replaceStr, '', $args->{$val});
			}
		}
		// $commonArgs = new stdClass();
		$args = new stdClass();

		$args->member_srl = $memberInfo->member_srl;
		
		// Enter the user's identity changed to lowercase
		if(!$memberInfo->user_id)
		{
			$args->user_id = 't' . $args->member_srl;
		}
		else
		{
			$args->user_id = strtolower($memberInfo->user_id);
		}

		if(!$memberInfo->user_name)
		{
			$args->user_name = $args->member_srl;
		}

		list($args->email_id, $args->email_host) = explode('@', $memberInfo->email_address);
		
		// Website, blog, checks the address
		if($memberInfo->homepage && !preg_match("/^[a-z]+:\/\//i", $memberInfo->homepage))
		{
			$args->homepage = 'http://' . $memberInfo->homepage;
		}
		if($memberInfo->blog && !preg_match("/^[a-z]+:\/\//i", $memberInfo->blog))
		{
			$args->blog = 'http://' . $memberInfo->blog;
		}

		// ID check is prohibited
		if($this->isDeniedID($memberInfo->user_id)) 
		{
			return new Object(-1, 'denied_user_id');
		}

		$oMemberModel = getModel('member');
		// ID, nickname, email address of the redundancy check
		$isExist = $oMemberModel->getMemberSrlByUserID($memberInfo->user_id);
		if($isExist)
		{
			return new Object(-1, 'msg_exists_user_id');
		}

		$isExist = $oMemberModel->getMemberSrlByNickName($memberInfo->nick_name);
		if($isExist)
		{
			return new Object(-1, 'msg_exists_nick_name');
		}

		$isExist = $oMemberModel->getMemberSrlByEmailAddress($memberInfo->email_address);
		if($isExist)
		{
			return new Object(-1, 'msg_exists_email_address');
		}

		// Insert data into the DB
		$args->nick_name = htmlspecialchars($memberInfo->nick_name);
		$args->homepage = htmlspecialchars($memberInfo->homepage);
		$args->blog = htmlspecialchars($memberInfo->blog);

		if($memberInfo->password && !$passwordIsHashed)
		{
			$args->password = md5($memberInfo->password);
		}
		elseif(!$memberInfo->password)
		{
			unset($args->password);
		}


		// When using email authentication mode (when you subscribed members denied a) certified mail sent
		if($config->enable_auth_mail == 'Y')
		{
			$args->waitAuth = 'Y';
			// Insert data into the authentication DB
			$auth_args->user_id = $args->user_id;
			$auth_args->member_srl = $args->member_srl;
			$auth_args->new_password = $args->password;
			$auth_args->auth_key = md5(rand(0, 999999));
			$auth_args->is_register = 'Y';

			$output = executeQuery('member.default.insertAuthMail', $auth_args);
			if(!$output->toBool()) 
			{
				return $output;
			}

			// Get content of the email to send a member
			Context::set('auth_args', $auth_args);

			global $lang;
			if(is_array($config->signupForm))
			{
				$exceptForm = array('password', 'find_account_question');
				foreach($config->signupForm as $form)
				{
					if(!in_array($form->name, $exceptForm) && $form->isDefaultForm && ($form->required || $form->mustRequired))
					{
						$authMemberInfo[$lang->{$form->name}] = $args->{$form->name};
					}
				}
			}
			else
			{
				$authMemberInfo[$lang->user_id] = $args->user_id;
				$authMemberInfo[$lang->user_name] = $args->user_name;
				$authMemberInfo[$lang->nick_name] = $args->nick_name;
				$authMemberInfo[$lang->email_address] = $args->email_address;
			}
			Context::set('memberInfo', $authMemberInfo);
			Context::set('member_config', $commonConfig);

			$tpl_path = $this->getDriverTplPath();
			if(!is_dir($tpl_path))
			{
				$tpl_path = sprintf('%sskins/%s', $this->getModulePath(), 'default');
			}

			$auth_url = getFullUrl('', 'module', 'member', 'act', 'procMemberAuthAccount', 'member_srl', $args->member_srl, 'auth_key', $auth_args->auth_key);
			Context::set('auth_url', $auth_url);

			$oTemplate = &TemplateHandler::getInstance();
			$content = $oTemplate->compile($tpl_path, 'confirm_member_account_mail');

			// Send a mail
			$oMail = new Mail();
			$oMail->setTitle(Context::getLang('msg_confirm_account_title'));
			$oMail->setContent($content);
			$oMail->setSender($commonConfig->webmaster_name ? $commonConfig->webmaster_name : 'webmaster', $commonConfig->webmaster_email);
			$oMail->setReceiptor($args->user_name, $args->email_address);
			$oMail->send();
		}

		$output = executeQuery('member.default.insertMember', $args);
		if(!$output->toBool())
		{
			return $output;
		}

		$result = new Object();
		$result->add('memberSrl', $args->member_srl);
		return $result;
	}

	/**
	 * @brief Delete member
	 * @access public
	 * @param $memberSrl
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function deleteMember($memberSrl)
	{
		if(!isset($memberSrl))
		{
			return new Object(-1, 'msg_missing_required_value');
		}
	}

	/**
	 * @brief Update member info
	 * @access public
	 * @param $memberInfo update member information (type of stdClass) 
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function updateMember($memberInfo)
	{
		if(!isset($memberInfo->memberSrl))
		{
			return new Object(-1, 'msg_missing_required_value');
		}
	}

	/**
	 * @brief Validate Login Info
	 * @access public
	 * @param $loginInfo login information (ex : user_id/email_address, password)
	 * @return boolean
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function validateLoginInfo($loginInfo)
	{
		if(!isset($loginInfo->user_id, $loginInfo->password))
		{
			return new Object(-1, 'msg_missing_required_value');
		}
	}

	/**
	 * @brief Verify if ID is denied
	 * @access public
	 * @param $userId
	 * @return boolean
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function isDeniedID($userId)
	{
		$args->user_id = $userId;
		$output = executeQuery('member.default.chkDeniedID', $args);
		return ($output->data->count) ? TRUE : FALSE;
	}

	public function extractExtraVars($memberInfo)
	{
		$extraVars = parent::extractExtraVars($memberInfo);

		$extractVars = array('user_id', 'email_address', 'password', 'user_name', 'nick_name', 'find_account_question', 'fund_account_answer', 'homepage', 'blog', 'birthday');
		
		foreach($extractVars as $column)
		{
			unset($extraVars->{$column});
		}

		return $extraVars;
	}
}
/* End of file MemberDriverDefault.php */
/* Location: ./modules/member/driver/xe/MeberDriverDefault.php */
