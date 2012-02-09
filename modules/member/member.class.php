<?php
@require_once(_XE_PATH_ . 'modules/member/classes/MemberVO.php');

/**
 * @brief high class of the member module
 * @developer NHN (developers@xpressengine.com)
 */
class Member extends ModuleObject
{

	/**
	 * @brief constructor
	 * @access public
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function __construct()
	{
		if(!Context::isInstalled())
		{
			return;
		}

		$oModuleModel = getModel('module');
		$member_config = $oModuleModel->getModuleConfig('member');

		// Set to use SSL upon actions related member join/information/password and so on
		if(Context::get('_use_ssl') == 'optional')
		{
			Context::addSSLAction('dispMemberModifyPassword');
			Context::addSSLAction('dispMemberSignUpForm');
			Context::addSSLAction('dispMemberModifyInfo');
			Context::addSSLAction('procMemberLogin');
			Context::addSSLAction('procMemberModifyPassword');
			Context::addSSLAction('procMemberInsert');
			Context::addSSLAction('procMemberModifyInfo');
			Context::addSSLAction('procMemberFindAccount');
		}
	}

	/**
	 * @brief Implement if additional tasks are necessary when installing
	 * @access public
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function moduleInstall()
	{
		// Register action forward (to use in administrator mode)
		$oModuleController = getController('module');

		$oDB = DB::getInstance();
		$oDB->addIndex("member_group", "idx_site_title", array("site_srl", "title"), TRUE);

		$oModuleModel = getModel('module');
		$args = $oModuleModel->getModuleConfig('member');

		// Set the basic information
		$args->enable_join = 'Y';
		if(!$args->enable_openid)
		{
			$args->enable_openid = 'N';
		}
		if(!$args->enable_auth_mail)
		{
			$args->enable_auth_mail = 'N';
		}
		if($args->group_image_mark != 'Y')
		{
			$args->group_image_mark = 'N';
		}

		$oModuleController->insertModuleConfig('member', $args);

		// Create a member controller object
		$oMemberController = getController('member');
		$oMemberAdminController = getAdminController('member');

		$oMemberModel = getModel('member');
		$groups = $oMemberModel->getGroups();
		if(!count($groups))
		{
			// Set an administrator, regular member(group1), and associate member(group2)
			$group_args->title = Context::getLang('admin_group');
			$group_args->is_default = 'N';
			$group_args->is_admin = 'Y';
			$output = $oMemberAdminController->insertGroup($group_args);

			unset($group_args);
			$group_args->title = Context::getLang('default_group_1');
			$group_args->is_default = 'Y';
			$group_args->is_admin = 'N';
			$output = $oMemberAdminController->insertGroup($group_args);

			unset($group_args);
			$group_args->title = Context::getLang('default_group_2');
			$group_args->is_default = 'N';
			$group_args->is_admin = 'N';
			$oMemberAdminController->insertGroup($group_args);
		}

		return new Object();
	}

	/**
	 * @brief a method to check if successfully installed
	 * @access public
	 * @return boolean
	 * @developer NHN (developer@xpressengine.com)
	 */
	public function checkUpdate()
	{
		$oDB = DB::getInstance();
		$oModuleModel = getModel('module');

		// check member directory (11/08/2007 added)
		if(!is_dir('./files/member_extra_info'))
		{
			return TRUE;
		}

		// check member directory (22/10/2007 added)
		if(!is_dir('./files/member_extra_info/profile_image'))
		{
			return TRUE;
		}

		// Add a column(is_register) to "member_auth_mail" table (22/04/2008)
		$act = $oDB->isColumnExists('member_auth_mail', 'is_register');
		if(!$act)
		{
			return TRUE;
		}

		// Add a column(site_srl) to "member_group_member" table (11/15/2008)
		if(!$oDB->isColumnExists('member_group_member', 'site_srl'))
		{
			return TRUE;
		}
		if(!$oDB->isColumnExists('member_group', 'site_srl'))
		{
			return TRUE;
		}
		if($oDB->isIndexExists('member_group', 'uni_member_group_title'))
		{
			return TRUE;
		}

		// Add a column for list_order (05/18/2011)
		if(!$oDB->isColumnExists('member_group', 'list_order'))
		{
			return TRUE;
		}

		// image_mark 추가 (2009. 02. 14)
		if(!$oDB->isColumnExists('member_group', 'image_mark'))
		{
			return TRUE;
		}
		// Add c column for password expiration date
		if(!$oDB->isColumnExists('member', 'change_password_date'))
		{
			return TRUE;
		}

		// Add columns of question and answer to verify a password
		if(!$oDB->isColumnExists('member', 'find_account_question'))
		{
			return TRUE;
		}
		if(!$oDB->isColumnExists('member', 'find_account_answer'))
		{
			return TRUE;
		}

		// TODO : this code move to driver
		// if(!$oDB->isColumnExists('member', 'list_order'))
		// {
			// return TRUE;
		// }
		// if(!$oDB->isIndexExists('member', 'idx_list_order'))
		// {
			// return TRUE;
		// }
//
		if(!$oDB->isColumnExists('member_join_form', 'driver'))
		{
			return TRUE;
		}
		if(!$oDB->isIndexExists('member_join_form', 'unique_name_driver'))
		{
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @brief Execute update
	 * @access public
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	function moduleUpdate()
	{
		$oDB = DB::getInstance();
		$oModuleController = getController('module');

		// Add a column
		if(!$oDB->isColumnExists('member_auth_mail', 'is_register'))
		{
			$oDB->addColumn('member_auth_mail', 'is_register', 'char', 1, 'N', TRUE);
		}

		// Add a column(site_srl) to "member_group_member" table (11/15/2008)
		if(!$oDB->isColumnExists('member_group_member', 'site_srl'))
		{
			$oDB->addColumn('member_group_member', 'site_srl', 'number', 11, 0, TRUE);
			$oDB->addIndex('member_group_member', 'idx_site_srl', 'site_srl', FALSE);
		}
		if(!$oDB->isColumnExists('member_group', 'site_srl'))
		{
			$oDB->addColumn('member_group', 'site_srl', 'number', 11, 0, TRUE);
			$oDB->addIndex('member_group', 'idx_site_title', array('site_srl', 'title'), TRUE);
		}
		if($oDB->isIndexExists('member_group', 'uni_member_group_title'))
		{
			$oDB->dropIndex('member_group', 'uni_member_group_title', TRUE);
		}

		// Add a column(list_order) to "member_group" table (05/18/2011)
		if(!$oDB->isColumnExists('member_group', 'list_order'))
		{
			$oDB->addColumn('member_group', 'list_order', 'number', 11, '', TRUE);
			$oDB->addIndex('member_group', 'idx_list_order', 'list_order', FALSE);
			$output = executeQuery('member.updateAllMemberGroupListOrder');
		}
		// Add a column for image_mark (02/14/2009)
		if(!$oDB->isColumnExists('member_group', 'image_mark'))
		{
			$oDB->addColumn('member_group', 'image_mark', 'text');
		}
		// Add a column for password expiration date
		if(!$oDB->isColumnExists("member", "change_password_date"))
		{
			$oDB->addColumn('member', 'change_password_date', 'date');
			executeQuery('member.updateAllChangePasswordDate');
		}

		// Add columns of question and answer to verify a password
		if(!$oDB->isColumnExists('member', 'find_account_question'))
		{
			$oDB->addColumn('member', 'find_account_question', 'number', 11);
		}
		if(!$oDB->isColumnExists('member', 'find_account_answer'))
		{
			$oDB->addColumn('member', 'find_account_answer', 'varchar', 250);
		}

		// TODO : this code move to driver
		// if(!$oDB->isColumnExists('member', 'list_order'))
		// {
			// $oDB->addColumn('member', 'list_order', 'number', 11);
			// set_time_limit(0);
			// $args->list_order = 'member_srl';
			// executeQuery('member.updateMemberListOrderAll', $args);
			// executeQuery('member.updateMemberListOrderAll');
		// }
		// if(!$oDB->isIndexExists('member', 'idx_list_order'))
		// {
			// $oDB->addIndex('member', 'idx_list_order', array('list_order'));
		// }

		if(!$oDB->isColumnExists('member_join_form', 'driver'))
		{
			$oDB->addColumn('member_join_form', 'driver', 'varchar', 60, 'default', TRUE);
			executeQuery('member.updateMemberJoinFormDriverDefault');
		}

		if(!$oDB->isIndexExists('member_join_form', 'unique_name_driver'))
		{
			$oDB->addIndex('member_join_form', 'unique_name_driver', array('column_name', 'driver'), TRUE);
		}

		return new Object(0, 'success_updated');
	}

	/**
	 * @brief Re-generate the cache file
	 * @access public
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	function recompileCache()
	{
		set_include_path(_XE_PATH_ . 'modules/member/php-openid-1.2.3');
		require_once('Auth/OpenID/XEStore.php');
		$store = new Auth_OpenID_XEStore();
		$store->reset();

		return new Object();
	}

	/**
	 * @brief Interface of driver process
	 * @access protected
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	protected function driverInterface()
	{
		$driver = Context::get('driver');
		$driverAct = Context::get('dact');

		// get instance of driver
		$oDriver = getDriver('member', $driver);
		if(!$oDriver)
		{
			return $this->stop('msg_invalid_request');
		}

		// check interface name
		$interface = $oDriver->getInterfaceNames();
		$class = get_class($this);
		$classType = str_replace('member', '', $class);
		if(!in_array($driverAct, $interface->{$classType}))
		{
			return $this->stop('msg_invalid_request');
		}
		if(!method_exists($oDriver, $driverAct))
		{
			return $this->stop('msg_invalid_request');
		}

		$output = $oDriver->{$driverAct}($this);
		$this->setError($output->getError());
		$this->setMessage($output->getMessage());
		return $this;
	}
}

/* End of file member.class.php */
/* Location: ./modules/member/member.class.php */
