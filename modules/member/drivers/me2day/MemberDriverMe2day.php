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
	private static $extractVars = array('me2dayId', 'me2dayNickName', 'face');

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
		$interface->AdminController = array('procSaveConfig', 'procSaveSignUpForm');
		$interface->View = array('dispLogin', 'dispCallback', 'dispUnregister', 'dispSignUp');
		$interface->Controller = array('procRefreshInfo', 'procUnregister');

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
	 * @brief Get member info with signupForm
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
		$oMemberVo = $this->getMemberVo($memberSrl);
		$memberInfo = $oMemberVo->getMemberInfo();

		$oMemberModel = getModel('member');

		$defaultForm = $this->getFormInfoDefault($memberInfo);
		$extendForm = $this->getFormInfo($memberInfo);
		$result->signupForm = array_merge($defaultForm, $extendForm);
		$result->memberInfo = get_object_vars($memberInfo);
		$result->memberInfo['face'] = sprintf('<img src="%s" alt="" />', $result->memberInfo['face']);
		$result->extend_form_list = $oMemberModel->getCombineJoinForm($memberInfo);

		return $result;

		// make result
		$result = new stdClass();
		$result->signupForm = array();

		$formList = array('me2dayId', 'me2dayNickName', 'face');
		$langList = array('me2day_id', 'me2day_nick_name', 'me2day_face');

		foreach($formList as $no => $formName)
		{
			$formInfo = new stdClass();
			$formInfo->title = Context::getLang($langList[$no]);
			$formInfo->name = $formName;
			$formInfo->isUse = TRUE;
			$formInfo->isDefaultForm = TRUE;
			$result->signupForm[] = $formInfo;
		}
		$result->memberInfo = get_object_vars($oMemberVo->getMemberInfo());
		$result->memberInfo['face'] = sprintf('<img src="%s" alt="" />', $result->memberInfo['face']);

		return $result;
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
		if(!isset($memberInfo->me2dayId, $memberInfo->me2dayNickName, $memberInfo->face))
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
		$args->nick_name = $memberInfo->me2dayNickName;
		$args->face = $memberInfo->face;
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
		if(!$memberSrl)
		{
			return new Object(-1, 'msg_invalid_request');
		}

		$args->member_srl = $memberSrl;
		$output = executeQuery('member.driver.me2day.deleteMember', $args);
		return $output;
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
		if(!$memberInfo->member_srl)
		{
			return new Object(-1, 'msg_invalid_request');
		}

		$args->member_srl = $memberInfo->member_srl;
		$args->nick_name = $memberInfo->me2dayNickName;
		$args->face = $memberInfo->face;
		$args->extra_vars = serialize($this->extractExtraVars($memberInfo));

		$output = executeQuery('member.driver.me2day.updateMember', $args);
		if(!$output->toBool())
		{
			return $output;
		}

		return new Object();
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
	 * @brief Check values when member insert
	 * @access public
	 * @param $args
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function isValidateAdminInsert($args)
	{
		return $this->checkDynamicRuleset($this->getAdminInsertRuleset());
	}

	/**
	 * @brief Check values when member signup
	 * @access public
	 * @param $args
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function isValidateSignUp($args)
	{
		$output = $this->checkDynamicRuleset($this->getSignupRuleset());
		if(!$output->toBool())
		{
			return $output;
		}

		try
		{
			$person = $this->oMe2dayApi->getPerson();
			$args->me2dayId = $person->id;
			$args->me2dayNickName = $person->nickname;
			$args->face = $person->face;
		}
		catch(Exception $e)
		{
			return new Object(-1, $e->getMessage());
		}

		if(!$args->allow_message)
		{
			$args->allow_message = 'Y';
		}

		return new Object();
	}

	/**
	 * @brief Check values when member modify
	 * @access public
	 * @param $args
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function isValidateModify($args)
	{
		return $this->checkDynamicRuleset($this->getModifyRuleset());
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
		$person = $this->oMe2dayApi->getPerson();

		$memberInfo = new stdClass();
		$memberInfo->me2dayId = $person->id;
		$memberInfo->me2dayNickName = $person->nickname;
		$memberInfo->face = $person->face;

		$defaultFormInfo = $this->getFormInfoDefault($memberInfo);
		$extendFormInfo = $this->getFormInfo($memberInfo);

		// remove no required
		foreach($extendFormInfo as $no => $formInfo)
		{
			if(!$formInfo->required)
			{
				unset($extendFormInfo[$no]);
			}
		}

		$formInfo = array_merge($defaultFormInfo, $extendFormInfo);

		return $formInfo;
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
		$oMemberVo = $this->getMemberVo($memberSrl);
		$memberInfo = $oMemberVo->getMemberInfo();

		$defaultFormInfo = $this->getFormInfoDefault($memberInfo);
		$extendFormInfo = $this->getFormInfo($memberInfo);
		$formInfos = array_merge($defaultFormInfo, $extendFormInfo);

		$formInfo = new stdClass();
		$formInfo->title = '';
		$formInfo->inputTag = sprintf('<button id="refresh_info" type="button">%s</button>', Context::getLang('me2day_refresh_info'));
		$formInfo->description = Context::getLang('me2day_about_refresh_info');

		array_unshift($formInfos, $formInfo);

		return $formInfos;
		$formList = array('me2dayId', 'me2dayNickName', 'face');
		$langList = array('me2day_id', 'me2day_nick_name', 'me2day_face');

		$formTags = array();
		$memberInfo = $oMemberVo->getMemberInfo();
		foreach($formList as $no => $formName)
		{
			$formInfo = new stdClass();
			$formInfo->title = Context::getLang($langList[$no]);

			if($formName == 'face')
			{
				$formInfo->inputTag = sprintf('<img src="%s" alt="" />', $memberInfo->{$formName});
			}
			else
			{
				$formInfo->inputTag = $memberInfo->{$formName};
			}
			$formTags[] = $formInfo;
		}

		$formTags[] = $formInfo;

		return $formTags;
	}

	/**
	 * @brief get signup ruleset field
	 * @access protected
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	protected function getSignUpRulesetField()
	{
		$formInfos = $this->getFormInfo();

		$fields = array();

		$type = array('email_address' => 'email', 'homepage' => 'url');
		foreach($formInfos as $formInfo)
		{
			$rule = $type[$formInfo->type];
			if($rule)
			{
				$rule = sprintf('rule="%s"', $rule);
			}

			$required = '';
			if($formInfo->required)
			{
				$required = 'required="true"';
			}

			if($rule || $required)
			{
				$fields[] = sprintf('<field name="%s" %s %s />', $formInfo->name, $rule, $required);
			}
		}

		return $fields;
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
		$this->createRulesets();
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
		$this->createRulesets();
		return new Object();
	}

	/**
	 * @brief Create rulesets
	 * @access private
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	private function createRulesets()
	{
		$this->createSignupRuleset();
		$this->createModifyRuleset();
		$this->createSigninRuleset();
		$this->createAdminInsertRuleset();
	}

	/**
	 * @brief Create signup ruleset
	 * @access protected
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	protected function createSignupRuleset()
	{
		$fields = $this->getSignUpRulesetField();
		$this->createRuleset($this->getSignupRuleset(TRUE), $fields);
	}

	/**
	 * @brief Create modify ruleset
	 * @access protected
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	protected function createModifyRuleset()
	{
		$fields = $this->getSignUpRulesetField();
		$this->createRuleset($this->getModifyRuleset(TRUE), $fields);
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
		$formInfos = $this->getFormInfo();

		$fields = array();

		$type = array('email_address' => 'email', 'homepage' => 'url');
		foreach($formInfos as $formInfo)
		{
			$rule = $type[$formInfo->type];
			if($rule)
			{
				$fields[] = sprintf('<field name="%s" rule="%s" />', $formInfo->name, $rule);
			}
		}

		$this->createRuleset($this->getAdminInsertRuleset(TRUE), $fields);
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
	 * @brief Get member list tpl
	 * @access public
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getListTpl()
	{
		// make filter title
		$filter = Context::get('filter_type');
		switch($filter)
		{
			case 'super_admin':
				Context::set('filter_type_title', Context::getLang('cmd_show_super_admin_member'));
				break;
			case 'enable':
				Context::set('filter_type_title', Context::getLang('approval'));
				break;
			case 'disable':
				Context::set('filter_type_title', Context::getLang('denied'));
				break;
			default:
				Context::set('filter_type_title', Context::getLang('cmd_show_all_member'));
		}

		// make sort order
		$sortIndex = Context::get('sort_index');
		$sortOrder = Context::get('sort_order');

		if($sortIndex == 'regdate')
		{
			if($sortOrder == 'asc')
			{
				Context::set('regdate_sort_order', 'desc');
			}
			else
			{
				Context::set('regdate_sort_order', 'asc');
			}
		}
		else
		{
			Context::set('regdate_sort_order', 'asc');
		}

		if($sortIndex == 'last_login')
		{
			if($sortOrder == 'asc')
			{
				Context::set('last_login_sort_order', 'desc');
			}
			else
			{
				Context::set('last_login_sort_order', 'asc');
			}
		}
		else
		{
			Context::set('last_login_sort_order', 'desc');
		}

		// get list
		$output = $this->getList();

		// combine group info
		$oMemberModel = getModel('member');
		if($output->data)
		{
			foreach($output->data as $key => $member)
			{
				$output->data[$key]->group_list = $oMemberModel->getMemberGroups($member->member_srl, 0);
			}
		}

		Context::set('driverInfo', $this->getDriverInfo());
		Context::set('total_count', $output->total_count);
		Context::set('total_page', $output->total_page);
		Context::set('page', $output->page);
		Context::set('member_list', $output->data);
		Context::set('page_navigation', $output->page_navigation);

		$security = new Security();
		$security->encodeHtml('member_list..nick_name', 'member_list..group_list..');

		return parent::getListTpl();
	}

	/**
	 * @brief Get memberInfo tpl
	 * @access public
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getInfoTpl()
	{
		$memberSrl = Context::get('member_srl');
		$oMemberVo = $this->getMemberVo($memberSrl);

		// get memberinfo
		$memberInfo = $oMemberVo->getMemberInfo();
		if (!is_array($memberInfo->group_list))
		{
			$memberInfo->group_list = array();
		}

		// make form info
		$defaultFormInfo = $this->getFormInfoDefault($memberInfo);
		$extendFormInfo = $this->getFormInfo($memberInfo);
		$signUpForm = array_merge($defaultFormInfo, $extendFormInfo);

		Context::set('signUpForm', $signUpForm);
		Context::set('memberInfo', $memberInfo);

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

		$oMemberVo = $this->getMemberVo($memberSrl);
		$memberInfo = $oMemberVo->getMemberInfo();

		// make form info
		$defaultFormInfo = $this->getFormInfoDefault($memberInfo);
		$extendFormInfo = $this->getFormInfo($memberInfo);
		$signUpForm = array_merge($defaultFormInfo, $extendFormInfo);

		Context::set('signUpForm', $signUpForm);
		return parent::getInsertTpl();
	}

	/**
	 * @brief get member signup form format(default)
	 * @access private
	 * @param $memberInfo (when modify member_info of modified target member)
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	private function getFormInfoDefault($memberInfo)
	{
		$formList = array('me2dayId', 'me2dayNickName', 'face');
		$langList = array('me2day_id', 'me2day_nick_name', 'me2day_face');
		$defaultForm = array();

		foreach($formList as $no => $formName)
		{
			$formTag = new stdClass();
			$formTag->title = Context::getLang($langList[$no]);
			$formTag->name = $formName;
			$formTag->isUse = TRUE;
			$formTag->isDefaultForm = TRUE;

			if($formName == 'face')
			{
				$formTag->value = $formTag->inputTag = sprintf('<img src="%s" alt="" />', $memberInfo->face);
			}
			else
			{
				$formTag->value = $formTag->inputTag = $memberInfo->{$formName};
			}

			$defaultForm[] = $formTag;
		}

		return $defaultForm;
	}

	/**
	 * @brief get member signup form format
	 * @access private
	 * @param $memberInfo (when modify member_info of modified target member)
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	private function getFormInfo($memberInfo = NULL)
	{
		$oMemberModel = getModel('member');
		$extendForms = $oMemberModel->getCombineJoinForm($memberInfo);

		$config = $this->getConfig();
		$formTags = array();

		if(!$config->signUpForm)
		{
			return $formTags;
		}

		foreach($config->signUpForm as $formSrl => $formInfo)
		{
			if(!$formInfo->isUse)
			{
				continue;
			}
			$extendForm = $extendForms[$formSrl];

			$formTag = new stdClass();
			$formTag->member_join_form_srl = $formSrl;
			$formTag->name = $formInfo->name;
			$formTag->title = $formInfo->title;
			$formTag->description = $formInfo->description;
			$formTag->required = $formInfo->required;
			$formTag->inputTag = $oMemberModel->getExtendsInputForm($extendForm);
			$formTag->value = $oMemberModel->getExtendsFormHtml($extendForm);
			$formTag->isUse = TRUE;
			$formTag->type = $formInfo->type;

			$formTags[] = $formTag;
		}

		return $formTags;
	}

	/**
	 * @breif get list
	 * @access private
	 * @return array
	 * @developer NHN (developers@xpressengine.com)
	 **/
	private function getList()
	{
		// make filter option
		$filter = Context::get('filter_type');
		switch($filter)
		{
			case 'super_admin':
				$args->is_admin = 'Y';
				break;
			case 'enable':
				$args->is_denied = 'N';
				break;
			case 'disable':
				$args->is_denied = 'Y';
				break;
		}

		// make search option
		$searchTarget = trim(Context::get('search_target'));
		$searchKeyword = trim(Context::get('search_keyword'));

		switch($searchTarget)
		{
			case 'me2day_id':
				$args->user_id = $searchKeyword;
				break;
			case 'me2day_nickname':
				$args->nick_name = $searchKeyword;
				break;
			case 'regdate':
				$args->regdate = preg_replace("/[^0-9]/", "", $searchKeyword);
				break;
			case 'regdate_more':
				$args->regdate_more = substr(preg_replace("/[^0-9]/", "", $searchKeyword) . '00000000000000', 0, 14);
				break;
			case 'regdate_less':
				$args->regdate_less = substr(preg_replace("/[^0-9]/", "", $searchKeyword) . '00000000000000', 0, 14);
				break;
			case 'last_login':
				$args->last_login = preg_replace("/[^0-9]/", "", $searchKeyword);
				break;
			case 'last_login_more':
				$args->last_login_more = substr(preg_replace("/[^0-9]/", "", $searchKeyword) . '00000000000000', 0, 14);
				break;
			case 'last_login_less':
				$args->last_login_less = substr(preg_replace("/[^0-9]/", "", $searchKeyword) . '00000000000000', 0, 14);
				break;
			case 'extra_vars':
				$args->extra_vars = $searchKeyword;
				break;
		}

		// make sort option
		$sortOrder = Context::get('sort_order');
		$sortIndex = Context::get('sort_index');
		$selectedGroupSrl = Context::get('selected_group_srl');

		if(!$sortIndex)
		{
			$sortIndex = 'list_order';
		}

		if(!$sortOrder)
		{
			$sortOrder = 'desc';
		}

		$args->sort_index = $sortIndex;
		$args->sort_order = $sortOrder;

		// select query id
		if($selectedGroupSrl)
		{
			$queryId = 'member.driver.me2day.getMemberListWithGroup';
		}
		else
		{
			$queryId = 'member.driver.me2day.getMemberList';
		}

		// set etc. option
		$args->page = Context::get('page');
		$args->list_count = 40;
		$args->page_count = 10;
		$output = executeQueryArray($queryId, $args);

		return $output;
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
		$config = $this->getConfig();
		if(!$config->applicationKey)
		{
			return new Object(-1, 'me2day_msg_no_application_key');
		}
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
		$oModule->setLayoutPath('./common/tpl/');
		$oModule->setLayoutFile('default_layout');

		$isUnregister = $_SESSION[self::SESSION_NAME]['IS_UNREGISTER'];
		unset($_SESSION[self::SESSION_NAME]['IS_UNREGISTER']);

		$mid = $_SESSION[self::SESSION_NAME]['mid'];
		$vid = $_SESSION[self::SESSION_NAME]['vid'];
		$documentSrl = $_SESSION[self::SESSION_NAME]['document_srl'];

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

			// if unregister?
			if($memberVo && $isUnregister)
			{
				// check id
				if($_SESSION[self::SESSION_NAME]['ME2DAY_ID'] != $userKey->userId)
				{
					$oMemberController->procMemberLogout();

					return new Object(-1, 'msg_invalid_request');
				}

				$this->oMe2dayApi->destroySession();
				$this->destroySession();

				// unregister
				$output = $oMemberController->deleteMember($memberVo->getMemberSrl(), 'me2day');
				if(!$output->toBool())
				{
					return $output;
				}

				$url = getNotEncodedUrl('', 'vid', $vid, 'mid', $mid, 'document_srl', $documentSrl);
				$oModule->setRedirectUrl($url);

				return new Object();
			}

			// get person information
			try
			{
				$person = $this->oMe2dayApi->getPerson();
			}
			catch(Exception $e)
			{
				return new Object(-1, $e->getMessage());
			}

			// if not exist, insert
			if(!$memberVo)
			{
				// if required extend form
				$isExtendForm = FALSE;
				$formInfos = $this->getFormInfo();
				foreach($formInfos as $formInfo)
				{
					if($formInfo->isUse && $formInfo->required)
					{
						$isExtendForm = TRUE;
					}
				}

				if($isExtendForm)
				{
					$url = getNotEncodedUrl('', 'vid', $vid, 'mid', $mid, 'document_srl', $documentSrl, 'act', 'dispMemberDriverInterface', 'driver', 'me2day', 'dact', 'dispSignUp');
					$oModule->setRedirectUrl($url);
					return new Object();
				}
				else
				{
					Context::setRequestMethod('POST');
					$oMemberController = getController('member');
					$output = $oMemberController->procMemberInsert();
					if(!$output->toBool())
					{
						return $output;
					}
				}
			}
			else
			{
				$memberSrl = $memberVo->getMemberSrl();

				// check change of nick name or face url
				if($person->nickname != $memberVo->getMe2dayNickName() || $person->face != $memberVo->getFace())
				{
					// update information
					$args = new stdClass();
					$args->member_srl = $memberSrl;
					$args->me2dayNickName = $person->nickname;
					$args->face = $person->face;
					$output = $this->updateMember($args);
					if(!$output->toBool())
					{
						return $output;
					}
				}

				// signin
				$output = $oMemberController->doSignin('me2day', $memberSrl);
				if(!$output->toBool())
				{
					return $output;
				}
			}

		}


		$url = getNotEncodedUrl('', 'vid', $vid, 'mid', $mid, 'document_srl', $documentSrl);
		$oModule->setRedirectUrl($url);

		return new Object();
	}

	/**
	 * @breif display extend signup form
	 * @access public
	 * @param $oModule MemberView
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function dispSignUp($oModule)
	{
		$oMemberModel = getModel('member');

		// Get the member information if logged-in
		if($oMemberModel->isLogged())
		{
			return $this->stop('msg_already_logged');
		}

		$driver = Context::get('driver');
		$config = $oMemberModel->getMemberConfig();
		Context::set('config', $config);

		// check driver
		if(!in_array($driver, $config->usedDriver))
		{
			return new Object(-1, 'msg_invalid_request');
		}

		// get diriver form tag
		$formTags = $this->getSignupFormInfo();

		Context::set('formTags', $formTags);
		Context::set('oDriver', $this);

		$oModule->setTemplateFile('signup_form');
		return new Object();
	}

	/**
	 * @breif display unregister
	 * @access public
	 * @param $oModule MemberView
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function dispUnregister($oModule)
	{
		if(!Context::get('is_logged'))
		{
			return new Object(-1, 'msg_not_logged');
		}

		$innerTpl = $this->getTpl('unregister');
		Context::set('innerTpl', $innerTpl);

		$oModule->setTemplateFile('member_info_inner');
		return new Object();
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
		$output = $oModuleController->updateDriverConfig('member', 'me2day', $config);
		if(!$output->toBool())
		{
			return $output;
		}

		$oModule->setRedirectUrl(getNotEncodedUrl('', 'module', 'admin', 'act', 'dispMemberAdminDriverConfig', 'driver', 'me2day'));

		return new Object();
	}

	/**
	 * @breif save signup form
	 * @access public
	 * @param $oModule MemberAdminController
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function procSaveSignUpForm($oModule)
	{
		$listOrder = Context::get('list_order');
		$allArgs = Context::getRequestVars();

		if(!is_array($listOrder))
		{
			$listOrder = array($listOrder);
		}

		// get extend form info
		$extendFormInfo = $this->getSignUpFormInfoFromDB();

		$oDB = DB::getInstance();
		$oDB->begin();

		// make SignUpForm info
		$signUpForm = array();
		foreach($listOrder as $formSrl)
		{
			$formInfo = $extendFormInfo[$formSrl];

			// make config
			$item = new stdClass();
			$item->member_join_form_srl = $formSrl;
			$item->name = $formInfo->name;
			$item->title = $formInfo->title;
			$item->type = $formInfo->type;
			$item->required = ($allArgs->{'required_' . $formSrl} == 'Y');
			$item->isUse = ($allArgs->{'is_use_' . $formSrl} == 'Y');
			$item->description = $formInfo->description;

			$signUpForm[$formSrl] = $item;

			// update db
			$args->member_join_form_srl = $formSrl;
			$args->is_active = $item->isUse ? 'Y' : 'N';
			$args->required = $item->required ? 'Y' : 'N';

			$output = executeQuery('member.updateJoinForm', $args);
			if(!$output->toBool())
			{
				$oDB->rollback();
				return $output;
			}
		}

		// update config
		$oModuleController = getController('module');
		$config->signUpForm = $signUpForm;
		$output = $oModuleController->updateDriverConfig('member', 'me2day', $config);
		if(!$output->toBool())
		{
			$oDB->rollback();
			return $output;
		}

		$oDB->commit();
		$this->createRulesets();

		$oModule->setRedirectUrl(getNotEncodedUrl('', 'module', 'admin', 'act', 'dispMemberAdminDriverConfig', 'driver', 'me2day'));

		return new Object();
	}


	/**
	 * @breif refresh info
	 * @access public
	 * @param $oModule MemberController
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function procRefreshInfo($oModule)
	{
		// get person information
		try
		{
			$person = $this->oMe2dayApi->getPerson();
		}
		catch(Exception $e)
		{
			return new Object(-1, $e->getMessage());
		}

		$loggedInfo = Context::get('logged_info');
		$oMemberVo = $this->getMemberVo($loggedInfo->member_srl);

		// check change of nick name or face url
		if($person->nickname != $oMemberVo->getMe2dayNickName() || $person->face != $oMemberVo->getFace())
		{
			// update information
			$args = new stdClass();
			$args->member_srl = $loggedInfo->member_srl;
			$args->me2dayNickName = $person->nickname;
			$args->face = $person->face;
			$output = $this->updateMember($args);
			if(!$output->toBool())
			{
				return $output;
			}
		}

		return new Object();
	}

	/**
	 * @breif unregister step 1
	 * @access public
	 * @param $oModule MemberController
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function procUnregister($oModule)
	{
		$loggedInfo = Context::get('logged_info');
		if(!$loggedInfo)
		{
			return new Object(-1, 'msg_invalid_request');
		}

		// api session destory...
		$this->oMe2dayApi->destroySession();

		// mark unregister...
		$_SESSION[self::SESSION_NAME]['IS_UNREGISTER'] = TRUE;
		$_SESSION[self::SESSION_NAME]['ME2DAY_ID'] = $loggedInfo->me2dayId;

		// redirect auth page
		try
		{
			$this->oMe2dayApi->doLogin();
		}
		catch(Exception $e)
		{
			$this->destroySession();
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
	 * @brief Get driver config
	 * @access public
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getConfig()
	{
		$oModuleModel = getModel('module');
		$config = $oModuleModel->getDriverConfig('member', 'me2day');
		$this->makeSignUpFormConfig($config);

		return $config;
	}

	/**
	 * @breif make signup form config
	 * @access private
	 * @param $oModule MemberAdminController
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	private function makeSignUpFormConfig(&$config)
	{
		$infoFromDB = $this->getSignUpFormInfoFromDB();
		$infoFromConfig = $config->signUpForm;
		if(!is_array($infoFromDB))
		{
			$infoFromDB = array();
		}
		if(!is_array($infoFromConfig))
		{
			$infoFromConfig = array();
		}

		// search different
		$deleteTarget = array_diff_key($infoFromConfig, $infoFromDB);
		foreach($deleteTarget as $key => $val)
		{
			unset($config->signUpForm[$key]);
		}

		$insertTarget = array_diff_key($infoFromDB, $infoFromConfig);
		foreach($insertTarget as $key => $val)
		{
			$config->signUpForm[$key] = $infoFromDB[$key];
		}
	}

	/**
	 * @brief get signup form info form db
	 * @access private
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getSignUpFormInfoFromDB()
	{
		// get extend item
		$oMemberModel = getModel('member');
		$extendItems = $oMemberModel->getJoinFormListByDriver('me2day');
		if(!$extendItems)
		{
			return NULL;
		}

		$signUpForm = array();
		foreach($extendItems as $formSrl => $itemInfo)
		{
			$item = new stdClass();
			$item->name = $itemInfo->column_name;
			$item->title = $itemInfo->column_title;
			$item->type = $itemInfo->column_type;
			$item->member_join_form_srl = $formSrl;
			$item->required = ($itemInfo->required == 'Y');
			$item->isUse = ($itemInfo->is_active == 'Y');
			$item->description = $itemInfo->description;
			$signUpForm[$item->member_join_form_srl] = $item;
		}

		return $signUpForm;
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
