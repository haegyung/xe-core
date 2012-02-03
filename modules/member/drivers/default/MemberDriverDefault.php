<?php

require_once _XE_PATH_ . 'modules/member/drivers/default/MemberVoDefault.php';

/**
 * @brief class of XE driver
 * @developer NHN (developers@xpressengine.com)
 */
class MemberDriverDefault extends MemberDriver
{
	/**
	 * @brief Get interface
	 * @access public
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getInterfaceNames()
	{
		$interface->AdminView = array();
		$interface->AdminController = array('procInsertDeniedId', 'procUpdateDeniedId', 'procSaveConfig', 'procSaveSignUpForm');
		$interface->View = array('dispModifyEmailAddress', 'dispUnregister');
		$interface->Controller = array('procChangeEmailAddress', 'procAuthEmailAddress', 'procUnregister');

		return $interface;
	}

	/**
	 * @brief Install Driver
	 * @access public
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function installDriver()
	{
		global $lang;
		$oMemberModel = &getModel('member');
		$identifier = 'email_address';

		$args->signupForm = $this->createSignupForm();
		$args->identifier = $identifier;

		$args->image_name = 'Y';
		$args->image_mark = 'Y';
		$args->profile_image = 'Y';
		$args->image_name_max_width = '90';
		$args->image_name_max_height = '20';
		$args->image_mark_max_width = '20';
		$args->image_mark_max_height = '20';
		$args->profile_image_max_width = '80';
		$args->profile_image_max_height = '80';

		$oModuleController = getController('module');
		$oModuleController->insertDriverConfig('member', 'default', $args);

		// Create Ruleset File
		$this->createSignupRuleset();
		$this->createSigninRuleset();
		$this->createFindAccountByQuestion();

		// Configure administrator information
		$oMemberAdminController = getAdminController('member');
		$oMemberController = getController('member');
		$admin_args->is_admin = 'Y';
		$output = executeQuery('member.driver.default.getMemberList', $admin_args);
		if(!$output->data)
		{
			$admin_info = Context::gets('user_id', 'password', 'nick_name', 'user_name', 'email_address');
			if($admin_info->user_id)
			{
				// Insert admin information
				$output = $oMemberAdminController->insertAdmin($admin_info);
				if(!$output->toBool())
				{
					return $output;
				}

				// Log-in Processing
				$output = $oMemberController->doSignin('default', $admin_info->member_srl);
				if(!$output->toBool())
				{
					return $output;
				}
			}
		}


		// Register denied ID(default + module name)
		$oModuleModel = &getModel('module');
		$module_list = $oModuleModel->getModuleList();
		foreach($module_list as $key => $val)
		{
			$this->insertDeniedID($val->module, '');
		}
		$this->insertDeniedID('www', '');
		$this->insertDeniedID('root', '');
		$this->insertDeniedID('administrator', '');
		$this->insertDeniedID('telnet', '');
		$this->insertDeniedID('ftp', '');
		$this->insertDeniedID('http', '');

		// Create cache directory to use in the member module
		FileHandler::makeDir('./files/member_extra_info/image_name');
		FileHandler::makeDir('./files/member_extra_info/image_mark');
		FileHandler::makeDir('./files/member_extra_info/profile_image');
		FileHandler::makeDir('./files/member_extra_info/signature');

		return new Object();
	}

	/**
	 * @brief Check update for driver
	 * @access public
	 * @return boolean
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function checkUpdate()
	{
		$oModuleModel = getModel('module');
		$config = $oModuleModel->getDriverConfig('member', 'default');
		$oDB = &DB::getInstance();

		$output = executeQuery('member.getCommonCount');
		if(!$output->data->count)
		{
			return TRUE;
		}

		if(!$oDB->isColumnExists('member', 'wait_auth'))
		{
			return TRUE;
		}

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
		// Check member directory
		FileHandler::makeDir('./files/member_extra_info/image_name');
		FileHandler::makeDir('./files/member_extra_info/image_mark');
		FileHandler::makeDir('./files/member_extra_info/signature');
		FileHandler::makeDir('./files/member_extra_info/profile_image');

		$output = executeQuery('member.getCommonCount');
		if(!$output->data->count)
		{
			$output = executeQuery('member.moveToCommon');

			if(!$output->toBool())
			{
				return $output;
			}
		}

		$oDB = &DB::getInstance();

		if(!$oDB->isColumnExists('member', 'wait_auth'))
		{
			$oDB->addColumn('member', 'wait_auth', 'varchar', 2, 'N', TRUE);
		}

		return new Object();
	}

	/**
	 * @brief create signup form
	 * @access private
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	private function createSignupForm()
	{
		global $lang;
		// Get join form list which is additionally set
		$oMemberModel = getModel('member');
		$extendItems = $oMemberModel->getJoinFormListByDriver('default');

		$identifier = 'user_id';
		$items = array('user_id', 'password', 'user_name', 'nick_name', 'email_address', 'find_account_question', 'homepage', 'blog', 'birthday', 'signature', 'profile_image', 'image_name', 'image_mark');
		$mustRequireds = array('email_address', 'nick_name', 'password', 'find_account_question');
		$orgRequireds = array('email_address', 'password', 'find_account_question', 'user_id', 'nick_name', 'user_name');
		$orgUse = array('email_address', 'password', 'find_account_question', 'user_id', 'nick_name', 'user_name', 'homepage', 'blog', 'birthday');
		$list_order = array();
		foreach($items as $key)
		{
			unset($signupItem);
			$signupItem->isDefaultForm = TRUE;
			$signupItem->name = $key;
			$signupItem->title = $key;
			$signupItem->mustRequired = in_array($key, $mustRequireds);
			$signupItem->imageType = (strpos($key, 'image') !== FALSE);
			$signupItem->required = in_array($key, $orgRequireds) || $signupItem->mustRequired;
			$signupItem->isUse = ($config->{$key} == 'Y') || in_array($key, $orgUse) || $signupItem->required;
			$signupItem->isIdentifier = ($key == $identifier);
			if($signupItem->imageType)
			{
				$signupItem->max_width = $config->{$key . '_max_width'};
				$signupItem->max_height = $config->{$key . '_max_height'};
			}
			if($signupItem->isIdentifier)
			{
				array_unshift($list_order, $signupItem);
			}
			else
			{
				$list_order[] = $signupItem;
			}
		}

		if(is_array($extendItems))
		{
			foreach($extendItems as $form_srl => $item_info)
			{
				unset($signupItem);
				$signupItem->name = $item_info->column_name;
				$signupItem->title = $item_info->column_title;
				$signupItem->type = $item_info->column_type;
				$signupItem->member_join_form_srl = $form_srl;
				$signupItem->mustRequired = FALSE;
				$signupItem->required = ($item_info->required == 'Y');
				$signupItem->isUse = ($item_info->is_active == 'Y') || $signupItem->required;
				$signupItem->description = $item_info->description;
				if($signupItem->imageType)
				{
					$signupItem->max_width = $config->{$key . '_max_width'};
					$signupItem->max_height = $config->{$key . '_max_height'};
				}
				$list_order[] = $signupItem;
			}
		}

		return $list_order;
	}

	/**
	 * @brief get member vo
	 * @access public
	 * @param $memberSrl
	 * @return MemberVO
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getMemberVo($memberSrl)
	{
		$args->member_srl = $memberSrl;
		$output = executeQuery('member.driver.default.getMemberInfoByMemberSrl', $args);
		if(!$output->toBool())
		{
			throw new MemberDriverException($output->getMessage());
		}

		if(!$output->data)
		{
			throw new MemberDriverException('invalid_user_id');
		}

		$memberVo = new MemberVoDefault();
		$memberVo->setMemberInfo($output->data);
		return $memberVo;
	}

	/**
	 * @brief Insert member
	 * @access public
	 * @param $memberInfo Information must have below variables.
	 *	email_address
	 *	password
	 *	nick_name
	 * @param $passwordIsHashed
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function insertMember($memberInfo, $passwordIsHashed = FALSE)
	{
		if(!isset($memberInfo->member_srl, $memberInfo->email_address, $memberInfo->password, $memberInfo->nick_name))
		{
			return new Object(-1, 'msg_missing_required_value');
		}

		// Terms and Conditions portion of the information set up by members reaffirmed
		$config = $this->getConfig();

		$oMemberModel = getModel('member');
		$commonConfig = $oMemberModel->getMemberConfig();

		$args = new stdClass();

		// Set the user state as "denied" when using mail authentication
		if($commonConfig->enable_confirm == 'Y')
		{
			$args->denied = 'Y';
		}

		// Insert data into the DB
		$args->allow_mailing = ($args->allow_mailing != 'Y') ? 'N' : 'Y';
		$args->member_srl = $memberInfo->member_srl;
		$args->user_id = strtolower($memberInfo->user_id);
		$args->user_name = $memberInfo->user_name;
		$args->email_address = $memberInfo->email_address;
		$args->nick_name = htmlspecialchars($memberInfo->nick_name);
		$args->homepage = htmlspecialchars($memberInfo->homepage);
		$args->blog = htmlspecialchars($memberInfo->blog);

		// Execute insert or update depending on the value of member_srl
		if(!$args->user_id)
		{
			$args->user_id = 't' . $args->member_srl;
		}
		if(!$args->user_name)
		{
			$args->user_name = $args->member_srl;
		}
		if(!$args->nick_name)
		{
			$args->nick_name = $args->member_srl;
		}

		// remove whitespace
		$checkInfos = array('user_id', 'nick_name', 'email_address');
		$replaceStr = array("\r\n", "\r", "\n", " ", "\t", "\xC2\xAD");
		foreach($checkInfos as $val)
		{
			if(isset($args->{$val}))
			{
				$args->{$val} = str_replace($replaceStr, '', $args->{$val});
			}
		}

		// make email id, host
		list($args->email_id, $args->email_host) = explode('@', $memberInfo->email_address);

		// Website, blog, checks the address
		if($args->homepage && !preg_match("/^[a-z]+:\/\//i", $args->homepage))
		{
			$args->homepage = 'http://' . $args->homepage;
		}
		if($args->blog && !preg_match("/^[a-z]+:\/\//i", $args->blog))
		{
			$args->blog = 'http://' . $args->blog;
		}

		// ID check is prohibited
		if($this->isDeniedID($args->user_id))
		{
			return new Object(-1, 'denied_user_id');
		}

		// ID, nickname, email address of the redundancy check
		$isExist = $this->getMemberSrlByUserID($memberInfo->user_id);
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


		if($memberInfo->password && !$passwordIsHashed)
		{
			$args->password = md5($memberInfo->password);
		}
		elseif(!$memberInfo->password)
		{
			unset($args->password);
		}

		// Add extra vars after excluding necessary information from all the requested arguments
		$extra_vars = delObjectVars($memberInfo, $args);
		$args->extra_vars = serialize($extra_vars);
		$oDB = &DB::getInstance();
		$oDB->begin();

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

			$output = executeQuery('member.driver.default.insertAuthMail', $auth_args);
			if(!$output->toBool())
			{
				$oDB->rollback();
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

		$output = executeQuery('member.driver.default.insertMember', $args);
		if(!$output->toBool())
		{
			$oDB->rollback();
			return $output;
		}

		$oDB->commit(TRUE);

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

		// Delete the entries in member_auth_mail
		$args->member_srl = $memberSrl;
		$output = executeQuery('member.driver.default.deleteAuthMail', $args);
		if (!$output->toBool())
		{
			return $output;
		}

		// member removed from the table
		$output = executeQuery('member.driver.default.deleteMember', $args);
		if(!$output->toBool())
		{
			return $output;
		}

		// Name, image, image, mark, sign, delete
		$this->procMemberDeleteProfileImage();
		$this->procMemberDeleteImageName();
		$this->procMemberDeleteImageMark();
		$this->delSignature($member_srl);
		return new Object();
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
		if(!isset($memberInfo->member_srl))
		{
			return new Object(-1, 'msg_missing_required_value');
		}

		// Extract the necessary information in advance
		$config = $this->getConfig();
		$getVars = array('find_account_answer','allow_mailing');
		if ($config->signupForm)
		{
			foreach($config->signupForm as $formInfo)
			{
				if($formInfo->isDefaultForm && ($formInfo->isUse || $formInfo->required || $formInfo->mustRequired))
				{
					$getVars[] = $formInfo->name;
				}
			}
		}

		foreach($getVars as $val)
		{
			$args->{$val} = $memberInfo->{$val};
		}

		$logged_info = Context::get('logged_info');
		// Login Information
		$args->member_srl = $memberInfo->member_srl;
		// Remove some unnecessary variables from all the vars
		unset($memberInfo->module);
		unset($memberInfo->act);
		unset($memberInfo->is_admin);
		unset($memberInfo->description);
		unset($memberInfo->group_srl_list);
		unset($memberInfo->body);
		unset($memberInfo->accept_agreement);
		unset($memberInfo->signature);
		unset($memberInfo->_filter);
		unset($memberInfo->mid);
		unset($memberInfo->error_return_url);
		unset($memberInfo->ruleset);
		unset($memberInfo->password);

		// Add extra vars after excluding necessary information from all the requested arguments
		$extra_vars = delObjectVars($memberInfo, $args);
		$args->extra_vars = serialize($extra_vars);
		// Create a member model object
		$oMemberModel = &getModel('member');

		// remove whitespace
		$checkInfos = array('user_id', 'nick_name', 'email_address');
		$replaceStr = array("\r\n", "\r", "\n", " ", "\t", "\xC2\xAD");
		foreach($checkInfos as $val)
		{
			if(isset($args->{$val}))
			{
				$args->{$val} = str_replace($replaceStr, '', $args->{$val});
			}
		}

		if($args->allow_mailing != 'Y')
		{
			$args->allow_mailing = 'N';
		}

		if ($config->identifier == 'email_address')
		{
			$orgMemberInfo = $this->getMemberInfoByEmailAddress($args->email_address);
			if($orgMemberInfo && $args->member_srl != $orgMemberInfo->member_srl)
			{
				return new Object(-1, 'msg_exists_email_address');
			}

			$args->email_address = ($logged_info->is_admin != 'Y') ? $orgMemberInfo->email_address : $args->email_address;
		}
		else
		{
			$orgMemberInfo = $this->getMemberInfoByUserID($args->user_id);
			if($orgMemberInfo && $args->member_srl != $orgMemberInfo->member_srl)
			{
				return new Object(-1, 'msg_exists_email_address');
			}

			$args->user_id = ($logged_info->is_admin != 'Y') ? $orgMemberInfo->user_id : $args->user_id;
		}

		list($args->email_id, $args->email_host) = explode('@', $args->email_address);
		// Website, blog, checks the address
		if($args->homepage && !preg_match("/^[a-z]+:\/\//is",$args->homepage))
		{
			$args->homepage = 'http://'.$args->homepage;
		}
		if($args->blog && !preg_match("/^[a-z]+:\/\//is",$args->blog))
		{
			$args->blog = 'http://'.$args->blog;
		}

		if($args->password)
		{
			$args->password = md5($args->password);
		}

		// Execute insert or update depending on the value of member_srl
		$output = executeQuery('member.driver.default.updateMember', $args);
		if(!$output->toBool())
		{
			return $output;
		}

		$profile_image = $_FILES['profile_image'];
		if (is_uploaded_file($profile_image['tmp_name'])){
			$this->insertProfileImage($args->member_srl, $profile_image['tmp_name']);
		}

		$image_mark = $_FILES['image_mark'];
		if (is_uploaded_file($image_mark['tmp_name'])){
			$this->insertImageMark($args->member_srl, $image_mark['tmp_name']);
		}

		$image_name = $_FILES['image_name'];
		if (is_uploaded_file($image_name['tmp_name'])){
			$this->insertImageName($args->member_srl, $image_name['tmp_name']);
		}

		// Save Signature
		$signature = Context::get('signature');
		$this->putSignature($args->member_srl, $signature);

		return new Object();
	}

	/**
	 * @brief Validate Login Info
	 * @access public
	 * @param $loginInfo login information (ex : user_id/email_address, password)
	 * @return $memberVo
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function validateLoginInfo($loginInfo)
	{
		if(!isset($loginInfo->user_id, $loginInfo->password))
		{
			throw new MemberDriverException('msg_missing_required_value');
		}

		$userId = $loginInfo->user_id;
		$password = $loginInfo->password;

		// Create a member model object
		$oMemberModel = &getModel('member');

		// check identifier
		$config = $this->getConfig();
		$output = $this->getMemberInfoByIdentifier($userId, $config->identifier);
		$data = $output->data;

		if(!$output->toBool())
		{
			throw new MemberDriverException($output->getMessage());
		}

		if(!$data)
		{
			throw new MemberDriverException('invalid_user_id');
		}

		// Set an invalid user if no value returned
		if(strtolower($data->{$config->identifier}) != strtolower($userId))
		{
			throw new MemberDriverException('invalid_' . $config->identifier);
		}

		// Password Check
		if($password && !$this->isValidPassword($data->password, $password))
		{
			throw new MemberDriverException('invalid_password');
		}

		// If denied == 'Y', notify
		if($data->denied == 'Y')
		{
			unset($args);
			$args->member_srl = $data->member_srl;
			$output = executeQuery('member.driver.default.chkAuthMail', $args);
			if($output->toBool() && $output->data->count != '0')
			{
				throw new MemberDriverException('msg_user_not_confirmed');
			}
			throw new MemberDriverException('msg_user_denied');
		}

		$memberVo = new MemberVoDefault($data);

		return $memberVo;
	}

	/**
	 * @brief do signin
	 * @access public
	 * @param $memberSrl
	 * @return boolean
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function doSignin($memberSrl)
	{
		$oMemberVo = $this->getMemberVo($memberSrl);
		if(!$oMemberVo)
		{
			throw new MemberDriverException('msg_invalid_request');
		}

		$config = $this->getConfig();

		$memberInfo = $oMemberVo->getMemberInfo();
		$userId = $memberInfo->{$config->identifier};
		$password = $oMemberVo->getPassword();

		// Check change_password_date
		$limit_date = $config->change_password_date;

		// Check if change_password_date is set
		if($limit_date > 0)
		{
			if($oMemberVo->getChangePasswordDate('YmdHis') < date ('YmdHis', strtotime ('-' . $limit_date . ' day')))
			{
				$url = getNotEncodedUrl('', 'vid', Context::get('vid'), 'mid', Context::get('mid'), 'act', 'dispMemberModifyPassword');
				throw new MemberDriverException($url, MemberDriverException::REDIRECT);
			}
		}

		// When user checked to use auto-login
		if($loginInfo->keep_signed)
		{
			// Key generate for auto login
			$autologin_args->autologin_key = md5(strtolower($userId) . $password . $_SERVER['REMOTE_ADDR']);
			$autologin_args->member_srl = $loginInfo->member_srl;
			executeQuery('member.deleteAutologin', $autologin_args);
			$autologin_output = executeQuery('member.insertAutologin', $autologin_args);
			if($autologin_output->toBool())
			{
				setCookie('xeak', $autologin_args->autologin_key, time() + 60 * 60 * 24 * 365, '/');
			}
		}

		return TRUE;
	}

	/**
	 * @brief Return member information with identifier
	 * @access private
	 * @param $target
	 * @param $identifier
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	private function getMemberInfoByIdentifier($target, $identifier)
	{
		if(!$target)
		{
			return new Object(-1, 'msg_invalid_request');
		}

		if($identifier == 'user_id')
		{
			$args->user_id = $target;
			$output = executeQuery('member.driver.default.getMemberInfo', $args);
		}
		else
		{
			$args->email_address = $target;
			$output = executeQuery('member.driver.default.getMemberInfoByEmailAddress', $args);
		}
		return $output;
	}

	/**
	 * @brief Compare plain text password to the password saved in DB
	 * @access private
	 * @param $hashedPassword
	 * @param $passwordText
	 * @return Blooean
	 * @developer NHN (developers@xpressengine.com)
	 */
	private function isValidPassword($hashedPassword, $passwordText)
	{
		// False if no password in entered
		if(!$passwordText)
		{
			return FALSE;
		}
		// Return TRUE if the user input is equal to md5 hash value
		if($hashedPassword == md5($passwordText))
		{
			return TRUE;
		}
		// Return TRUE if the user input is equal to the value of mysql_pre4_hash_password
		if(mysql_pre4_hash_password($passwordText) == $hashedPassword)
		{
			return TRUE;
		}
		// Verify the password by using old_password if the current db is MySQL. If correct, return TRUE.
		if(substr(Context::getDBType(), 0, 5) == 'mysql')
		{
			$oDB = &DB::getInstance();
			if($oDB->isValidOldPassword($passwordText, $hashedPassword))
			{
				return TRUE;
			}
		}

		return FALSE;
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
		$output = executeQuery('member.driver.default.chkDeniedID', $args);
		return ($output->data->count) ? TRUE : FALSE;
	}

	/**
	 * @brief Extract extravars
	 * @access public
	 * @param $memberInfo
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function extractExtraVars($memberInfo)
	{
		$extraVars = parent::extractExtraVars($memberInfo);

		$extractVars = array('user_id', 'email_address', 'password', 'user_name', 'nick_name', 'find_account_question', 'find_account_answer', 'homepage', 'blog', 'birthday');

		foreach($extractVars as $column)
		{
			unset($extraVars->{$column});
		}

		return $extraVars;
	}

	/**
	 * @brief Get member signin tpl
	 * @access public
	 * @param $type
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getSigninTpl($type)
	{
		Context::set('oDriver', $this);

		return parent::getSigninTpl($type);
	}

	/**
	 * @brief Get member list tpl
	 * @access public
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getListTpl()
	{
		// make filter title
		switch($filter)
		{
			case 'super_admin':
				Context::set('filter_type_title', Context::get('cmd_show_super_admin_member'));
				break;
			case 'site_admin':
				Context::set('filter_type_title', Context::get('cmd_show_site_admin_member'));
				break;
			case 'enable':
				Context::set('filter_type_title', Context::get('approval'));
				break;
			case 'disable':
				Context::set('filter_type_title', Context::get('denied'));
				break;
			default:
				Context::set('filter_type_title', Context::get('cmd_show_all_member'));
				break;
		}

		// get member list
		$output = $this->getMemberList();

		// combine group info
		$oMemberModel = getModel('member');
		if($output->data)
		{
			foreach($output->data as $key => $member)
			{
				$output->data[$key]->group_list = $oMemberModel->getMemberGroups($member->member_srl, 0);
			}
		}

		$config = $this->getConfig();
		$memberIdentifiers = array('user_id' => 'user_id', 'user_name' => 'user_name', 'nick_name' => 'nick_name');
		$usedIdentifiers = array();

		if (is_array($config->signupForm))
		{
			foreach($config->signupForm as $signupItem)
			{
				if (!count($memberIdentifiers))
				{
					break;
				}
				if(in_array($signupItem->name, $memberIdentifiers))
				{
					if($signupItem->required || $signupItem->isUse)
					{
						$usedIdentifiers[$signupItem->name] = Context::getLang($signupItem->name);
					}
					unset($memberIdentifiers[$signupItem->name]);
				}
			}
		}

		Context::set('driverInfo', $this->getDriverInfo());
		Context::set('total_count', $output->total_count);
		Context::set('total_page', $output->total_page);
		Context::set('page', $output->page);
		Context::set('member_list', $output->data);
		Context::set('usedIdentifiers', $usedIdentifiers);
		Context::set('page_navigation', $output->page_navigation);

		$security = new Security();
		$security->encodeHTML('member_list..user_name', 'member_list..nick_name', 'member_list..group_list..');
		return parent::getListTpl();
	}

	/**
	 * @brief Get signup tpl
	 * @access public
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getSignUpTpl()
	{
		$config = $this->getConfig();
		Context::set('member_config', $config);

		$oMemberAdminView = &getAdminView('member');
		$formTags = $this->getMemberInputTag();
		Context::set('formTags', $formTags);

		$identifierForm->title = Context::getLang($config->identifier);
		$identifierForm->name = $config->identifier;
		$identifierForm->value = $member_info->{$config->identifier};
		Context::set('identifierForm', $identifierForm);

		return parent::getSignUpTpl();
	}

	/**
	 * @brief Get memberInfo tpl
	 * @access public
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getInfoTpl()
	{
		$oMemberModel = &getModel('member');
		$oModuleModel = &getModel('module');
		$member_config = $this->getConfig('member');

		$member_srl = Context::get('member_srl');
		$memberInfo = $this->getMemberInfoByMemberSrl($member_srl);

		Context::set('member_config', $member_config);
		$extendForm = $oMemberModel->getCombineJoinForm($memberInfo);

		Context::set('extend_form_list', $extendForm);
		$memberInfo = get_object_vars($memberInfo);

		if (!is_array($memberInfo['group_list']))
		{
			$memberInfo['group_list'] = array();
		}
		Context::set('memberInfo', $memberInfo);

		$disableColumns = array('password', 'find_account_question');
		Context::set('disableColumns', $disableColumns);

		$security = new Security();
		$security->encodeHTML('member_config..');
		$security->encodeHTML('memberInfo.user_name', 'memberInfo.nick_name', 'memberInfo.description','memberInfo.group_list..');
		$security->encodeHTML('extend_form_list...');
		return parent::getInfoTpl();
	}

	/**
	 * @brief Get member insert tpl
	 * @access public
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getInsertTpl()
	{
		$memberSrl = Context::get('member_srl');
		$formTags = $this->getAdminFormInfo($memberSrl);
		Context::set('formTags', $formTags);
		return parent::getInsertTpl();
	}
	/**
	 * @brief Get memberInfo tpl
	 * @access public
	 * @param $memberSrl
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getMemberInfoWithSignupForm($memberSrl)
	{
		$output = new stdClass();

		$columnList = array('member.member_srl', 'member.user_id', 'member.email_address', 'member.user_name', 'member.nick_name', 'member.homepage', 'member.blog', 'member.birthday', 'member.extra_vars');
		$memberInfo = $this->getMemberInfoByMemberSrl($memberSrl, $columnList);
		$member_config = $this->getConfig();

		if(is_array($member_config->signupForm))
		{
			global $lang;
			foreach($member_config->signupForm AS $key => $value)
			{
				if($lang->{$value->title})
				{
					$member_config->signupForm[$key]->title = $lang->{$value->title};
				}
			}
		}

		$output->signupForm = $member_config->signupForm;

		$oMemberModel = getModel('member');
		$extendForm = $oMemberModel->getCombineJoinForm($memberInfo);
		unset($extendForm->find_member_account);
		unset($extendForm->find_member_answer);

		$output->extend_form_list = $extendForm;

		$memberInfo = get_object_vars($memberInfo);
		$output->memberInfo = $memberInfo;

		return $output;
	}

	/**
	 * @brief get member signup form format
	 * @access public
	 * @param $memberInfo (when modify member_info of modified target member)
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getSignupFormInfo($memberInfo = NULL)
	{
		$formTags = $this->getFormInfo();

		$member_config = $this->getConfig();

		// insert identifier
		$identifierTag->required = TRUE;
		$identifierTag->title = Context::getLang($member_config->identifier);
		$identifierTag->description = Context::getLang('about_' . $member_config->identifier);
		$identifierTag->inputTag = sprintf('<input type="text" name="%s" value="%s" />', $member_config->identifier, $memberInfo[$member_config->identifier]);

		// insert password field
		$passwdTag->required = TRUE;
		$passwdTag->title = Context::getLang('password');
		$passwdTag->description = Context::getLang('about_password');
		$passwdTag->inputTag = '<input type="password" name="password" value="" />';

		$passwd2Tag->required = TRUE;
		$passwd2Tag->title = Context::getLang('retype_password');
		$passwd2Tag->description = '';
		$passwd2Tag->inputTag = '<input type="password" name="password2" value="" />';
		array_unshift($formTags, $identifierTag, $passwdTag, $passwd2Tag);

		// insert agreement
		if($member_config->agreement)
		{
			unset($formTag);
			$formTag->title = Context::getLang('agreement');
			$formTag->inputTag = sprintf('<div class="agreement"><div class="text">%s</div><div class="confirm"><input type="checkbox" name="accept_agreement" value="Y" id="accept_agree" /><label for="accept_agree">%s</label></div></div>', $member_config->agreement, Context::getLang('about_accept_agreement'));
			$formTag->required = TRUE;

			array_unshift($formTags, $formTag);
		}

		return $formTags;
	}

	/**
	 * @brief get member modify form format
	 * @access public
	 * @param $memberSrl (when modify member_info of modified target member)
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getModifyFormInfo($memberSrl)
	{
		$memberInfo = $this->getMemberInfoByMemberSrl($memberSrl);
		$formTags = $this->getFormInfo($memberInfo);

		$memberInfo = get_object_vars($memberInfo);

		$member_config = $this->getConfig();

		// insert identifier
		$identifierTag->required = TRUE;
		$identifierTag->title = Context::getLang($member_config->identifier);
		$identifierTag->description = Context::getLang('about_' . $member_config->identifier);
		$identifierTag->inputTag = sprintf('<input type="hidden" name="%s" value="%s" /><input type="text" name="disable_field" value="%s" disabled="disabled" />', $member_config->identifier, $memberInfo[$member_config->identifier], $memberInfo[$member_config->identifier]);

		array_unshift($formTags, $identifierTag);

		return $formTags;
	}

	/**
	 * @brief get member modify form format
	 * @access public
	 * @param $memberSrl (when modify member_info of modified target member)
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getAdminFormInfo($memberSrl)
	{
		$memberInfo = $this->getMemberInfoByMemberSrl($memberSrl);
		$formTags = $this->getFormInfo($memberInfo);

		$memberInfo = get_object_vars($memberInfo);

		$member_config = $this->getConfig();

		// insert identifier
		$identifierTag->required = TRUE;
		$identifierTag->title = Context::getLang($member_config->identifier);
		$identifierTag->description = Context::getLang('about_' . $member_config->identifier);
		$identifierTag->inputTag = sprintf('<input type="text" name="%s" value="%s" />', $member_config->identifier, $memberInfo[$member_config->identifier]);

		// insert password field
		$passwdTag->required = TRUE;
		$passwdTag->title = Context::getLang('password');
		$passwdTag->description = Context::getLang('about_password');
		$passwdTag->inputTag = '<input type="password" name="password" value="" />';
		array_unshift($formTags, $identifierTag, $passwdTag);

		return $formTags;
	}
	/**
	 * @brief get member signup form format
	 * @access public
	 * @param $memberInfo (when modify member_info of modified target member)
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getFormInfo($memberInfo = NULL)
	{
		$oMemberModel = &getModel('member');
		$extend_form_list = $oMemberModel->getCombineJoinForm($memberInfo);

		if($memberInfo)
		{
			$memberInfo = get_object_vars($memberInfo);
		}

		$member_config = $this->getConfig();
		$formTags = array();

		foreach($member_config->signupForm as $no => $formInfo)
		{
			if(!$formInfo->isUse)
			{
				continue;
			}
			if($formInfo->name == $member_config->identifier || $formInfo->name == 'password')
			{
				continue;
			}
			unset($formTag);
			$inputTag = '';

			$formTag->name = $formInfo->name;
			$formTag->description = $formInfo->description;

			if($formInfo->isDefaultForm)
			{
				$formTag->title = Context::getLang($formInfo->name);
				$formTag->description = Context::getLang('about_' . $formInfo->name) ? Context::getLang('about_' . $formInfo->name) : $formTag->description;

				// if input form could image uploaded
				if($formInfo->imageType)
				{
					switch($formInfo->name)
					{
						case 'profile_image' :
							$target = $memberInfo['profile_image'];
							$functionName = 'doDeleteProfileImage';
							break;
						case 'image_name' :
							$target = $memberInfo['image_name'];
							$functionName = 'doDeleteImageName';
							break;
						case 'image_mark' :
							$target = $memberInfo['image_mark'];
							$functionName = 'doDeleteImageMark';
							break;
					}

					// if member info modify mode
					if($target->src)
					{
						$inputTag = sprintf('<p class="a"><input type="hidden" name="__%s_exist" value="true" /><span id="%s"><img src="%s" alt="%s" /> <button type="button" class="text" onclick="%s(%d);return false;">%s</button></span></p>'
											, $formInfo->name
											, $formInfo->name . 'tag'
											, $target->src
											, $formInfo->title
											, $functionName
											, $memberInfo['member_srl']
											, Context::getLang('cmd_delete'));
					}
					// if member info insert mode
					else
					{
						$inputTag = sprintf('<input type="hidden" name="__%s_exist" value="false" />', $formInfo->name);
					}
					$inputTag .= sprintf('<p class="a"><input type="file" name="%s" id="%s" value="" /> <span class="desc">%s : %dpx, %s : %dpx</span></p>'
										, $formInfo->name
										, $formInfo->name
										, Context::getLang($formInfo->name . '_max_width')
										, $member_config->{$formInfo->name . '_max_width'}
										, Context::getLang($formInfo->name . '_max_height')
										, $member_config->{$formInfo->name . '_max_height'});
				}//end imageType
				elseif($formInfo->name == 'birthday')
				{
					$inputTag = sprintf('<input type="hidden" name="birthday" id="date_birthday" value="%s" /><input type="text" class="inputDate" id="birthday" value="%s" /> <input type="button" value="%s" class="dateRemover" />'
							, $memberInfo['birthday']
							, zdate($memberInfo['birthday'], 'Y-m-d', FALSE)
							, Context::getLang('cmd_delete'));
				}// end birthday
				elseif($formInfo->name == 'find_account_question')
				{
					$inputTag = '<select name="find_account_question" style="width:290px">%s</select><br />';
					$optionTag = array();
					foreach(Context::getLang('find_account_question_items') as $key => $val)
					{
						if($key == $memberInfo['find_account_question'])
						{
							$selected = 'selected="selected"';
						}
						else
						{
							$selected = '';
						}
						$optionTag[] = sprintf('<option value="%s" %s >%s</option>'
												, $key
												, $selected
												, $val);
					}
					$inputTag = sprintf($inputTag, implode('', $optionTag));
					$inputTag .= '<input type="text" name="find_account_answer" value="' . $memberInfo['find_account_answer'] . '" />';
				}// end find_account_question
				else if($formInfo->name == 'signature')
				{
					// Editor of the module set for signing by calling getEditor
					if($memberInfo['member_srl']) {
						$oEditorModel = &getModel('editor');
						$option->primary_key_name = 'member_srl';
						$option->content_key_name = 'signature';
						$option->allow_fileupload = false;
						$option->enable_autosave = false;
						$option->enable_default_component = true;
						$option->enable_component = false;
						$option->resizable = false;
						$option->disable_html = true;
						$option->height = 200;
						$option->skin = $this->member_config->editor_skin;
						$option->colorset = $this->member_config->editor_colorset;
						$editor = $oEditorModel->getEditor($memberInfo['member_srl'], $option);

						$inputTag = sprintf('<input type="hidden" name="signature" value="%s" />', htmlspecialchars($this->getSignature($memberInfo['member_srl']))).$editor;
					}
				}// end signature
				else
				{
					$inputTag = sprintf('<input type="text" name="%s" value="%s" />'
								, $formInfo->name
								, $memberInfo[$formInfo->name]);
				}
			}//end isDefaultForm
			else
			{
				$formTag->title = $formInfo->title;
				$extendForm = $extend_form_list[$formInfo->member_join_form_srl];
				$inputTag = $oMemberModel->getExtendsInputForm($extendForm);
			}

			if($formInfo->required || $formInfo->mustRequired && $formInfo->name != 'password')
			{
				$formTag->required = TRUE;
			}
			$formTag->inputTag = $inputTag;
			$formTags[] = $formTag;
		}

		unset($formTag);
		$formTag->title = Context::getLang('allow_mailing');
		$formTag->description = Context::getLang('about_allow_mailing');
		if($memberInfo['allow_mailing'] != 'Y')
		{
			$inputTag = '<input type="radio" name="allow_mailing" id="mailingYes" value="Y" /> <label for="mailingYes">%s</label><input type="radio" name="allow_mailing" id="mailingNo" value="N" checked="checked" /> <label for="mailingNo">%s</label>';
		}
		else
		{
			$inputTag = '<input type="radio" name="allow_mailing" id="mailingYes" value="Y" checked="checked"/> <label for="mailingYes">%s</label><input type="radio" name="allow_mailing" id="mailingNo" value="N" /> <label for="mailingNo">%s</label>';
		}
		$formTag->inputTag = sprintf($inputTag, Context::getLang('cmd_yes'), Context::getLang('cmd_no'));

		$formTags[] = $formTag;

		return $formTags;
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
		Context::set('driverConfig', $this->getConfig());
		return parent::getInfoFooterTpl($memberSrl);
	}

	/**
	 * @brief get config tpl
	 * @access public
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getConfigTpl()
	{
		// get driver config
		$config = $this->getConfig();

		if(is_array($config->signupForm))
		{
			foreach($config->signupForm AS $key => $value)
			{
				$config->signupForm[$key]->title = Context::getLang($value->title);
			}
		}

		// get denied ID list
		$denied_list = $this->getDeniedIDs();
		Context::set('deniedIDs', $denied_list);

		Context::set('config', $config);

		// retrieve skins of editor
		$oEditorModel = getModel('editor');
		// get an editor
		$option->primary_key_name = 'temp_srl';
		$option->content_key_name = 'agreement';
		$option->allow_fileupload = FALSE;
		$option->enable_autosave = FALSE;
		$option->enable_default_component = TRUE;
		$option->enable_component = TRUE;
		$option->resizable = TRUE;
		$option->height = 300;
		$editor = $oEditorModel->getEditor(0, $option);
		Context::set('editor', $editor);

		$oSecurity = new Security();
		$oSecurity->encodeHTML('config.agreement');

		return parent::getConfigTpl();
	}

	/**
	 * @brief get denied id list
	 * @access public
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getDeniedIDs()
	{
		$output = executeQueryArray('member.driver.default.getDeniedIDs');
		if(!$output->toBool())
		{
			return array();
		}
		return $output->data;
	}

	/**
	 * @brief get trigger obj
	 * @access public
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getTriggerObj()
	{
		$triggerObj = new stdClass();
		$triggerObj->user_id = $this->
		$output = executeQueryArray('member.driver.default.getDeniedIDs');
		if(!$output->toBool())
		{
			return array();
		}
		return $output->data;
	}

	/**
	 * @brief Get a member list
	 **/
	function getMemberList() {
		// Search option
		$args->is_admin = Context::get('is_admin') == 'Y' ? 'Y' : '';
		$args->is_denied = Context::get('is_denied') == 'Y' ? 'Y' : '';
		$args->selected_group_srl = Context::get('selected_group_srl');

		$oMemberAdminModel = getAdminModel('member');
		$filter = Context::get('filter_type');
		switch($filter)
		{
			case 'super_admin':
				$args->is_admin = 'Y';
				break;
			case 'site_admin':
				//TODO check getStieAdminMemberSrls
				$args->member_srls = $oMemberAdminModel->getSiteAdminMemberSrls();
				break;
			case 'enable':
				$args->is_denied = 'N';
				break;
			case 'disable':
				$args->is_denied = 'Y';
				break;
		}

		$search_target = trim(Context::get('search_target'));
		$search_keyword = trim(Context::get('search_keyword'));

		if($search_target && $search_keyword)
		{
			switch($search_target)
			{
				case 'user_id':
					if($search_keyword)
					{
						$search_keyword = str_replace(' ', '%', $search_keyword);
					}
					$args->s_user_id = $search_keyword;
					break;
				case 'user_name':
					if($search_keyword)
					{
						$search_keyword = str_replace(' ', '%', $search_keyword);
					}
					$args->s_user_name = $search_keyword;
					break;
				case 'nick_name':
					if($search_keyword)
					{
						$search_keyword = str_replace(' ', '%', $search_keyword);
					}
					$args->s_nick_name = $search_keyword;
					$args->html_nick_name = htmlspecialchars($search_keyword);
					break;
				case 'email_address':
					if($search_keyword)
					{
						$search_keyword = str_replace(' ', '%', $search_keyword);
					}
					$args->s_email_address = $search_keyword;
					break;
				case 'regdate':
					$args->s_regdate = preg_replace("/[^0-9]/", "", $search_keyword);
					break;
				case 'regdate_more':
					$args->s_regdate_more = substr(preg_replace("/[^0-9]/", "", $search_keyword) . '00000000000000', 0, 14);
					break;
				case 'regdate_less':
					$args->s_regdate_less = substr(preg_replace("/[^0-9]/", "", $search_keyword) . '00000000000000', 0, 14);
					break;
				case 'last_login':
					$args->s_last_login = $search_keyword;
					break;
				case 'last_login_more':
					$args->s_last_login_more = substr(preg_replace("/[^0-9]/", "", $search_keyword) . '00000000000000', 0, 14);
					break;
				case 'last_login_less':
					$args->s_last_login_less = substr(preg_replace("/[^0-9]/", "", $search_keyword) . '00000000000000', 0, 14);
					break;
				case 'extra_vars':
					$args->s_extra_vars = $search_keyword;
					break;
			}
		}

		// Change the query id if selected_group_srl exists (for table join)
		$sort_order = Context::get('sort_order');
		$sort_index = Context::get('sort_index');

		if($sort_index != 'last_login')
		{
			$sort_index = "list_order";
		}
		else
		{
			$sort_order = 'desc';
		}

		if($args->selected_group_srl)
		{
			$query_id = 'member.driver.default.getMemberListWithinGroup';
			$args->sort_index = 'member.' . $sort_index;
		}
		else
		{
			$query_id = 'member.driver.default.getMemberList';
			$args->sort_index = $sort_index;
		}

		if($sort_order != 'desc')
		{
			$sort_order = "asc";
		}

		$args->sort_order = $sort_order;
		Context::set('sort_order', $sort_order);

		// Other variables
		$args->page = Context::get('page');
		$args->list_count = 40;
		$args->page_count = 10;
		$output = executeQuery($query_id, $args);

		return $output;
	}

	/**
	 * @brief Register denied ID
	 * @access public
	 * @param $userId
	 * @param $description
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function insertDeniedID($userId, $description = '')
	{
		$args->user_id = $userId;
		$args->description = $description;
		$args->list_order = -1 * getNextSequence();

		return executeQuery('member.driver.default.insertDeniedID', $args);
	}

	/**
	 * @brief Delete a denied ID
	 * @access public
	 * @param $userId
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function deleteDeniedID($userId)
	{
		$args->user_id = $userId;
		return executeQuery('member.driver.default.deleteDeniedID', $args);
	}

	/**
	 * @brief display page of modify email address
	 * @access public
	 * @param $oModule member view
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function dispModifyEmailAddress($oModule)
	{
		if(!Context::get('is_logged'))
		{
			return new Object(-1, 'msg_not_logged');
		}

		$innerTpl = $this->getTpl('modify_email_address');
		Context::set('innerTpl', $innerTpl);
		$oModule->setTemplateFile('member_info_inner');
		return new Object();
	}

	public function dispUnregister($oModule)
	{
		if(!Context::get('is_logged'))
		{
			return new Object(-1, 'msg_not_logged');
		}

		$member_config = $this->getConfig();
		$logged_info = Context::get('logged_info');

		Context::set('member_config', $member_config);
		Context::set('identifierValue', $logged_info->{$member_config->identifier});
		$innerTpl = $this->getTpl('unregister');
		Context::set('innerTpl', $innerTpl);
		$oModule->setTemplateFile('member_info_inner');
		return new Object();
	}

	/**
	 * @brief Add a denied ID
	 * @access public
	 * @param $oModule member admin controller
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function procInsertDeniedId($oModule)
	{
		$output = $this->checkRuleset('insertDeniedId');
		if(!$output->toBool())
		{
			return $output;
		}

		$userIds = Context::get('user_id');

		$userIds = explode(',', $userIds);
		$successIds = array();

		foreach($userIds as $val)
		{
			$output = $this->insertDeniedID($val, '');
			if($output->toBool())
			{
				$successIds[] = $val;
			}
		}

		$oModule->add('user_ids', implode(',', $successIds));

		return new Object();
	}

	/**
	 * @brief Change email address
	 * @access public
	 * @param $oModule member controller
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function procChangeEmailAddress($oModule)
	{
		$output = $this->checkRuleset('modifyEmailAddress');
		if(!$output->toBool())
		{
			return $output;
		}

		if(!Context::get('is_logged'))
		{
			return new Object(-1, 'msg_not_logged');
		}

		$member_info = Context::get('logged_info');
		$newEmail = Context::get('email_address');

		if(!$newEmail)
		{
			return new Object(-1, 'msg_invalid_request');
		}

		$auth_args->user_id = $newEmail;
		$auth_args->member_srl = $member_info->member_srl;
		$auth_args->auth_key = md5(rand(0, 999999));
		$auth_args->new_password = 'XE_change_emaill_address';

		$output = executeQuery('member.driver.default.insertAuthMail', $auth_args);
		if (!$output->toBool())
		{
			return $output;
		}

		$oMemberModel = getModel('member');
		$commonConfig = $oMemberModel->getMemberConfig();
		$config = $this->getConfig('member');

		$tpl_path = $this->getDriverTplPath();

		global $lang;

		$memberInfo[$lang->email_address] = $member_info->email_address;
		$memberInfo[$lang->nick_name] = $member_info->nick_name;

		Context::set('memberInfo', $memberInfo);

		Context::set('newEmail', $newEmail);

		$auth_url = getFullUrl('', 'module', 'member', 'act', 'procMemberDriverInterface', 'driver', 'default', 'dact', 'procAuthEmailAddress', 'member_srl', $member_info->member_srl, 'auth_key', $auth_args->auth_key);
		Context::set('auth_url', $auth_url);

		$oTemplate = &TemplateHandler::getInstance();
		$content = $oTemplate->compile($tpl_path, 'confirm_member_new_email');

		$oMail = new Mail();
		$oMail->setTitle( Context::getLang('title_modify_email_address') );
		$oMail->setContent($content);
		$oMail->setSender( $commonConfig->webmaster_name?$commonConfig->webmaster_name:'webmaster', $commonConfig->webmaster_email);
		$oMail->setReceiptor( $member_info->nick_name, $newEmail);
		$result = $oMail->send();

		$msg = sprintf(Context::getLang('msg_confirm_mail_sent'), $newEmail);

		if(!in_array(Context::getRequestMethod(),array('XMLRPC','JSON')))
		{
			$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'mid', Context::get('mid'), 'act', '');
			$oModule->setRedirectUrl($returnUrl);
		}

		return new Object(0, $msg);
	}

	/**
	 * @brief Unregister member
	 * @access public
	 * @param $oModule member controller
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function procUnregister($oModule)
	{
		if(!Context::get('is_logged'))
		{
			return new Object(-1, 'msg_not_logged');
		}
		// Extract the necessary information in advance
		$password = trim(Context::get('password'));
		// Get information of logged-in user
		$logged_info = Context::get('logged_info');
		$member_srl = $logged_info->member_srl;
		// Create a member model object
		$oMemberModel = &getModel('member');
		// Get information of member_srl
		if(!$this->memberInfo->password)
		{
			$columnList = array('member.member_srl', 'member.password');
			$memberInfo = $this->getMemberInfoByMemberSrl($member_srl, 0, $columnList);
			$this->memberInfo->password = $memberInfo->password;
		}
		// Verify the cuttent password
		if(!$oMemberModel->isValidPassword($this->memberInfo->password, $password))
		{
			return new Object(-1, 'invalid_password');
		}

		$output = $this->deleteMember($member_srl);
		if(!$output->toBool()) return $output;
		// Destroy all session information
		$this->destroySessionInfo();
		// Return success message
		$this->setMessage('success_leaved');
		if(!in_array(Context::getRequestMethod(),array('XMLRPC','JSON'))) {
			$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'mid', Context::get('mid'), 'act', '');
			header('location:'.$returnUrl);
			return;
		}
	}

	/**
	 * @brief Auth email address
	 * @access public
	 * @param $oModule member controller
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function procAuthEmailAddress($oModule)
	{
		$member_srl = Context::get('member_srl');
		$auth_key = Context::get('auth_key');
		if(!$member_srl || !$auth_key)
		{
			return new Object(-1, 'msg_invalid_request');
		}

		// Test logs for finding password by user_id and authkey
		$args->member_srl = $member_srl;
		$args->auth_key = $auth_key;
		$output = executeQuery('member.driver.default.getAuthMail', $args);
		if(!$output->toBool() || $output->data->auth_key != $auth_key)
		{
			return new Object(-1, 'msg_invalid_modify_email_auth_key');
		}

		$newEmail = $output->data->user_id;
		$args->email_address = $newEmail;
		list($args->email_id, $args->email_host) = explode('@', $newEmail);

		$output = executeQuery('member.driver.default.updateMemberEmailAddress', $args);
		if(!$output->toBool())
		{
			return $output;
		}

		// Remove all values having the member_srl and new_password equal to 'XE_change_emaill_address' from authentication table
		executeQuery('member.driver.default.deleteAuthChangeEmailAddress',$args);

		// Notify the result
		$oModule->setTemplatePath($this->module_path.'tpl');
		$oModule->setTemplateFile('msg_success_modify_email_address');

		return new Object();
	}

	/**
	 * @brief Update denied ID
	 * @access public
	 * @param $oModule member admin contoller
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function procUpdateDeniedId($oModule)
	{
		$userId = Context::get('user_id');
		$mode = Context::get('mode');

		switch($mode)
		{
			case 'delete' :
				$output = $this->deleteDeniedID($userId);
				if(!$output->toBool())
				{
					return $output;
				}
				$msgCode = 'success_deleted';
				break;
		}

		$oModule->add('page', Context::get('page'));
		$oModule->setMessage($msg_code);
	}

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
		// memberConfig update
		$signupItem->name = $args->column_name;
		$signupItem->title = $args->column_title;
		$signupItem->type = $args->column_type;
		$signupItem->member_join_form_srl = $args->member_join_form_srl;
		$signupItem->required = ($args->required == 'Y');
		$signupItem->isUse = ($args->is_active == 'Y');
		$signupItem->description = $args->description;

		$config = $this->getConfig();

		if($isInsert)
		{
			$config->signupForm[] = $signupItem;
		}
		else
		{
			foreach($config->signupForm as $key => $val)
			{
				if($val->member_join_form_srl == $signupItem->member_join_form_srl)
				{
					$config->signupForm[$key] = $signupItem;
				}
			}
		}
		$oModuleController = getController('module');
		$output = $oModuleController->insertDriverConfig('member', 'default', $config);

		return $output;
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
		$config = $this->getConfig();

		foreach($config->signupForm as $key => $val)
		{
			if($val->member_join_form_srl == $args->member_join_form_srl)
			{
				unset($config->signupForm[$key]);
				break;
			}
		}
		$oModuleController = getController('module');
		$output = $oModuleController->insertDriverConfig('member', 'default', $config);

		return $output;
	}

	/**
	 * @brief save signup form
	 * @access public
	 * @param $oModule (module.admin.controller)
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function procSaveConfig($oModule)
	{
		$oModuleController = getController('module');

		$config = $this->getConfig();

		$args = Context::gets(
				'enable_confirm',
				'change_password_date',
				'agreement'
				);

		$config->enable_auth_mail = ($args->enable_auth_mail != 'Y') ? 'N' : 'Y';
		$config->change_password_date = (!$args->change_password_date) ? 0 : $args->change_password_date;
		$config->agreement = (!trim(strip_tags($args->agreement))) ? NULL : $args->agreement;

		if(!$config->signupForm)
		{
			$config->signupForm = $this->createSignupForm();
		}
		$this->createSignupRuleset();

		$output = $oModuleController->insertDriverConfig('member', 'default', $config);
		// default setting end

		if($output->toBool() && !in_array(Context::getRequestMethod(), array('XMLRPC', 'JSON')))
		{
			$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act', 'dispMemberAdminDriverConfig', 'driver', 'default');
			$oModule->setRedirectUrl($returnUrl);
			return;
		}

	}

	/**
	 * @brief save signup form
	 * @access public
	 * @param $oModule (module.admin.controller)
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function procSaveSignUpForm($oModule)
	{
		$input_args = Context::gets(
				'profile_image', 'profile_image_max_width', 'profile_image_max_height',
				'image_name', 'image_name_max_width', 'image_name_max_height',
				'image_mark', 'image_mark_max_width', 'image_mark_max_height'
				);

		$list_order = Context::get('list_order');
		$usable_list = Context::get('usable_list');
		$all_args = Context::getRequestVars();

		$oModuleController = getController('module');
		$oMemberModel = getModel('member');

		$config = $this->getConfig();

		foreach($input_args as $key => $value)
		{
			$config->{$key} = $value;
		}

		$config->profile_image = $input_args->profile_image ? 'Y' : 'N';
		$config->image_name = $input_args->image_name ? 'Y' : 'N';
		$config->image_mark = $input_args->image_mark ? 'Y' : 'N';
		$config->signature = $input_args->signature != 'Y' ? 'N' : 'Y';
		$config->identifier = $all_args->identifier;

		// signupForm
		global $lang;
		$signupForm = array();
		$items = array('user_id', 'password', 'user_name', 'nick_name', 'email_address', 'find_account_question', 'homepage', 'blog', 'birthday', 'signature', 'profile_image', 'image_name', 'image_mark', 'profile_image_max_width', 'profile_image_max_height', 'image_name_max_width', 'image_name_max_height', 'image_mark_max_width', 'image_mark_max_height');
		$mustRequireds = array('email_address', 'nick_name', 'password', 'find_account_question');
		$extendItems = $oMemberModel->getJoinFormList();
		foreach($list_order as $key)
		{
			unset($signupItem);
			$signupItem->isIdentifier = ($key == $all_args->identifier);
			$signupItem->isDefaultForm = in_array($key, $items);

			$signupItem->name = $key;
			if(in_array($key, $items))
			{
				$signupItem->title = $key;
			}
			else
			{
				$signupItem->title = $lang->{$key};
			}
			$signupItem->mustRequired = in_array($key, $mustRequireds);
			$signupItem->imageType = (strpos($key, 'image') !== FALSE);
			$signupItem->required = ($all_args->{$key} == 'required') || $signupItem->mustRequired || $signupItem->isIdentifier;
			$signupItem->isUse = in_array($key, $usable_list) || $signupItem->required;

			if($signupItem->imageType)
			{
				$signupItem->max_width = $all_args->{$key . '_max_width'};
				$signupItem->max_height = $all_args->{$key . '_max_height'};
			}

			// set extends form
			if(!$signupItem->isDefaultForm)
			{
				$extendItem = $extendItems[$all_args->{$key . '_member_join_form_srl'}];
				$signupItem->type = $extendItem->column_type;
				$signupItem->member_join_form_srl = $extendItem->member_join_form_srl;
				$signupItem->title = $extendItem->column_title;
				$signupItem->description = $extendItem->description;

				// check usable value change, required/option
				if($signupItem->isUse != ($extendItem->is_active == 'Y') || $signupItem->required != ($extendItem->required == 'Y'))
				{
					unset($update_args);
					$update_args->member_join_form_srl = $extendItem->member_join_form_srl;
					$update_args->is_active = $signupItem->isUse ? 'Y' : 'N';
					$update_args->required = $signupItem->required ? 'Y' : 'N';

					$update_output = executeQuery('member.updateJoinForm', $update_args);
				}
				unset($extendItem);
			}
			$signupForm[] = $signupItem;
		}
		$config->signupForm = $signupForm;

		// create Ruleset
		$this->createSignupRuleset();
		$this->createSigninRuleset();
		$this->createFindAccountByQuestion();

		$output = $oModuleController->insertDriverConfig('member', 'default', $config);
		// default setting end

		if($output->toBool() && !in_array(Context::getRequestMethod(), array('XMLRPC', 'JSON')))
		{
			$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act', 'dispMemberAdminDriverConfig', 'driver', 'default');
			$oModule->setRedirectUrl($returnUrl);
			return;
		}
	}


	/**
	 * @brief create signin ruleset
	 * @access private
	 * @param $identifier
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	protected function createSigninRuleset()
	{
		$config = $this->getConfig();
		$identifier = $config->identifier;

		$fields = array();
		$trans = array('email_address' => 'email', 'user_id' => 'userid');
		$fields[] = sprintf('<field name="user_id" required="true" rule="%s"/>', $trans[$identifier]);
		$fields[] = '<field name="password" required="true" />';

		$this->createRuleset($this->getSigninRulset(TRUE), $fields);
	}


	/**
	 * @brief create findAccount ruleset
	 * @access private
	 * @param $identifier
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	private function createFindAccountByQuestion()
	{
		$config = $this->getConfig();
		$identifier = $config->identifier;

		$fields = array();
		if($identifier == 'user_id')
		{
			$fields[] = '<field name="user_id" required="true" rule="userid" />';
		}

		$fields[] = '<field name="email_address" required="true" rule="email" />';
		$fields[] = '<field name="find_account_question" required="true" />';
		$fields[] = '<field name="find_account_answer" required="true" length=":250"/>';

		$this->createRuleset('find_member_account_by_question', $fields);
	}

	/**
	 * @brief get Config
	 * @access private
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	private function getConfig()
	{
		$oModuleModel = getModel('module');
		$config = $oModuleModel->getDriverConfig('member', 'default');

		// check identifier
		if(!$config->identifier)
		{
			$config->identifier = 'email_address';
		}

		// check signupform
		if(!$config->signupForm)
		{
			$config->signupForm = $this->createSignupForm();
		}

		// make default value
		$config->enable_auth_mail = ($config->enable_auth_mail != 'Y') ? 'N' : 'Y';
		$config->change_password_date = (!$config->change_password_date) ? 0 : $config->change_password_date;
		$config->agreement = (!trim(strip_tags($config->agreement))) ? NULL : $config->agreement;

		return $config;
	}


	/**
	 * @brief Return member information with member_srl
	 * @access public
	 * @param $member_srl
	 * @param $columnList (default array())
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getMemberInfoByMemberSrl($member_srl, $columnList = array())
	{
		if(!$member_srl)
		{
			return;
		}

		$args->member_srl = $member_srl;
		$output = executeQuery('member.driver.default.getMemberInfoByMemberSrl', $args, $columnList);
		if(!$output->toBool())
		{
			return $output;
		}
		if(!$output->data)
		{
			return;
		}

		$member_info = $this->arrangeMemberInfo($output->data);

		return $member_info;
	}

	/**
	 * @brief Return member information with user_id
	 * @access public
	 * @param $user_id
	 * @param $columnList (default array())
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getMemberInfoByUserID($user_id, $columnList = array())
	{
		if(!$user_id)
		{
			return;
		}

		$args->user_id = $user_id;
		$output = executeQuery('member.driver.default.getMemberInfo', $args, $columnList);
		if(!$output->toBool())
		{
			return $output;
		}
		if(!$output->data)
		{
			return;
		}

		$member_info = $this->arrangeMemberInfo($output->data);

		return $member_info;
	}

	/**
	 * @brief Return member information with email_address
	 * @access public
	 * @param $email_address
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getMemberInfoByEmailAddress($email_address)
	{
		if(!$email_address)
		{
			return;
		}

		$args->email_address = $email_address;
		$output = executeQuery('member.getMemberInfoByEmailAddress', $args);
		if(!$output->toBool())
		{
			return;
		}
		if(!$output->data)
		{
			return;
		}

		$member_info = $this->arrangeMemberInfo($output->data);
		return $member_info;
	}

	/**
	 * @brief Get information of the profile image
	 * @access public
	 * @param $memberSrl
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getProfileImage($memberSrl)
	{
		if(!isset($GLOBALS['__member_info__']['profile_image'][$memberSrl]))
		{
			$GLOBALS['__member_info__']['profile_image'][$memberSrl] = NULL;
			$exts = array('gif', 'jpg', 'png');
			foreach($exts as $ext)
			{
				$image_name_file = sprintf('files/member_extra_info/profile_image/%s%d.%s', getNumberingPath($memberSrl), $memberSrl, $ext);
				if(file_exists($image_name_file))
				{
					list($width, $height, $type, $attrs) = getimagesize($image_name_file);
					$info = NULL;
					$info->width = $width;
					$info->height = $height;
					$info->src = Context::getRequestUri() . $image_name_file;
					$info->file = './' . $image_name_file;
					$GLOBALS['__member_info__']['profile_image'][$memberSrl] = $info;
					break;
				}
			}
		}

		return $GLOBALS['__member_info__']['profile_image'][$memberSrl];
	}

	/**
	 * @brief Get the image name
	 * @access public
	 * @param $memberSrl
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getImageName($memberSrl)
	{
		if(!isset($GLOBALS['__member_info__']['image_name'][$memberSrl]))
		{
			$image_name_file = sprintf('files/member_extra_info/image_name/%s%d.gif', getNumberingPath($memberSrl), $memberSrl);
			if(file_exists($image_name_file))
			{
				list($width, $height, $type, $attrs) = getimagesize($image_name_file);
				$info->width = $width;
				$info->height = $height;
				$info->src = Context::getRequestUri() . $image_name_file;
				$info->file = './' . $image_name_file;
				$GLOBALS['__member_info__']['image_name'][$memberSrl] = $info;
			}
			else
			{
				$GLOBALS['__member_info__']['image_name'][$memberSrl] = NULL;
			}
		}
		return $GLOBALS['__member_info__']['image_name'][$memberSrl];
	}

	/**
	 * @brief Get the image mark
	 * @access public
	 * @param $memberSrl
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getImageMark($memberSrl)
	{
		if(!isset($GLOBALS['__member_info__']['image_mark'][$memberSrl]))
		{
			$image_mark_file = sprintf('files/member_extra_info/image_mark/%s%d.gif', getNumberingPath($memberSrl), $memberSrl);
			if(file_exists($image_mark_file))
			{
				list($width, $height, $type, $attrs) = getimagesize($image_mark_file);
				$info->width = $width;
				$info->height = $height;
				$info->src = Context::getRequestUri() . $image_mark_file;
				$info->file = './' . $image_mark_file;
				$GLOBALS['__member_info__']['image_mark'][$memberSrl] = $info;
			}
			else
			{
				$GLOBALS['__member_info__']['image_mark'][$memberSrl] = NULL;
			}
		}

		return $GLOBALS['__member_info__']['image_mark'][$memberSrl];
	}

	/**
	 * @brief Get member_srl corresponding to userid
	 * @access public
	 * @param $user_id
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getMemberSrlByUserID($user_id)
	{
		$args->user_id = $user_id;
		$output = executeQuery('member.driver.default.getMemberSrl', $args);
		return $output->data->member_srl;
	}

	/**
	 * @brief Get member_srl corresponding to EmailAddress
	 * @access public
	 * @param $email_address
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getMemberSrlByEmailAddress($email_address)
	{
		$args->email_address = $email_address;
		$output = executeQuery('member.driver.default.getMemberSrl', $args);
		return $output->data->member_srl;
	}

	/**
	 * @brief Get member_srl corresponding to nickname
	 * @access public
	 * @param $nick_name
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getMemberSrlByNickName($nick_name)
	{
		$args->nick_name = $nick_name;
		$output = executeQuery('member.driver.default.getMemberSrl', $args);
		return $output->data->member_srl;
	}


	/**
	 * @brief Create Signup ruleset
	 * @access protected
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	protected function createSignupRuleset()
	{
		$config = $this->getConfig();
		$signupForm = $config->signupForm;
		$agreement = $config->agreement;

		$fields = array();

		if($agreement)
		{
			$fields[] = '<field name="accept_agreement"><if test="$act == \'procMemberInsert\'" attr="required" value="true" /></field>';
		}

		foreach($signupForm as $formInfo)
		{
			if($formInfo->required || $formInfo->mustRequired)
			{
				if($formInfo->type == 'tel' || $formInfo->type == 'kr_zip')
				{
					$fields[] = sprintf('<field name="%s[]" required="true" />', $formInfo->name);
				}
				elseif($formInfo->name == 'password')
				{
					$fields[] = '<field name="password"><if test="$act == \'procMemberInsert\'" attr="required" value="true" /><if test="$act == \'procMemberInsert\'" attr="length" value="3:20" /></field>';
					$fields[] = '<field name="password2"><if test="$act == \'procMemberInsert\'" attr="required" value="true" /><if test="$act == \'procMemberInsert\'" attr="equalto" value="password" /></field>';
				}
				elseif($formInfo->name == 'find_account_question')
				{
					$fields[] = '<field name="find_account_question"><if test="$act != \'procMemberAdminInsert\'" attr="required" value="true" /></field>';
					$fields[] = '<field name="find_account_answer"><if test="$act != \'procMemberAdminInsert\'" attr="required" value="true" /><if test="$act != \'procMemberAdminInsert\'" attr="length" value=":250" /></field>';
				}
				elseif($formInfo->name == 'email_address')
				{
					$fields[] = sprintf('<field name="%s" required="true" rule="email"/>', $formInfo->name);
				}
				elseif($formInfo->name == 'user_id')
				{
					$fields[] = sprintf('<field name="%s" required="true" rule="userid" length="3:20" />', $formInfo->name);
				}
				elseif(strpos($formInfo->name, 'image') !== FALSE)
				{
					$fields[] = sprintf('<field name="%s"><if test="$act != \'procMemberAdminInsert\' &amp;&amp; $__%s_exist != \'true\'" attr="required" value="true" /></field>', $formInfo->name, $formInfo->name);
				}
				else
				{
					$fields[] = sprintf('<field name="%s" required="true" />', $formInfo->name);
				}
			}
		}

		$this->createRuleset($this->getSignupRuleset(TRUE), $fields);
	}

	/**
	 * @brief Create Admin insert ruleset
	 * @access protected
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	protected function createAdminInsertRuleset()
	{
		$config = $this->getConfig();

		$fields = array();

		// check user_id
		if($config->indentifier == 'user_id')
		{
			$fields[] = '<field name="user_id" required="true" rule="user_id" />';
		}

		// make fields node
		$fields[] = '<field name="email_address" required="true" rule="email" />';
		$fields[] = '<field name="password">' .
						'<if test="!$member_srl" attr="required" value="true" />' .
						'<if test="!$member_srl" attr="length" value="3:20" />' .
					'</field>';
		$fields[] = '<field name="nick_name" required="true" />';

		// make ruleset(using super class's function)
		$this->createRuleset($this->getAdminInsertRuleset(TRUE), $fields);
	}

	/**
	 * @brief Check values when member joining
	 * @access public
	 * @param $name
	 * @param $value
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function isValidateValue($name, $value, $memberSrl = 0)
	{
		// Check if logged-in
		if(!$memberSrl)
		{
			$logged_info = Context::get('logged_info');
			$memberSrl = $logged_info->member_srl;
		}

		switch($name)
		{
			case 'user_id' :
				// Check denied ID
				if($this->isDeniedID($value))
				{
					return new Object(0,'denied_user_id');
				}

				// Check if duplicated
				$targetMemberSrl = $this->getMemberSrlByUserID($value);
				if($targetMemberSrl && $memberSrl != $targetMemberSrl )
				{
					return new Object(0,'msg_exists_user_id');
				}
				break;
			case 'nick_name' :
				// Check if duplicated
				$targetMemberSrl = $this->getMemberSrlByNickName($value);
				if($targetMemberSrl && $memberSrl != $targetMemberSrl )
				{
					return new Object(0,'msg_exists_nick_name');
				}
				break;
			case 'email_address' :
				// Check if duplicated
				$targetMemberSrl = $this->getMemberSrlByEmailAddress($value);
				if($targetMemberSrl && $memberSrl != $targetMemberSrl )
				{
					return new Object(0,'msg_exists_email_address');
				}
				break;
		}
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
		$requiredList = array('email_address', 'nick_name');

		$config = $this->getConfig();
		if($config->identifier == 'user_id')
		{
			$requiredList[] = 'user_id';
		}

		$isUpdate = isset($args->member_srl);
		if(!$isUpdate)
		{
			$requiredList[] = 'password';
		}

		foreach($requiredList as $requiredTag)
		{
			if(empty($args->{$requiredTag}))
			{
				return new Object(-1, 'msg_invalid_request');
			}
		}
		return new Object();
	}
	public function insertProfileImage($member_srl, $target_file)
	{
		$oModuleModel = &getModel('module');
		$config = $oModuleModel->getModuleConfig('member');
		// Get an image size
		$max_width = $config->profile_image_max_width;
		if(!$max_width) $max_width = "90";
		$max_height = $config->profile_image_max_height;
		if(!$max_height) $max_height = "20";
		// Get a target path to save
		$target_path = sprintf('files/member_extra_info/profile_image/%s', getNumberingPath($member_srl));
		FileHandler::makeDir($target_path);
		// Get file information
		list($width, $height, $type, $attrs) = @getimagesize($target_file);
		if($type == 3) $ext = 'png';
		elseif($type == 2) $ext = 'jpg';
		else $ext = 'gif';

		$target_filename = sprintf('%s%d.%s', $target_path, $member_srl, $ext);
		// Convert if the image size is larger than a given size or if the format is not a gif
		if($width > $max_width || $height > $max_height || $type!=1) FileHandler::createImageFile($target_file, $target_filename, $max_width, $max_height, $ext);
		else @copy($target_file, $target_filename);
	}

	public function insertImageName($member_srl, $target_file)
	{
		$oModuleModel = &getModel('module');
		$config = $oModuleModel->getModuleConfig('member');
		// Get an image size
		$max_width = $config->image_name_max_width;
		if(!$max_width) $max_width = "90";
		$max_height = $config->image_name_max_height;
		if(!$max_height) $max_height = "20";
		// Get a target path to save
		$target_path = sprintf('files/member_extra_info/image_name/%s/', getNumberingPath($member_srl));
		FileHandler::makeDir($target_path);

		$target_filename = sprintf('%s%d.gif', $target_path, $member_srl);
		// Get file information
		list($width, $height, $type, $attrs) = @getimagesize($target_file);
		// Convert if the image size is larger than a given size or if the format is not a gif
		if($width > $max_width || $height > $max_height || $type!=1) FileHandler::createImageFile($target_file, $target_filename, $max_width, $max_height, 'gif');
		else @copy($target_file, $target_filename);
	}

	public function insertImageMark($member_srl, $target_file)
	{
		$oModuleModel = &getModel('module');
		$config = $oModuleModel->getModuleConfig('member');
		// Get an image size
		$max_width = $config->image_mark_max_width;
		if(!$max_width) $max_width = "20";
		$max_height = $config->image_mark_max_height;
		if(!$max_height) $max_height = "20";

		$target_path = sprintf('files/member_extra_info/image_mark/%s/', getNumberingPath($member_srl));
		FileHandler::makeDir($target_path);

		$target_filename = sprintf('%s%d.gif', $target_path, $member_srl);
		// Get file information
		list($width, $height, $type, $attrs) = @getimagesize($target_file);

		if($width > $max_width || $height > $max_height || $type!=1) FileHandler::createImageFile($target_file, $target_filename, $max_width, $max_height, 'gif');
		else @copy($target_file, $target_filename);

	}
	/**
	 * @brief Save the signature as a file
	 * @access public
	 * @param $member_srl
	 * @param $signature
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function putSignature($member_srl, $signature)
	{
		$signature = trim(removeHackTag($signature));
		$signature = preg_replace('/<(\/?)(embed|object|param)/is', '&lt;$1$2', $signature);

		$check_signature = trim(str_replace(array('&nbsp;',"\n","\r"),'',strip_tags($signature,'<img><object>')));
		$path = sprintf('files/member_extra_info/signature/%s/', getNumberingPath($member_srl));
		$filename = sprintf('%s%d.signature.php', $path, $member_srl);

		if(!$check_signature) return FileHandler::removeFile($filename);

		$buff = sprintf('<?php if(!defined("__XE__")) exit();?>%s', $signature);
		FileHandler::makeDir($path);
		FileHandler::writeFile($filename, $buff);
	}

	/**
	 * @brief Get user's signature
	 * @access public
	 * @param $member_srl
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getSignature($member_srl)
	{
		if(!isset($GLOBALS['__member_info__']['signature'][$member_srl]))
		{
			$filename = sprintf('files/member_extra_info/signature/%s%d.signature.php', getNumberingPath($member_srl), $member_srl);
			if(is_readable($filename))
			{
				$buff = FileHandler::readFile($filename);
				$signature = trim(substr($buff, 38));
				$GLOBALS['__member_info__']['signature'][$member_srl] = $signature;
			}
			else
			{
				$GLOBALS['__member_info__']['signature'][$member_srl] = null;
			}
		}
		return $GLOBALS['__member_info__']['signature'][$member_srl];
	}
	/**
	 * @brief check validate unregister account before
	 * @access public
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function isValidateUnregister($args)
	{
		$logged_info = Context::get('logged_info');
		if(!$logged_info)
		{
			return new Object(-1, 'msg_not_logged');
		}
		// Extract the necessary information in advance
		$password = trim($args->password);

		if(!$logged_info->password)
		{
			$columnList = array('member_srl', 'password');
			$memberInfo = $this->getMemberInfoByMemberSrl($logged_info->member_srl, $columnList);
			$logged_info->password = $memberInfo->password;
		}
		// Verify the cuttent password
		if(!$this->isValidPassword($logged_info->password, $password))
		{
			return new Object(-1, 'invalid_password');
		}

		return new Object();
	}

	/**
	 * @brief Delete profile image
	 **/
	function procMemberDeleteProfileImage() {
		$member_srl = Context::get('member_srl');
		if(!$member_srl) return new Object(0,'success');

		$logged_info = Context::get('logged_info');

		if($logged_info->is_admin != 'Y') {
			$oModuleModel = &getModel('module');
			$config = $oModuleModel->getModuleConfig('member');
			if($config->profile_image == 'N') return new Object(0,'success');
		}

		if($logged_info->is_admin == 'Y' || $logged_info->member_srl == $member_srl) {
			$oMemberModel = &getModel('member');
			$profile_image = $oMemberModel->getProfileImage($member_srl);
			FileHandler::removeFile($profile_image->file);
		}
		return new Object(0,'success');
	}

	/**
	 * @brief Delete Image name
	 **/
	function procMemberDeleteImageName() {
		$member_srl = Context::get('member_srl');
		if(!$member_srl) return new Object(0,'success');

		$logged_info = Context::get('logged_info');

		if($logged_info->is_admin != 'Y') {
			$oModuleModel = &getModel('module');
			$config = $oModuleModel->getModuleConfig('member');
			if($config->image_name == 'N') return new Object(0,'success');
		}

		if($logged_info->is_admin == 'Y' || $logged_info->member_srl == $member_srl) {
			$oMemberModel = &getModel('member');
			$image_name = $oMemberModel->getImageName($member_srl);
			FileHandler::removeFile($image_name->file);
		}
		return new Object(0,'success');
	}

	/**
	 * @brief Delete Image Mark
	 **/
	function procMemberDeleteImageMark() {
		$member_srl = Context::get('member_srl');
		if(!$member_srl) return new Object(0,'success');

		$logged_info = Context::get('logged_info');
		if($logged_info->is_admin == 'Y' || $logged_info->member_srl == $member_srl) {
			$oMemberModel = &getModel('member');
			$image_mark = $oMemberModel->getImageMark($member_srl);
			FileHandler::removeFile($image_mark->file);
		}
		return new Object(0,'success');
	}

	/**
	 * @brief Delete the signature file
	 **/
	function delSignature($member_srl) {
		$filename = sprintf('files/member_extra_info/signature/%s%d.gif', getNumberingPath($member_srl), $member_srl);
		FileHandler::removeFile($filename);
	}
}
/* End of file MemberDriverDefault.php */
/* Location: ./modules/member/drivers/default/MeberDriverDefault.php */
