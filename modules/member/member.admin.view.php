<?php
/**
 * @brief  member module's admin view class
 * @developer NHN (developers@xpressengine.com)
 */
class MemberAdminView extends Member
{
	private $groupList = NULL; ///< group list
	private $memberSrl = NULL; ///< selected member info

	/**
	 * @brief initialization
	 * @access public
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	function init()
	{
		$oMemberModel = getModel('member');

		// if member_srl exists, set memberInfo
		$this->memberSrl = Context::get('member_srl');

		// retrieve group list
		$this->groupList = $oMemberModel->getGroups();
		Context::set('group_list', $this->groupList);

		$security = new Security();
		$security->encodeHTML('group_list..');

		$this->setTemplatePath($this->module_path . 'tpl');
	}

	/**
	 * @brief display member list
	 * @access public
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function dispMemberAdminList()
	{
		$driverName = Context::get('driver');
		if(!$driverName)
		{
			$driverName = 'default';
		}
		// get driver list
		$oMemberModel = getModel('member');
		$driverList = $oMemberModel->getDrivers();
		Context::set('driverList', $driverList);

		// get driver instance
		$oDriver = getDriver('member', $driverName);
		$listTpl = $oDriver->getListTpl();

		Context::set('listTpl', $listTpl);
		$this->setTemplateFile('member_list');
	}

	/**
	 * @brief default configuration for member management
	 * @access public
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function dispMemberAdminConfig()
	{
		global $lang;            // retrieve configuration via module model instance
		$oModuleModel = &getModel('module');
		$oMemberModel = &getModel('member');
		$config = $oMemberModel->getMemberConfig();

		Context::set('config', $config);

		// list of skins for member module
		$skinList = $oModuleModel->getSkins($this->module_path);
		Context::set('skin_list', $skinList);

		$oEditorModel = getModel('editor');
		Context::set('editor_skin_list', $oEditorModel->getEditorSkinList());

		$security = new Security();
		$security->encodeHTML('config..');

		$drivers = $oMemberModel->getDrivers();
		Context::set('drivers', $drivers);

		$this->setTemplateFile('member_config');
	}

	/**
	 * @brief driver configuration for member management
	 * @access public
	 * @return void
	 * @developer NHN(developers@xpressengine.com)
	 */
	public function dispMemberAdminSigninConfig()
	{
		$oMemberModel = getModel('member');
		$drivers = $oMemberModel->getDrivers(TRUE);
		Context::set('drivers', $drivers);

		// get preivew
		$previewTpl = $oMemberModel->getPreviewSigninTpl();
		Context::set('previewTpl', $previewTpl);

		$this->setTemplateFile('signinConfig');
	}

	/**
	 * @brief driver configuration for member management
	 * @access public
	 * @return void
	 * @developer NHN(developers@xpressengine.com)
	 */
	public function dispMemberAdminDriverConfig()
	{
		$oMemberModel = getModel('member');

		$driverName = Context::get('driver');
		$driver = getDriver('member', $driverName);
		$oModuleModel = getModel('module');
		$driverInfo = $oModuleModel->getDriverInfoXml('member', $driverName);
		Context::set('driverInfo', $driverInfo);

		if(!$driver)
		{
			return $this->stop('msg_invalid_request');
		}

		Context::set('driverConfig', $driver->getConfigTpl());

		$this->setTemplateFile('driver_config');
	}

	/**
	 * @brief display member information
	 * @access public
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function dispMemberAdminInfo()
	{
		$driverName = Context::get('driver');
		if(!$driverName)
		{
			$driverName = 'default';
		}

		// get driver instance
		$oDriver = getDriver('member', $driverName);
		$infoTpl = $oDriver->getInfoTpl();

		Context::set('infoTpl', $infoTpl);
		$this->setTemplateFile('member_info');
		return;
	}

	/**
	 * @brief display member insert form
	 * @access public
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function dispMemberAdminInsert()
	{
		$driverName = Context::get('driver');
		if(!$driverName)
		{
			$driverName = 'default';
		}

		// get driver instance
		$oDriver = getDriver('member', $driverName);
		Context::set('oDriver', $oDriver);

		// get compliled insert template
		$insertTpl = $oDriver->getInsertTpl();
		Context::set('insertTpl', $insertTpl);

		$this->setTemplateFile('insert_member');
		return;

		// retrieve extend form
		$oMemberModel = &getModel('member');

		$memberInfo = Context::get('member_info');
		$memberInfo->signature = $oMemberModel->getSignature($this->memberInfo->member_srl);
		Context::set('member_info', $memberInfo);

		// get an editor for the signature
		if($memberInfo->member_srl) {
			$oEditorModel = &getModel('editor');
			$option->primary_key_name = 'member_srl';
			$option->content_key_name = 'signature';
			$option->allow_fileupload = false;
			$option->enable_autosave = false;
			$option->enable_default_component = true;
			$option->enable_component = false;
			$option->resizable = false;
			$option->height = 200;
			$editor = $oEditorModel->getEditor($this->memberInfo->member_srl, $option);
			Context::set('editor', $editor);
		}

		$security = new Security();
		$security->encodeHTML('extend_form_list..');
		$security->encodeHTML('extend_form_list..default_value.');

		$formTags = $this->_getMemberInputTag($memberInfo);
		Context::set('formTags', $formTags);
		$member_config = $oMemberModel->getMemberConfig();

		global $lang;
		$identifierForm->title = $lang->{$member_config->identifier};
		$identifierForm->name = $member_config->identifier;
		$identifierForm->value = $memberInfo->{$member_config->identifier};
		Context::set('identifierForm', $identifierForm);
		$this->setTemplateFile('insert_member');
	}


	/**
	 * @brief display member delete form
	 * @access public
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function dispMemberAdminDeleteForm()
	{
		if(!Context::get('member_srl')) return $this->dispMemberAdminList();
		$this->setTemplateFile('delete_form');
	}

	/**
	 * @brief display group list
	 * @access public
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function dispMemberAdminGroupList()
	{
		$oModuleModel = getModel('module');

		$config = $oModuleModel->getModuleConfig('member');
		Context::set('config', $config);

		$groupSrl = Context::get('group_srl');

		if($groupSrl && $this->groupList[$groupSrl])
		{
			Context::set('selected_group', $this->groupList[$groupSrl]);
			$this->setTemplateFile('group_update_form');
		}
		else
		{
			$this->setTemplateFile('group_list');
		}

		$output = $oModuleModel->getModuleFileBoxList();
		Context::set('fileBoxList', $output->data);
	}

	/**
	 * @brief Update all the member groups
	 * @access public
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function dispMemberAdminManageGroup()
	{
		// Get a list of the selected member
		$args->member_srl = trim(Context::get('member_srls'));
		$output = executeQueryArray('member.getMembers', $args);
		Context::set('member_list', $output->data);
		// Get a list of the selected member
		$oMemberModel = &getModel('member');
		Context::set('member_groups', $oMemberModel->getGroups());

		$security = new Security();
		$security->encodeHTML('member_list..');

		$this->setLayoutFile('popup_layout');
		$this->setTemplateFile('manage_member_group');
	}

	/**
	 * @brief Delete all members
	 * @access public
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function dispMemberAdminDeleteMembers()
	{
		// Get a list of the selected member
		$args->member_srl = trim(Context::get('member_srls'));
		$output = executeQueryArray('member.getMembers', $args);
		Context::set('member_list', $output->data);

		$this->setLayoutFile('popup_layout');
		$this->setTemplateFile('delete_members');
	}

	/**
	 * @brief Interface of driver view
	 * @access public
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function dispMemberAdminDriverInterface()
	{
		return $this->driverInterface();
	}
}

/* End of file member.admin.view.php */
/* Location: ./modules/member/member.admin.view.php */
