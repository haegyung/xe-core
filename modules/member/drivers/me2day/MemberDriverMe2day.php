<?php

require_once _XE_PATH_ . 'modules/member/drivers/me2day/MemberVoMe2day.php';
require_once _XE_PATH_ . 'modules/member/drivers/me2day/Me2dayApi.php';

/**
 * @brief class of me2day driver
 * @developer NHN (developers@xpressengine.com)
 */
class MemberDriverMe2day extends MemberDriver
{
	const SESSION_NAME = '__ME2DAY_DRIVER__';
	private static $extractVars = array('me2dayId');

	var $oMe2dayApi;

	/**
	 * @brief Constructor
	 * @access public
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function __construct()
	{
		parent::__construct();

		$config = $this->getConfig();
		$this->oMe2dayApi = new Me2dayApi($config->applicationKey);
	}

	/**
	 * @brief Get interface
	 * @access public
	 * @return stdClass
	 * @developer NHN (developers@xpresengine.com)
	 */
	public function getInterfaceNames()
	{
		$interface->AdminView = array();
		$interface->AdminController = array('procSaveConfig');
		$interface->View = array('dispLogin', 'dispCallback');
		$interface->Contoller = array();

		return $interface;
	}

	/**
	 * @brief Install Driver
	 * @access public
	 * @return Object
	 * @developer NHN (developers@xpresengine.com)
	 */
	public function installDriver()
	{
		return new Object();
	}


	/**
	 * @brief Check update for driver
	 * @access public
	 * @return boolean
	 * @developer NHN (developers@xpresengine.com)
	 */
	public function checkUpdate()
	{
		return FALSE;
	}

	/**
	 * @brief Update for driver
	 * @access public
	 * @return Object
	 * @developer NHN (developers@xpresengine.com)
	 */
	public function updateDriver()
	{
		return new Object();
	}

	/**
	 * @brief Get MemberVo
	 * @access public
	 * @param $memberSrl
	 * @return MemberVo
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getMemberVo($memberSrl)
	{
		$args->member_srl = $memberSrl;
		$output = executeQuery('member.driver.me2day.getMemberInfoByMemberSrl', $args);
		if(!$output->toBool())
		{
			throw new MemberDriverException($output->getMessage());
		}

		if(!$output->data)
		{
			return FALSE;
		}

		$memberVo = new MemberVoMe2day($output->data);

		return $memberVo;
	}

	/**
	 * @brief Get MemberVo By me2day id
	 * @access public
	 * @param $me2dayId
	 * @return MemberVo
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getMemberVoByMe2dayId($me2dayId)
	{
		$args->user_id = $me2dayId;
		$output = executeQuery('member.driver.me2day.getMemberInfo', $args);
		if(!$output->toBool())
		{
			throw new MemberDriverException($output->getMessage());
		}

		if(!$output->data)
		{
			return FALSE;
		}

		$memberVo = new MemberVoMe2day($output->data);

		return $memberVo;
	}

	/**
	 * @brief Insert member
	 * @access public
	 * @param $memberInfo insert member information (type of stdClass)
	 * @param $passwordIsHashed
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function insertMember($memberInfo, $passwordIsHashed = FALSE)
	{
		if(!isset($memberInfo->me2dayId))
		{
			return new Object(-1, 'me2day_msg_missing_me2day_id');
		}

		// check duplicate
		$memberVo = $this->getMemberVoByMe2dayId($memberInfo->me2dayId);
		if($memberVo)
		{
			return new Object(-1, 'msg_exists_user_id');
		}

		// insert
		$args = new stdClass();
		$args->member_srl = $memberInfo->member_srl;
		$args->user_id = $memberInfo->me2dayId;
		$args->extra_vars = $memberInfo->extra_vars;

		$oDB = DB::getInstance();
		$oDB->begin();

		$output = executeQuery('member.driver.me2day.insertMember', $args);
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
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function deleteMember($memberSrl)
	{
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

		return TRUE;
	}

	/**
	 * @brief Validate Login Info
	 * @access public
	 * @param $loginInfo login information
	 * @return memberVo
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function validateLoginInfo($loginInfo)
	{
	}

	/**
	 * @biief get member signup form format
	 * @access public
	 * @param $memberInfo (when modify member_info of modified target member)
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getSignupFormInfo($memberInfo = NULL)
	{
		return; // do nothing...
	}

	/**
	 * @brief get member modify form format
	 * @access public
	 * @param $memberSrl (when modify member_info of modified target member)
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getModifyFormInfo($memberSrl)
	{
	}

	/**
	 * @brief Create signup ruleset
	 * @access protected
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	protected function createSignupRuleset()
	{
		return; // do nothing...
	}

	/**
	 * @brief Create signin ruleset
	 * @access protected
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	protected function createSigninRuleset()
	{
		return; // do nothing...
	}

	/**
	 * @brief Create admin insert ruleset
	 * @access protected
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	protected function createAdminInsertRuleset()
	{
		return; // do nothing...
	}

	/**
	 * @brief Get driver config view tpl
	 * @access public
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getConfigTpl()
	{
		$config = $this->getConfig();
		Context::set('config', $config);
		return parent::getConfigTpl();
	}

	/**
	 * @breif display login. redirect to me2day
	 * @access public
	 * @param $oModule MemberView
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function dispLogin($oModule)
	{
		try
		{
			$this->oMe2dayApi->doLogin();
		}
		catch(Exception $e)
		{
			return new Object(-1, $e->getMessage());
		}

		$_SESSION[self::SESSION_NAME]['mid'] = Context::get('mid');
		$_SESSION[self::SESSION_NAME]['vid'] = Context::get('vid');
		$_SESSION[self::SESSION_NAME]['document_srl'] = Context::get('document_srl');

		$oModule->setLayoutPath('./common/tpl/');
		$oModule->setLayoutFile('default_layout');

		return new Object();
	}

	/**
	 * @breif callback
	 * @access public
	 * @param $oModule MemberView
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function dispCallback($oModule)
	{
		if($this->oMe2dayApi->isLogged())
		{
			$userKey = $this->oMe2dayApi->getUserKey();
			try
			{
				$memberVo = $this->getMemberVoByMe2dayId($userKey->userId);
			}
			catch(MemberDriverException $e)
			{
				return new Object(-1, $e->getMessage());
			}

			$oMemberController = getController('member');

			// if not exist, insert
			if(!$memberVo)
			{
				$memberInfo = new stdClass();
				$memberInfo->me2dayId = $userKey->userId;

				$output = $oMemberController->insertMember($memberInfo, FALSE, 'me2day');
				if(!$output->toBool())
				{
					return $output;
				}

				$memberSrl = $output->get('member_srl');
			}
			else
			{
				$memberSrl = $memberVo->getMemberSrl();
			}

			// signin
			$output = $oMemberController->doSignin('me2day', $memberSrl);
			if(!$output->toBool())
			{
				return $output;
			}
		}

		$mid = $_SESSION[self::SESSION_NAME]['mid'];
		$vid = $_SESSION[self::SESSION_NAME]['vid'];
		$documentSrl = $_SESSION[self::SESSION_NAME]['document_srl'];

		$url = getNotEncodedUrl('', 'vid', $vid, 'mid', $mid, 'document_srl', $documentSrl);
		$oModule->setRedirectUrl($url);

		$oModule->setLayoutPath('./common/tpl/');
		$oModule->setLayoutFile('default_layout');

		return new Object();
	}

	/**
	 * @breif callback
	 * @access private
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	private function destroySession()
	{
		unset($_SESSION[self::SESSION_NAME]);
	}

	/**
	 * @breif save config
	 * @access public
	 * @param $oModule MemberAdminController
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function procSaveConfig($oModule)
	{
		$config->applicationKey = Context::get('applicationKey');

		$oModuleController = getController('module');
		$output = $oModuleController->insertDriverConfig('member', 'me2day', $config);
		if(!$output->toBool())
		{
			return $output;
		}

		$oModule->setRedirectUrl(getNotEncodedUrl('', 'module', 'admin', 'act', 'dispMemberAdminDriverConfig', 'driver', 'me2day'));

		return new Object();
	}

	/**
	 * @brief Get driver config
	 * @access public
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getConfig()
	{
		$oModuleModel = getModel('module');
		$config = $oModuleModel->getDriverConfig('member', 'me2day');

		return $config;
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
		$extraVars = parent::extractExtraVars($memberInfo);

		foreach(self::$extractVars as $column)
		{
			unset($extraVars->{$column});
		}

		return $extraVars;
	}

}

/* End of file MemberDriverMe2day.php */
/* Location: ./modules/member/drivers/me2day/MemberDriverMe2day.php */
