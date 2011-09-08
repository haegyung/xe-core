<?php
    /**
     * @class  member
     * @author NHN (developers@xpressengine.com)
     * @brief high class of the member module
     **/
    class member extends ModuleObject {

        /**
         * @brief constructor
         **/
        function member() {
            if(!Context::isInstalled()) return;

            $oModuleModel = &getModel('module');
            $member_config = $oModuleModel->getModuleConfig('member');
            // Set to use SSL upon actions related member join/information/password and so on
            if(Context::get('_use_ssl') == 'optional') {
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
         **/
        function moduleInstall() {
            // Register action forward (to use in administrator mode)
            $oModuleController = &getController('module');

            $oDB = &DB::getInstance();
            $oDB->addIndex("member_group","idx_site_title", array("site_srl","title"),true);

            $oModuleModel = &getModel('module');
            $args = $oModuleModel->getModuleConfig('member');
            // Set the basic information
            $args->enable_join = 'Y';
            if(!$args->enable_openid) $args->enable_openid = 'N';
            if(!$args->enable_auth_mail) $args->enable_auth_mail = 'N';
            if(!$args->image_name) $args->image_name = 'Y';
            if(!$args->image_mark) $args->image_mark = 'Y';
            if(!$args->profile_image) $args->profile_image = 'Y';
            if(!$args->image_name_max_width) $args->image_name_max_width = '90';
            if(!$args->image_name_max_height) $args->image_name_max_height = '20';
            if(!$args->image_mark_max_width) $args->image_mark_max_width = '20';
            if(!$args->image_mark_max_height) $args->image_mark_max_height = '20';
            if(!$args->profile_image_max_width) $args->profile_image_max_width = '80';
            if(!$args->profile_image_max_height) $args->profile_image_max_height = '80';
            if($args->group_image_mark!='Y') $args->group_image_mark = 'N';

			global $lang;
			$oMemberModel = &getModel('member');

			$extendItems = $oMemberModel->getJoinFormList();
			
			$identifier = 'email_address';
			$items = array('user_id', 'password', 'user_name', 'nick_name', 'email_address', 'find_account_question', 'homepage', 'blog', 'birthday', 'signature', 'profile_image', 'image_name', 'image_mark');
			$mustRequireds = array('email_address', 'nick_name','password', 'find_account_question');
			$orgRequireds = array('email_address', 'nick_name','password', 'find_account_question');
			$orgUse = array('email_address', 'password', 'find_account_question');
			$list_order = array();
			foreach($items as $key){
				unset($signupItem);
				$signupItem->isDefaultForm = true;
				$signupItem->name = $key;
				$signupItem->title = $lang->{$key};
				$signupItem->mustRequired = in_array($key, $mustRequireds);
				$signupItem->imageType = (strpos($key, 'image') !== false);
				$signupItem->required = in_array($key, $orgRequireds);
				$signupItem->isUse = ($config->{$key} == 'Y') || in_array($key, $orgUse);
				$signupItem->isIdentifier = ($key == $identifier);
				if ($signupItem->imageType){
					$signupItem->max_width = $config->{$key.'_max_width'};
					$signupItem->max_height = $config->{$key.'_max_height'};
				}
				$list_order[] = $signupItem;
			}
			if (is_array($extendItems)){
				foreach($extendItems as $form_srl=>$item_info){
					unset($signupItem);
					$signupItem->name = $item_info->column_name;
					$signupItem->title = $item_info->column_title;
					$signupItem->type = $item_info->column_type;
					$signupItem->member_join_form_srl = $form_srl;
					$signupItem->mustRequired = in_array($key, $mustRequireds);
					$signupItem->required = ($item_info->required == 'Y');
					$signupItem->isUse = ($item_info->is_active == 'Y');
					$signupItem->description = $item_info->description;
					if ($signupItem->imageType){
						$signupItem->max_width = $config->{$key.'_max_width'};
						$signupItem->max_height = $config->{$key.'_max_height'};
					}
					$list_order[] = $signupItem;
				}
			}
			$args->signupForm = $list_order;
			$args->identifier = $identifier;

            $oModuleController->insertModuleConfig('member',$args);
            // Create a member controller object
            $oMemberController = &getController('member');
            $oMemberAdminController = &getAdminController('member');

            $groups = $oMemberModel->getGroups();
            if(!count($groups)) {
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
            // Configure administrator information
            $admin_args->is_admin = 'Y';
            $output = executeQuery('member.getMemberList', $admin_args);
            if(!$output->data) {
                $admin_info = Context::gets('user_id','password','nick_name','user_name', 'email_address');
                if($admin_info->user_id) {
                    // Insert admin information
                    $oMemberAdminController->insertAdmin($admin_info);
                    // Log-in Processing
                    $output = $oMemberController->doLogin($admin_info->email_address);
                }
            }
            // Register denied ID(default + module name)
            $oModuleModel = &getModel('module');
            $module_list = $oModuleModel->getModuleList();
            foreach($module_list as $key => $val) {
                $oMemberAdminController->insertDeniedID($val->module,'');
            }
            $oMemberAdminController->insertDeniedID('www','');
            $oMemberAdminController->insertDeniedID('root','');
            $oMemberAdminController->insertDeniedID('administrator','');
            $oMemberAdminController->insertDeniedID('telnet','');
            $oMemberAdminController->insertDeniedID('ftp','');
            $oMemberAdminController->insertDeniedID('http','');
            // Create cache directory to use in the member module
            FileHandler::makeDir('./files/member_extra_info/image_name');
            FileHandler::makeDir('./files/member_extra_info/image_mark');
            FileHandler::makeDir('./files/member_extra_info/profile_image');
            FileHandler::makeDir('./files/member_extra_info/signature');

            $oDB->addIndex("member_openid_association","idx_assoc", array("server_url(255)","handle"), false);
            return new Object();
        }

        /**
         * @brief a method to check if successfully installed
         **/
        function checkUpdate() {
            $oDB = &DB::getInstance();
            $oModuleModel = &getModel('module');
            // check member directory (11/08/2007 added)
            if(!is_dir("./files/member_extra_info")) return true;
            // check member directory (22/10/2007 added)
            if(!is_dir("./files/member_extra_info/profile_image")) return true;
            // Add a column(is_register) to "member_auth_mail" table (22/04/2008)
            $act = $oDB->isColumnExists("member_auth_mail", "is_register");
            if(!$act) return true;
            // Add a column(site_srl) to "member_group_member" table (11/15/2008)
            if(!$oDB->isColumnExists("member_group_member", "site_srl")) return true;
            if(!$oDB->isColumnExists("member_group", "site_srl")) return true;
            if($oDB->isIndexExists("member_group","uni_member_group_title")) return true;

			// Add a column for list_order (05/18/2011)
            if(!$oDB->isColumnExists("member_group", "list_order")) return true;

            // image_mark 추가 (2009. 02. 14)
            if(!$oDB->isColumnExists("member_group", "image_mark")) return true;
            // Add c column for password expiration date
            if(!$oDB->isColumnExists("member", "change_password_date")) return true;

            // Add columns of question and answer to verify a password
            if(!$oDB->isColumnExists("member", "find_account_question")) return true;
            if(!$oDB->isColumnExists("member", "find_account_answer")) return true;

            if(!$oDB->isColumnExists("member", "list_order")) return true;
            if(!$oDB->isIndexExists("member","idx_list_order")) return true;

            $oMemberModel = &getModel('member');
            $config = $oMemberModel->getMemberConfig();
			// check signup form ordering info
			if (!$config->signupForm) return true;

            return false;
        }

        /**
         * @brief Execute update
         **/
        function moduleUpdate() {
            $oDB = &DB::getInstance();
            $oModuleController = &getController('module');
            // Check member directory
            FileHandler::makeDir('./files/member_extra_info/image_name');
            FileHandler::makeDir('./files/member_extra_info/image_mark');
            FileHandler::makeDir('./files/member_extra_info/signature');
            FileHandler::makeDir('./files/member_extra_info/profile_image');
            // Add a column
            if (!$oDB->isColumnExists("member_auth_mail", "is_register")) {
                $oDB->addColumn("member_auth_mail", "is_register", "char", 1, "N", true);
            }
            // Add a column(site_srl) to "member_group_member" table (11/15/2008)
            if (!$oDB->isColumnExists("member_group_member", "site_srl")) {
                $oDB->addColumn("member_group_member", "site_srl", "number", 11, 0, true);
                $oDB->addIndex("member_group_member", "idx_site_srl", "site_srl", false);
            }
            if (!$oDB->isColumnExists("member_group", "site_srl")) {
                $oDB->addColumn("member_group", "site_srl", "number", 11, 0, true);
                $oDB->addIndex("member_group","idx_site_title", array("site_srl","title"),true);
            }
            if($oDB->isIndexExists("member_group","uni_member_group_title")) {
                $oDB->dropIndex("member_group","uni_member_group_title",true);
            }
           
            // Add a column(list_order) to "member_group" table (05/18/2011)
            if (!$oDB->isColumnExists("member_group", "list_order")) {
                $oDB->addColumn("member_group", "list_order", "number", 11, '', true);
                $oDB->addIndex("member_group","idx_list_order", "list_order",false);
                $output = executeQuery('member.updateAllMemberGroupListOrder');
            }
            // Add a column for image_mark (02/14/2009)
            if(!$oDB->isColumnExists("member_group", "image_mark")) {
                $oDB->addColumn("member_group", "image_mark", "text");
            }
            // Add a column for password expiration date
            if(!$oDB->isColumnExists("member", "change_password_date")) {
                $oDB->addColumn("member", "change_password_date", "date");
                executeQuery('member.updateAllChangePasswordDate');
            }

            // Add columns of question and answer to verify a password
            if(!$oDB->isColumnExists("member", "find_account_question")) {
                $oDB->addColumn("member", "find_account_question", "number", 11);
            }
            if(!$oDB->isColumnExists("member", "find_account_answer")) {
                $oDB->addColumn("member", "find_account_answer", "varchar", 250);
            }

            if(!$oDB->isColumnExists("member", "list_order")) {
                $oDB->addColumn("member", "list_order", "number", 11);
                set_time_limit(0);
                $args->list_order = 'member_srl';
                executeQuery('member.updateMemberListOrderAll',$args);
                executeQuery('member.updateMemberListOrderAll');
            }
            if(!$oDB->isIndexExists("member","idx_list_order")) {
                $oDB->addIndex("member","idx_list_order", array("list_order"));
            }

            $oMemberModel = &getModel('member');
            $config = $oMemberModel->getMemberConfig();

			// check signup form ordering info
			if (!$config->signupForm || !is_array($config->signupForm)){
				global $lang;
				$oModuleController = &getController('module');
				// Get join form list which is additionally set
				$extendItems = $oMemberModel->getJoinFormList();
				
				$identifier = 'user_id';
				$items = array('user_id', 'password', 'user_name', 'nick_name', 'email_address', 'find_account_question', 'homepage', 'blog', 'birthday', 'signature', 'profile_image', 'image_name', 'image_mark');
				$mustRequireds = array('email_address', 'nick_name','password', 'find_account_question');
				$orgRequireds = array('email_address', 'password', 'find_account_question', 'user_id', 'nick_name', 'user_name');
				$orgUse = array('email_address', 'password', 'find_account_question', 'user_id', 'nick_name', 'user_name', 'homepage', 'blog', 'birthday');
				$list_order = array();
				foreach($items as $key){
					unset($signupItem);
					$signupItem->isDefaultForm = true;
					$signupItem->name = $key;
					$signupItem->title = $lang->{$key};
					$signupItem->mustRequired = in_array($key, $mustRequireds);
					$signupItem->imageType = (strpos($key, 'image') !== false);
					$signupItem->required = in_array($key, $orgRequireds);
					$signupItem->isUse = ($config->{$key} == 'Y') || in_array($key, $orgUse);
					$signupItem->isIdentifier = ($key == $identifier);
					if ($signupItem->imageType){
						$signupItem->max_width = $config->{$key.'_max_width'};
						$signupItem->max_height = $config->{$key.'_max_height'};
					}
					$list_order[] = $signupItem;
				}
				if (is_array($extendItems)){
					foreach($extendItems as $form_srl=>$item_info){
						unset($signupItem);
						$signupItem->name = $item_info->column_name;
						$signupItem->title = $item_info->column_title;
						$signupItem->type = $item_info->column_type;
						$signupItem->member_join_form_srl = $form_srl;
						$signupItem->mustRequired = in_array($key, $mustRequireds);
						$signupItem->required = ($item_info->required == 'Y');
						$signupItem->isUse = ($item_info->is_active == 'Y');
						$signupItem->description = $item_info->description;
						if ($signupItem->imageType){
							$signupItem->max_width = $config->{$key.'_max_width'};
							$signupItem->max_height = $config->{$key.'_max_height'};
						}
						$list_order[] = $signupItem;
					}
				}
				$config->signupForm = $list_order;
				$config->identifier = $identifier;
				$output = $oModuleController->updateModuleConfig('member', $config);
			}

            return new Object(0, 'success_updated');
        }

        /**
         * @brief Re-generate the cache file
         **/
        function recompileCache() {
            set_include_path(_XE_PATH_."modules/member/php-openid-1.2.3");
            require_once('Auth/OpenID/XEStore.php');
            $store = new Auth_OpenID_XEStore();
            $store->reset();
        }
    }
?>
