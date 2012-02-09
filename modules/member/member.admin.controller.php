<?php
    /**
     * @class  memberAdminController
     * @author NHN (developers@xpressengine.com)
     * @brief member module of the admin controller class
     **/

    class memberAdminController extends member {

        /**
         * @brief Initialization
         **/
        function init() {
        }

        /**
         * @brief Add a user (Administrator)
         **/
        function procMemberAdminInsert()
		{
			if(Context::getRequestMethod() == 'GET')
			{
				return new Object(-1, 'msg_invalid_request');
			}

			$driver = Context::get('driver');
			if(empty($driver))
			{
				return new Object(-1, 'msg_invalid_request');
			}

			$args = Context::getRequestVars();

			$oDriver = getDriver('member', $driver);
			$output = $oDriver->isValidateAdminInsert($args);

			if(!$output->toBool())
			{
				return $output;
			}

			$memberSrl = Context::get('member_srl');
			$args = Context::getRequestVars();

			$oMemberController = getController('member');

			if($memberSrl)
			{
				$output = $oMemberController->updateMember($args, $driver);
			}
			else
			{
				$output = $oMemberController->insertMember($args, FALSE, $driver);
			}

			if(!$output->toBool())
			{
				return $output;
			}

			if(!in_array(Context::getRequestMethod(),array('XMLRPC','JSON')))
			{
				$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act', 'dispMemberAdminList');
				$this->setRedirectUrl($returnUrl);
				return;
			}
        }

        /**
         * @brief Delete a user (Administrator)
         **/
        function procMemberAdminDelete() {
            // Separate all the values into DB entries and others
            $member_srl = Context::get('member_srl');

            $oMemberController = getController('member');
            $output = $oMemberController->deleteMember($member_srl);
            if(!$output->toBool()) return $output;

            $this->add('page',Context::get('page'));
            $this->setMessage("success_deleted");
        }

		function procMemberAdminInsertConfig(){
            $input_args = Context::gets(
				'enable_join',
				'webmaster_name',
				'webmaster_email',
				'limit_day',
				'after_login_url',
				'after_logout_url',
				'redirect_url',
				'skin',
				'colorset'
            );

			$oModuleController = getController('module');

			// default setting start
            if($input_args->enable_join != 'Y')
			{
				$args->enable_join = 'N';
			}
			else
			{
				$args = $input_args;
				$args->enable_join = 'Y';
				$args->limit_day = (int)$args->limit_day;
				if(!trim(strip_tags($args->after_login_url)))
				{
					$args->after_login_url = null;
				}
				if(!trim(strip_tags($args->after_logout_url)))
				{
					$args->after_logout_url = null;
				}
				if(!trim(strip_tags($args->redirect_url)))
				{
					$args->redirect_url = null;
				}

				if(!$args->skin)
				{
					$args->skin = "default";
				}
				if(!$args->colorset)
				{
					$args->colorset = "white";
				}
			}
			$output = $oModuleController->updateModuleConfig('member', $args);
			// default setting end

 			if($output->toBool() && !in_array(Context::getRequestMethod(), array('XMLRPC','JSON')))
			{
				$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act', 'dispMemberAdminConfig');
				$this->setRedirectUrl($returnUrl);
				return;
 			}
		}

        /**
         * @brief Add a user group
         **/
        function procMemberAdminInsertGroup() {
            $args = Context::gets('title','description','is_default','image_mark');
            $output = $this->insertGroup($args);
            if(!$output->toBool()) return $output;

            $this->add('group_srl','');
            $this->add('page',Context::get('page'));
            $this->setMessage('success_registed');

			if(!in_array(Context::getRequestMethod(),array('XMLRPC','JSON'))) {
				$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act', 'dispMemberAdminGroupList');
				header('location:'.$returnUrl);
				return;
			}
        }

        /**
         * @brief Update user group information
         **/
        function procMemberAdminUpdateGroup() {
            $group_srl = Context::get('group_srl');

			$args = Context::gets('group_srl','title','description','is_default','image_mark');
			$args->site_srl = 0;
			$output = $this->updateGroup($args);
			if(!$output->toBool()) return $output;

            $this->add('group_srl','');
            $this->add('page',Context::get('page'));
            $this->setMessage('success_updated');

			if(!in_array(Context::getRequestMethod(),array('XMLRPC','JSON'))) {
				$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act', 'dispMemberAdminGroupList');
				header('location:'.$returnUrl);
				return;
			}
        }

        /**
         * @brief Update user group information
         **/
        function procMemberAdminDeleteGroup() {
            $group_srl = Context::get('group_srl');

			$output = $this->deleteGroup($group_srl);
			if(!$output->toBool()) return $output;

            $this->add('group_srl','');
            $this->add('page',Context::get('page'));
            $this->setMessage('success_deleted');

			if(!in_array(Context::getRequestMethod(),array('XMLRPC','JSON'))) {
				$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act', 'dispMemberAdminGroupList');
				header('location:'.$returnUrl);
				return;
			}
        }

        /**
         * @brief Add a join form
         **/
        function procMemberAdminInsertJoinForm() {
			$driver = $args->driver = Context::get('driver');
            $args->member_join_form_srl = Context::get('member_join_form_srl');
            $args->column_type = Context::get('column_type');
            $args->column_name = strtolower(Context::get('column_name'));
            $args->column_title = Context::get('column_title');
            $args->default_value = explode("\n", str_replace("\r", '', Context::get('default_value')));
            $args->required = Context::get('required');
			$args->is_active = Context::get('is_active') == 'Y' ? 'Y' : 'N';


            if(!in_array(strtoupper($args->required), array('Y','N')))
			{
				$args->required = 'N';
			}
            $args->description = Context::get('description');

            // Default values
            if(in_array($args->column_type, array('checkbox','select','radio')) && count($args->default_value) )
			{
                $args->default_value = serialize($args->default_value);
            }
			else
			{
                $args->default_value = '';
            }

			$oDB = DB::getInstance();
			$oDB->begin();

			// Check duplicate
			$output = executeQuery('member.getJoinFormByNameDriver', $args, array('member_join_form_srl'));
			if($output->data)
			{
				if(!$args->member_join_form_srl || ($output->data->member_join_form_srl != $args->member_join_form_srl))
				{
					return $this->stop('msg_already_inserted_item');
				}
			}

            // Fix if member_join_form_srl exists. Add if not exists.
            $isInsert = FALSE;
			if(!$args->member_join_form_srl)
			{
				$isInsert = TRUE;
				$args->list_order = $args->member_join_form_srl = getNextSequence();
                $output = executeQuery('member.insertJoinForm', $args);
            }
			else
			{
                $output = executeQuery('member.updateJoinForm', $args);
            }

            if(!$output->toBool())
			{
				return $output;
			}

			$oDriver = getDriver('member', $driver);
			if(!$oDriver)
			{
				$oDB->rollback();
				return $this->stop('msg_invalid_request');
			}
			$output = $oDriver->afterInsertJoinForm($args, $isInsert);

			if(!$output->toBool())
			{
				$oDB->rollback();
				return $output;
			}

			$this->add('member_join_form_srl', $args->member_join_form_srl);
			$this->add('is_insert', $isInsert);
            $this->setMessage('success_registed');

			if(!in_array(Context::getRequestMethod(),array('XMLRPC','JSON')))
			{
				$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act', 'dispMemberAdminJoinFormList');
				$this->setRedirectUrl($returnUrl);
				return;
			}
        }

		function procMemberAdminDeleteJoinForm()
		{
            $member_join_form_srl = Context::get('member_join_form_srl');
			$driver = Context::get('driver');

			if(!$member_join_form_srl || !$driver)
			{
				return $this->stop('msg_invalid_request');
			}

			$oDB = DB::getInstance();
			$oDB->begin();

            $args->member_join_form_srl = $member_join_form_srl;
			$args->driver = $driver;
            $output = executeQuery('member.deleteJoinForm', $args);

            if(!$output->toBool())
			{
				return $output;
			}

			$oDriver = getDriver('member', $driver);
			if(!$oDriver)
			{
				return $this->stop('msg_invalid_request');
			}

			$output = $oDriver->afterDeleteJoinForm($args);

			if(!$output->toBool())
			{
				$oDB->rollback();
				return $output;
			}

		}

        /**
         * @brief Move up/down the member join form and modify it
         **/
        function procMemberAdminUpdateJoinForm() {
            $member_join_form_srl = Context::get('member_join_form_srl');
            $mode = Context::get('mode');

            switch($mode) {
                case 'up' :
                        $output = $this->moveJoinFormUp($member_join_form_srl);
                        $msg_code = 'success_moved';
                    break;
                case 'down' :
                        $output = $this->moveJoinFormDown($member_join_form_srl);
                        $msg_code = 'success_moved';
                    break;
                case 'delete' :
                        $output = $this->deleteJoinForm($member_join_form_srl);
                        $msg_code = 'success_deleted';
                    break;
                case 'update' :
                    break;
            }
            if(!$output->toBool()) return $output;

            $this->setMessage($msg_code);
        }

		/**
		 * selected member manager layer in dispAdminList
		 **/
		function procMemberAdminSelectedMemberManage(){
			$var = Context::getRequestVars();
			$groups = $var->groups;
			$members = $var->member_srls;
			$driver = Context::get('driver');

			$oDB = DB::getInstance();
			$oDB->begin();

			$oMemberController = getController('member');
			foreach($members as $key=>$member_srl){
				unset($args);
				$args->member_srl = $member_srl;
				switch($var->type){
					case 'modify':{
									  if (count($groups) > 0){
										  $args->site_srl = 0;
										  // One of its members to delete all the group
										  $output = executeQuery('member.deleteMemberGroupMember', $args);
										  if(!$output->toBool()) {
											  $oDB->rollback();
											  return $output;
										  }
										  // Enter one of the loop a
										  foreach($groups as $group_srl) {
											  $output = $oMemberController->addMemberToGroup($args->member_srl,$group_srl);
											  if(!$output->toBool()) {
												  $oDB->rollback();
												  return $output;
											  }
										  }
									  }
									  if ($var->denied){
										  $args->denied = $var->denied;
										  $output = executeQuery('member.updateMemberDeniedInfo', $args);
										  if(!$output->toBool()) {
											  $oDB->rollback();
											  return $output;
										  }
									  }
									  break;
								  }
					case 'delete':{
									  $oMemberController->memberInfo = null;
									  $output = $oMemberController->deleteMember($member_srl, $driver);
									  if(!$output->toBool()) {
										  $oDB->rollback();
										  return $output;
									  }
								  }
				}
			}

			$message = $var->message;
			// Send a message
			if($message) {
				$oCommunicationController = getController('communication');

				$logged_info = Context::get('logged_info');
				$title = cut_str($message,10,'...');
				$sender_member_srl = $logged_info->member_srl;

				foreach($members as $member_srl){
					$oCommunicationController->sendMessage($sender_member_srl, $member_srl, $title, $message, false);
				}
			}

			if(!in_array(Context::getRequestMethod(),array('XMLRPC','JSON'))) {
				$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act', 'dispMemberAdminList');
				$this->setRedirectUrl($returnUrl);
				return;
			}
		}

        /**
         * @brief Delete the selected members
         */
        function procMemberAdminDeleteMembers() {
            $target_member_srls = Context::get('target_member_srls');
            if(!$target_member_srls) return new Object(-1, 'msg_invalid_request');
            $member_srls = explode(',', $target_member_srls);
            $oMemberController = getController('member');

            foreach($member_srls as $member) {
                $output = $oMemberController->deleteMember($member);
                if(!$output->toBool()) {
                    $this->setMessage('failed_deleted');
                    return $output;
                }
            }

            $this->setMessage('success_deleted');
        }

        /**
         * @brief Update a group of selected memebrs
         **/
        function procMemberAdminUpdateMembersGroup() {
            $member_srl = Context::get('member_srl');
            if(!$member_srl) return new Object(-1,'msg_invalid_request');
            $member_srls = explode(',',$member_srl);

            $group_srl = Context::get('group_srls');
            if(!is_array($group_srl)) $group_srls = explode('|@|', $group_srl);
			else $group_srls = $group_srl;

            $oDB = DB::getInstance();
            $oDB->begin();
            // Delete a group of selected members
            $args->member_srl = $member_srl;
            $output = executeQuery('member.deleteMembersGroup', $args);
            if(!$output->toBool()) {
                $oDB->rollback();
                return $output;
            }
            // Add to a selected group
            $group_count = count($group_srls);
            $member_count = count($member_srls);
            for($j=0;$j<$group_count;$j++) {
                $group_srl = (int)trim($group_srls[$j]);
                if(!$group_srl) continue;
                for($i=0;$i<$member_count;$i++) {
                    $member_srl = (int)trim($member_srls[$i]);
                    if(!$member_srl) continue;

                    $args = null;
                    $args->member_srl = $member_srl;
                    $args->group_srl = $group_srl;

                    $output = executeQuery('member.addMemberToGroup', $args);
                    if(!$output->toBool()) {
                        $oDB->rollback();
                        return $output;
                    }
                }
            }
            $oDB->commit();
            $this->setMessage('success_updated');

			if(!in_array(Context::getRequestMethod(),array('XMLRPC','JSON'))) {
				global $lang;
				htmlHeader();
				alertScript($lang->success_updated);
				reload(true);
				closePopupScript();
				htmlFooter();
				Context::close();
				exit;
			}
        }

        /**
         * @brief Add an administrator
         **/
        function insertAdmin($args) {
            // Assign an administrator
            $args->is_admin = 'Y';
            // Get admin group and set
            $oMemberModel = getModel('member');
            $admin_group = $oMemberModel->getAdminGroup();
            $args->group_srl_list = $admin_group->group_srl;

            $oMemberController = getController('member');
            return $oMemberController->insertMember($args);
        }

        /**
         * @brief Change the group values of member
         **/
        function changeGroup($source_group_srl, $target_group_srl) {
            $args->source_group_srl = $source_group_srl;
            $args->target_group_srl = $target_group_srl;

            return executeQuery('member.changeGroup', $args);
        }

        /**
         * @brief find_account_answerInsert a group
         **/
        function insertGroup($args) {
            if(!$args->site_srl) $args->site_srl = 0;
            // Check the value of is_default.
            if($args->is_default!='Y') {
				$args->is_default = 'N';
			} else {
				 $output = executeQuery('member.updateGroupDefaultClear', $args);
				 if(!$output->toBool()) return $output;
			}

			if (!$args->group_srl) $args->group_srl = getNextSequence();
            return executeQuery('member.insertGroup', $args);
        }

        /**
         * @brief Modify Group Information
         **/
        function updateGroup($args) {
            // Check the value of is_default.
			if(!$args->group_srl) return new Object(-1, 'lang->msg_not_founded');
            if($args->is_default!='Y') {
				$args->is_default = 'N';
			} else {
				 $output = executeQuery('member.updateGroupDefaultClear', $args);
				 if(!$output->toBool()) return $output;
			}

            return executeQuery('member.updateGroup', $args);
        }

        /**
         * Delete a Group
         **/
        function deleteGroup($group_srl, $site_srl = 0) {
            // Create a member model object
            $oMemberModel = getModel('member');
            // Check the group_srl (If is_default == 'Y', it cannot be deleted)
			$columnList = array('group_srl', 'is_default');
            $group_info = $oMemberModel->getGroup($group_srl, $columnList);

            if(!$group_info) return new Object(-1, 'lang->msg_not_founded');
            if($group_info->is_default == 'Y') return new Object(-1, 'msg_not_delete_default');
            // Get groups where is_default == 'Y'
			$columnList = array('site_srl', 'group_srl');
            $default_group = $oMemberModel->getDefaultGroup($site_srl, $columnList);
            $default_group_srl = $default_group->group_srl;
            // Change to default_group_srl
            $this->changeGroup($group_srl, $default_group_srl);

            $args->group_srl = $group_srl;
            return executeQuery('member.deleteGroup', $args);
        }

        /**
         * Set group config
         **/
		function procMemberAdminGroupConfig() {
			$vars = Context::getRequestVars();

			$oMemberModel = getModel('member');
			$oModuleController = getController('module');

			// group image mark option
			$config = $oMemberModel->getMemberConfig();
			$config->group_image_mark = $vars->group_image_mark;
			$output = $oModuleController->updateModuleConfig('member', $config);

			// group data save
			$group_srls = $vars->group_srls;
			foreach($group_srls as $order=>$group_srl){
				unset($update_args);
				$update_args->title = $vars->group_titles[$order];
				$update_args->is_default = ($vars->defaultGroup == $group_srl)?'Y':'N';
				$update_args->description = $vars->descriptions[$order];
				$update_args->image_mark = $vars->image_marks[$order];
				$update_args->list_order = $order + 1;

				if (is_numeric($group_srl)){
					$update_args->group_srl = $group_srl;
					$output = $this->updateGroup($update_args);
				}else
					$output = $this->insertGroup($update_args);
			}

			if(!in_array(Context::getRequestMethod(),array('XMLRPC','JSON'))) {
				$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act', 'dispMemberAdminGroupList');
				$this->setRedirectUrl($returnUrl);
				return;
			}
		}

        function procMemberAdminUpdateGroupOrder() {
			$vars = Context::getRequestVars();

			foreach($vars->group_srls as $key => $val){
				$args->group_srl = $val;
				$args->list_order = $key + 1;
				executeQuery('member.updateMemberGroupListOrder', $args);
			}

			header(sprintf('Location:%s', getNotEncodedUrl('', 'module', 'admin', 'act', 'dispMemberAdminGroupList')));
        }

        /**
         * @brief Delete a join form
		 * @deprecated
         **/
        function deleteJoinForm($member_join_form_srl) {
            $args->member_join_form_srl = $member_join_form_srl;
            $output = executeQuery('member.deleteJoinForm', $args);
            return $output;
        }

        /**
         * @brief Move up a join form
         **/
        function moveJoinFormUp($member_join_form_srl) {
            $oMemberModel = getModel('member');
            // Get information of the join form
            $args->member_join_form_srl = $member_join_form_srl;
            $output = executeQuery('member.getJoinForm', $args);

            $join_form = $output->data;
            $list_order = $join_form->list_order;
            // Get a list of all join forms
            $join_form_list = $oMemberModel->getJoinFormList();
            $join_form_srl_list = array_keys($join_form_list);
            if(count($join_form_srl_list)<2) return new Object();

            $prev_member_join_form = NULL;
            foreach($join_form_list as $key => $val) {
                if($val->member_join_form_srl == $member_join_form_srl) break;
                $prev_member_join_form = $val;
            }
            // Return if no previous join form exists
            if(!$prev_member_join_form) return new Object();
            // Information of the join form
            $cur_args->member_join_form_srl = $member_join_form_srl;
            $cur_args->list_order = $prev_member_join_form->list_order;
            // Information of the target join form
            $prev_args->member_join_form_srl = $prev_member_join_form->member_join_form_srl;
            $prev_args->list_order = $list_order;
            // Execute Query
            $output = executeQuery('member.updateMemberJoinFormListorder', $cur_args);
            if(!$output->toBool()) return $output;

            executeQuery('member.updateMemberJoinFormListorder', $prev_args);
            if(!$output->toBool()) return $output;

            return new Object();
        }

        /**
         * @brief Move down a join form
         **/
        function moveJoinFormDown($member_join_form_srl) {
            $oMemberModel = getModel('member');
            // Get information of the join form
            $args->member_join_form_srl = $member_join_form_srl;
            $output = executeQuery('member.getJoinForm', $args);

            $join_form = $output->data;
            $list_order = $join_form->list_order;
            // Get information of all join forms
            $join_form_list = $oMemberModel->getJoinFormList();
            $join_form_srl_list = array_keys($join_form_list);
            if(count($join_form_srl_list)<2) return new Object();

            for($i=0;$i<count($join_form_srl_list);$i++) {
                if($join_form_srl_list[$i]==$member_join_form_srl) break;
            }

            $next_member_join_form_srl = $join_form_srl_list[$i+1];
            // Return if no previous join form exists
            if(!$next_member_join_form_srl) return new Object();
            $next_member_join_form = $join_form_list[$next_member_join_form_srl];
            // Information of the join form
            $cur_args->member_join_form_srl = $member_join_form_srl;
            $cur_args->list_order = $next_member_join_form->list_order;
            // Information of the target join form
            $next_args->member_join_form_srl = $next_member_join_form->member_join_form_srl;
            $next_args->list_order = $list_order;
            // Execute Query
            $output = executeQuery('member.updateMemberJoinFormListorder', $cur_args);
            if(!$output->toBool()) return $output;

            $output = executeQuery('member.updateMemberJoinFormListorder', $next_args);
            if(!$output->toBool()) return $output;

            return new Object();
        }

		/**
		 * @brief Interface of driver process
		 * @access public
		 * @return void
		 * @developer NHN (developers@xpressengine.com)
		 */
		public function procMemberAdminDriverInterface()
		{
			return $this->driverInterface();
		}

		/**
		 * @brief save signin form
		 * @access public
		 * @return void
		 * @developer NHN (developers@xpressengine.com)
		 */
		public function procMemberAdminSaveSigninConfig()
		{
			$siginConfig = Context::get('signinConfig');
			$useDriver = array();

			$xmlParser = new XmlParser();
			$xml = $xmlParser->parse($siginConfig);

			$config->signinConfig = array();

			$items = $xml->items->item;
			if(!is_array($items))
			{
				$items = array($items);
			}

			foreach($items as $value)
			{
				$item = new stdClass();
				if($value->attrs->name == 'horizontal')
				{
					$item->name = 'horizontal';
					$item->items = array();
					$childItems = $value->item;
					if(!is_array($childItems))
					{
						$childItems = array($childItems);
					}
					foreach($childItems as $value2)
					{
						$useDriver[] = $value2->attrs->name;
						$item->items[] = $value2->attrs;
					}
				}
				else
				{
					$useDriver[] = $value->attrs->name;
					$item = $value->attrs;
				}
				$config->signinConfig[] = $item;
			}

			$config->usedDriver = array_unique($useDriver);

			$oModuleController = getController('module');
			$oModuleController->updateModuleConfig('member', $config);

			if(!in_array(Context::getRequestMethod(),array('XMLRPC','JSON')))
			{
				$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act', 'dispMemberAdminSigninConfig');
				header('location:' . $returnUrl);
				return;
			}
		}
    }
?>
