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
         **/
        function dispMemberInfo() {
            $oMemberModel = &getModel('member');
            $logged_info = Context::get('logged_info');
            // Don't display member info to non-logged user
            if(!$logged_info->member_srl) return $this->stop('msg_not_permitted');

            $member_srl = Context::get('member_srl');
            if(!$member_srl && Context::get('is_logged')) {
                $member_srl = $logged_info->member_srl;
            } elseif(!$member_srl) {
                return $this->dispMemberSignUpForm();
            }

            $oModuleModel = &getModel('module');
            $member_config = $oModuleModel->getModuleConfig('member');
            Context::set('member_config', $member_config);

			$memberInfo = get_object_vars(Context::get('member_info'));
			Context::set('memberInfo', $memberInfo);
            $site_module_info = Context::get('site_module_info');
			$columnList = array('member_srl', 'user_name', 'nick_name', 'homepage', 'blog', 'birthday', 'regdate', 'last_login');
            $member_info = $oMemberModel->getMemberInfoByMemberSrl($member_srl, $site_module_info->site_srl, $columnList);
            unset($member_info->password);
            unset($member_info->email_id);
            unset($member_info->email_host);

            if(!$member_info->member_srl) return $this->dispMemberSignUpForm();

            Context::set('memberInfo', get_object_vars($member_info));

			$extendForm = $oMemberModel->getCombineJoinForm($member_info);
            unset($extendForm->find_member_account);
            unset($extendForm->find_member_answer);
            Context::set('extend_form_list', $extendForm);
            if ($member_info->member_srl == $logged_info->member_srl)
                Context::set('openids', $oMemberModel->getMemberOpenIDByMemberSrl($member_srl));

            $this->setTemplateFile('member_info');
        }

        /**
         * @brief Display member join form
         **/
        function dispMemberSignUpForm() {
            $oMemberModel = &getModel('member');
            // Get the member information if logged-in
            if($oMemberModel->isLogged()) return $this->stop('msg_already_logged');
            // call a trigger (before) 
            $trigger_output = ModuleHandler::triggerCall('member.dispMemberSignUpForm', 'before', $this->member_config);
            if(!$trigger_output->toBool()) return $trigger_output;
            // Error appears if the member is not allowed to join
            if($this->member_config->enable_join != 'Y') return $this->stop('msg_signup_disabled');

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
            $this->setTemplateFile('signup_form');
        }

        /**
         * @brief Modify member information
         **/
        function dispMemberModifyInfo() {
            $oMemberModel = &getModel('member');
            $oModuleModel = &getModel('module');
            $memberModuleConfig = $oModuleModel->getModuleConfig('member');
            // A message appears if the user is not logged-in
            if(!$oMemberModel->isLogged()) return $this->stop('msg_not_logged');

            $logged_info = Context::get('logged_info');
            $member_srl = $logged_info->member_srl;

			$columnList = array('member_srl', 'user_id', 'user_name', 'nick_name', 'email_address', 'find_account_answer', 'homepage', 'blog', 'birthday', 'allow_mailing');
            $member_info = $oMemberModel->getMemberInfoByMemberSrl($member_srl, 0, $columnList);
            $member_info->signature = $oMemberModel->getSignature($member_srl);
            Context::set('member_info',$member_info);
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
			if(!$member_config->identifier) $member_config->identifier = 'user_id';
			$identifierForm->title = $lang->{$member_config->identifier};
			$identifierForm->name = $member_config->identifier;
			$identifierForm->value = $member_info->{$member_config->identifier};
			Context::set('identifierForm', $identifierForm);
            // Set a template file
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
        function dispMemberLoginForm() {
            if(Context::get('is_logged')) {
                Context::set('redirect_url', getUrl('act',''));
                $this->setTemplatePath($this->module_path.'tpl');
                $this->setTemplateFile('redirect.html');
                return;
            }
            // Set a template file
            Context::set('referer_url', $_SERVER['HTTP_REFERER']);
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
        function dispMemberLeave() {
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
            $oMemberController->procMemberLogout();

            Context::set('layout','none');
            $this->setTemplatePath($this->module_path.'/tpl');
            $this->setTemplateFile('logout');
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
    }
?>
