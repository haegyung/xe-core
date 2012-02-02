<?php
/**
 * @class  memberView
 * @author NHN (developers@xpressengine.com)
 * @brief View class of member module
 **/

class memberView extends member {

	var $group_list = NULL; // /< Group list information
	var $member_info = NULL; // /< Member information of the user
	var $skin = 'default';

	/**
	 * @brief Initialization
	 **/
	function init() {
		// Get the member configuration
		$oModuleModel = &getModel('module');
		$this->member_config = $oModuleModel->getModuleConfig('member');
		if(!$this->member_config->skin) $this->member_config->skin = "default";
		if(!$this->member_config->colorset) $this->member_config->colorset = "white";

		Context::set('member_config', $this->member_config);
		$skin = $this->member_config->skin;
		// Set the template path
		$tpl_path = sprintf('%sskins/%s', $this->module_path, $skin);
		if(!is_dir($tpl_path)) $tpl_path = sprintf('%sskins/%s', $this->module_path, 'default');
		$this->setTemplatePath($tpl_path);
	}

	/**
	 * @brief Display member information
	 * @access public
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function dispMemberInfo()
	{
		$oMemberModel = &getModel('member');

		// Don't display member info to non-logged user
		if(!Context::get('is_logged'))
		{
			return $this->stop('msg_not_permitted');
		}

		$logged_info = Context::get('logged_info');
		$member_srl = Context::get('member_srl');
		if(!$member_srl)
		{
			$member_srl = $logged_info->member_srl;
		}

		// find driver to exist member srl
		$site_module_info = Context::get('site_module_info');
		$columnList = array('member_srl', 'regdate', 'last_login', 'driver');
		$memberInfo = $oMemberModel->getMemberInfoByMemberSrl($member_srl, $site_module_info->site_srl, $columnList);

		if(!$memberInfo)
		{
			return $this->stop('msg_invaild_request');
		}

		$oDriver = getDriver('member', $memberInfo->driver);
		if(!$oDriver)
		{
			return $this->stop('msg_invaild_request');
		}

		Context::set('oDriver', $oDriver);

		// common info
		$memberInfo = get_object_vars($memberInfo);
		Context::set('memberCommonInfo', $memberInfo);

		// member info
		$memberInfoWithSignupForm = $oDriver->getMemberInfoWithSignupForm($memberInfo['member_srl']);

		// memberInfo
		Context::set('memberInfo', $memberInfoWithSignupForm->memberInfo);
		// signup form
		Context::set('signupForm', $memberInfoWithSignupForm->signupForm);
		// extend form
		Context::set('extend_form_list', $memberInfoWithSignupForm->extend_form_list);
		$this->setTemplateFile('member_info');
	}

	/**
	 * @brief Display member join form
	 * @access public
	 * @return void
	 * @developer NHN(developers@xpressengine.com)
	 **/
	public function dispMemberSignUpForm()
	{
		//setcookie for redirect url in case of going to member sign up
		if (!isset($_COOKIE["XE_REDIRECT_URL"]))
		{
			setcookie("XE_REDIRECT_URL", $_SERVER['HTTP_REFERER']);
		}

		$oMemberModel = &getModel('member');
		$oModuleModel = &getModel('module');

		// Get the member information if logged-in
		if($oMemberModel->isLogged())
		{
			return $this->stop('msg_already_logged');
		}

		// call a trigger (before)
		$trigger_output = ModuleHandler::triggerCall('member.dispMemberSignUpForm', 'before', $this->member_config);
		if(!$trigger_output->toBool())
		{
			return $trigger_output;
		}

		//Error appears if the member is not allowed to join
		if($this->member_config->enable_join != 'Y')
		{
			return $this->stop('msg_signup_disabled');
		}

		// set driver
		$usedDriver = array();
		if(!is_array($this->member_config->usedDriver))
		{
			$this->member_config->usedDriver = array();
		}

		foreach($this->member_config->usedDriver as $driverName)
		{
			$driverInfo = $oModuleModel->getDriverInfoXml('member', $driverName);
			if($driverInfo->options['signup']->value == 'Y')
			{
				$oDriver = getDriver('member', $driverName);
				$usedDriver[] = $oDriver;
			}
		}

		if(count($usedDriver) == 1)
		{
			$url = getNotEncodedUrl('act', 'dispMemberDriverSignUpForm', 'driver', $usedDriver[0]->getDriverName());
			$this->setRedirectUrl($url);
			return;
		}

		Context::set('usedDriver', $usedDriver);

		// Set a template file
		$this->setTemplateFile('signup.select');
	}

	/**
	 * @brief Display driver signup form
	 * @access public
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function dispMemberDriverSignUpForm()
	{
		$oMemberModel = getModel('member');

		// Get the member information if logged-in
		if($oMemberModel->isLogged())
		{
			return $this->stop('msg_already_logged');
		}

		$driver = Context::get('driver');
		$config = $this->member_config;
		Context::set('config', $config);

		// check driver
		if(!in_array($driver, $config->usedDriver))
		{
			return new Object(-1, 'msg_invalid_request');
		}

		$oModuleModel = getModel('module');
		// check signup option
		foreach($config->usedDriver as $driverName)
		{
			$driverInfo = $oModuleModel->getDriverInfoXml('member', $driverName);
			if($driverInfo->options['signup']->value != 'Y')
			{
				return new Object(-1, 'msg_invalid_request');
			}
		}

		// check driver object
		$oDriver = getDriver('member', $driver);
		if(!$oDriver)
		{
			return new Object(-1, 'msg_invalid_request');
		}

		// get diriver form tag
		$formTags = $oDriver->getSignupFormInfo();

		Context::set('formTags', $formTags);
		Context::set('oDriver', $oDriver);

		$this->setTemplateFile('signup_form');
	}

	/**
	 * @brief Modify member information
	 **/
	function dispMemberModifyInfo() {
		$oMemberModel = &getModel('member');

		// A message appears if the user is not logged-in
		if(!$oMemberModel->isLogged())
		{
			return $this->stop('msg_not_logged');
		}

		$logged_info = Context::get('logged_info');
		$member_srl = $logged_info->member_srl;

		$args->member_srl = $member_srl;
		$output = executeQuery('member.getDefaultDriver', $args);

		if(!$output->toBool())
		{
			return $output;
		}

		$driver = $output->data->driver;
		$driver = empty($driver) ? 'default' : $driver;

		$config = $this->member_config;
		Context::set('config', $config);

		// check driver
		if(!in_array($driver, $config->usedDriver))
		{
			return new Object(-1, 'msg_invalid_request');
		}

		// check driver object
		$oDriver = getDriver('member', $driver);
		if(!$oDriver)
		{
			return new Object(-1, 'msg_invalid_request');
		}

		// get diriver form tag
		$formTags = $oDriver->getModifyFormInfo($member_srl);

		Context::set('formTags', $formTags);
		Context::set('oDriver', $oDriver);

		$columnList = array('member_srl', 'allow_message');
		$member_info = $oMemberModel->getMemberInfoByMemberSrl($member_srl, 0, $columnList);
		//$member_info->signature = $oMemberModel->getSignature($member_srl);
		Context::set('member_info',$member_info);

		/*
		// Get a list of extend join form
		Context::set('extend_form_list', $oMemberModel->getCombineJoinForm($member_info));

		Context::set('openids', $oMemberModel->getMemberOpenIDByMemberSrl($member_srl));
		// Editor of the module set for signing by calling getEditor
		if($member_info->member_srl) {
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
			$editor = $oEditorModel->getEditor($member_info->member_srl, $option);
			Context::set('editor', $editor);
		}

		$oMemberAdminView = &getAdminView('member');
		$formTags = $oMemberAdminView->_getMemberInputTag($member_info);
		Context::set('formTags', $formTags);

		$member_config = $oMemberModel->getMemberConfig();
		Context::set('member_config', $member_config);

		global $lang;
		$identifierForm->title = $lang->{$member_config->identifier};
		$identifierForm->name = $member_config->identifier;
		$identifierForm->value = $member_info->{$member_config->identifier};
		Context::set('identifierForm', $identifierForm);
		// Set a template file

		*/
		$this->setTemplateFile('modify_info');
	}


	/**
	 * @brief Display documents written by the member
	 **/
	function dispMemberOwnDocument() {
		$oMemberModel = &getModel('member');
		// A message appears if the user is not logged-in
		if(!$oMemberModel->isLogged()) return $this->stop('msg_not_logged');

		$logged_info = Context::get('logged_info');
		$member_srl = $logged_info->member_srl;

		$module_srl = Context::get('module_srl');
		Context::set('module_srl',Context::get('selected_module_srl'));
		Context::set('search_target','member_srl');
		Context::set('search_keyword',$member_srl);

		$oDocumentAdminView = &getAdminView('document');
		$oDocumentAdminView->dispDocumentAdminList();

		Context::set('module_srl', $module_srl);
		$this->setTemplateFile('document_list');
	}

	/**
	 * @brief Display documents scrapped by the member
	 **/
	function dispMemberScrappedDocument() {
		$oMemberModel = &getModel('member');
		// A message appears if the user is not logged-in
		if(!$oMemberModel->isLogged()) return $this->stop('msg_not_logged');

		$logged_info = Context::get('logged_info');
		$args->member_srl = $logged_info->member_srl;
		$args->page = (int)Context::get('page');

		$output = executeQuery('member.getScrapDocumentList', $args);
		Context::set('total_count', $output->total_count);
		Context::set('total_page', $output->total_page);
		Context::set('page', $output->page);
		Context::set('document_list', $output->data);
		Context::set('page_navigation', $output->page_navigation);

		$this->setTemplateFile('scrapped_list');
	}

	/**
	 * @brief Display documents saved by the member
	 **/
	function dispMemberSavedDocument() {
		$oMemberModel = &getModel('member');
		// A message appears if the user is not logged-in
		if(!$oMemberModel->isLogged()) return $this->stop('msg_not_logged');
		// Get the saved document(module_srl is set to member_srl instead)
		$logged_info = Context::get('logged_info');
		$args->member_srl = $logged_info->member_srl;
		$args->page = (int)Context::get('page');
		$args->statusList = array('TEMP');

		$oDocumentModel = &getModel('document');
		$output = $oDocumentModel->getDocumentList($args, true);
		Context::set('total_count', $output->total_count);
		Context::set('total_page', $output->total_page);
		Context::set('page', $output->page);
		Context::set('document_list', $output->data);
		Context::set('page_navigation', $output->page_navigation);

		$this->setTemplateFile('saved_list');
	}

	/**
	 * @brief Display the login form
	 **/
	function dispMemberLoginForm()
	{
		if(Context::get('is_logged'))
		{
			Context::set('redirect_url', getUrl('act',''));
			$this->setTemplatePath($this->module_path.'tpl');
			$this->setTemplateFile('redirect.html');
			return;
		}

		$oMemberModel = getModel('member');
		$config = $oMemberModel->getMemberConfig();

		Context::set('config', $config);

		$drivers = array();
		foreach($config->signinConfig as $value)
		{
			$driver = new stdClass();
			$driver->name = $value->name;
			if($value->name == 'horizontal')
			{
				$items = array();
				foreach($value->items as $value2)
				{
					$driver2 = new stdClass();
					$driver2->name = $value2->name;
					$driver2->driver =  getDriver('member', $value2->name);
					$driver2->type = $value2->type;
					$items[] = $driver2;
				}
				$driver->items = $items;
			}
			else
			{
				$driver->driver = getDriver('member', $value->name);
				$driver->type =  $value->type;
			}
			$drivers[] = $driver;
		}

		Context::set('drivers', $drivers);

		// Set a template file
		Context::set('referer_url', $_SERVER['HTTP_REFERER']);
		Context::set('act', 'procMemberLogin');
		$this->setTemplateFile('login_form');
	}

	/**
	 * @brief Change the user password
	 **/
	function dispMemberModifyPassword() {
		$oMemberModel = &getModel('member');
		// A message appears if the user is not logged-in
		if(!$oMemberModel->isLogged()) return $this->stop('msg_not_logged');

		$logged_info = Context::get('logged_info');
		$member_srl = $logged_info->member_srl;

		$columnList = array('member_srl', 'user_id');
		$member_info = $oMemberModel->getMemberInfoByMemberSrl($member_srl, 0, $columnList);
		Context::set('member_info',$member_info);
		// Set a template file
		$this->setTemplateFile('modify_password');
	}

	/**
	 * @brief Member withdrawl
	 **/
	function dispMemberLeave()
	{
		$oMemberModel = &getModel('member');
		// A message appears if the user is not logged-in
		if(!$oMemberModel->isLogged()) return $this->stop('msg_not_logged');

		$logged_info = Context::get('logged_info');
		$member_srl = $logged_info->member_srl;

		$member_info = $oMemberModel->getMemberInfoByMemberSrl($member_srl);
		Context::set('member_info',$member_info);
		// Set a template file
		$this->setTemplateFile('leave_form');
	}

	/**
	 * @brief OpenID member withdrawl
	 **/
	function dispMemberOpenIDLeave() {
		$oMemberModel = &getModel('member');
		// A message appears if the user is not logged-in
		if(!$oMemberModel->isLogged()) return $this->stop('msg_not_logged');

		$logged_info = Context::get('logged_info');
		$member_srl = $logged_info->member_srl;

		$member_info = $oMemberModel->getMemberInfoByMemberSrl($member_srl);
		Context::set('member_info',$member_info);
		// Set a template file
		$this->setTemplateFile('openid_leave_form');
	}

	/**
	 * @brief Member log-out
	 **/
	function dispMemberLogout() {
		$oMemberController = &getController('member');
		$output = $oMemberController->procMemberLogout();
		if(!$output->redirect_url)
			$this->setRedirectUrl(getNotEncodedUrl('act', ''));
		else
			$this->setRedirectUrl($output->redirect_url);

		return;
	}

	/**
	 * @brief Display a list of saved articles
	 * @Deplicated - instead Document View - dispTempSavedList method use
	 **/
	function dispSavedDocumentList() {
		return new Object(0, 'Deplicated method');
	}

	/**
	 * @brief Find user ID and password
	 **/
	function dispMemberFindAccount() {
		if(Context::get('is_logged')) return $this->stop('already_logged');

		$oMemberModel = &getModel('member');
		$config = $oMemberModel->getMemberConfig();

		Context::set('identifier', $config->identifier);

		$this->setTemplateFile('find_member_account');
	}

	/**
	 * @brief Generate a temporary password
	 **/
	function dispMemberGetTempPassword() {
		if(Context::get('is_logged')) return $this->stop('already_logged');

		$user_id = Context::get('user_id');
		$temp_password = $_SESSION['xe_temp_password_'.$user_id];
		unset($_SESSION['xe_temp_password_'.$user_id]);

		if(!$user_id||!$temp_password) return new Object(-1,'msg_invaild_request');

		Context::set('temp_password', $temp_password);

		$this->setTemplateFile('find_temp_password');
	}

	/**
	 * @brief Page of re-sending an authentication mail
	 **/
	function dispMemberResendAuthMail() {
		if(Context::get('is_logged')) return $this->stop('already_logged');

		$this->setTemplateFile('resend_auth_mail');
	}

	/**
	 * @brief Interface of driver view
	 * @access public
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function dispMemberDriverInterface()
	{
		return $this->driverInterface();
	}

	/**
	 * @brief 이메일 주소를 기본 로그인 계정 사용시 이메일 주소 변경을 위한 화면 추가
	 **/
	function dispMemberModifyEmailAddress(){
		if(!Context::get('is_logged')) return $this->stop('msg_not_logged');

		$this->setTemplateFile('modify_email_address');
	}
}
?>
