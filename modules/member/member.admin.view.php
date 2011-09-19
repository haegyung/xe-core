<?php
    /**
     * @class  memberAdminView
     * @author NHN (developers@xpressengine.com)
     * @brief  member module's admin view class
     **/

    class memberAdminView extends member {

        var $group_list = NULL; ///< group list
        var $memberInfo = NULL; ///< selected member info

        /**
         * @brief initialization
         **/
        function init() {
            $oMemberModel = &getModel('member');

            // if member_srl exists, set memberInfo
            $member_srl = Context::get('member_srl');
            if($member_srl) {
                $this->memberInfo = $oMemberModel->getMemberInfoByMemberSrl($member_srl);
                if(!$this->memberInfo) Context::set('member_srl','');
                else Context::set('member_info',$this->memberInfo);
            }

            // retrieve group list
            $this->group_list = $oMemberModel->getGroups();
            Context::set('group_list', $this->group_list);

            $this->setTemplatePath($this->module_path.'tpl');
        }

        /**
         * @brief display member list
         **/
        function dispMemberAdminList() {
            $oMemberAdminModel = &getAdminModel('member');
            $oMemberModel = &getModel('member');
            $output = $oMemberAdminModel->getMemberList();

			$filter = Context::get('filter_type');
			global $lang;
			switch($filter){
				case 'super_admin' : Context::set('filter_type_title', $lang->cmd_show_super_admin_member);break;
				case 'site_admin' : Context::set('filter_type_title', $lang->cmd_show_site_admin_member);break;
				case 'enable' :  Context::set('filter_type_title', $lang->approval);break;
				case 'disable' : Context::set('filter_type_title', $lang->denied);break;
				default : Context::set('filter_type_title', $lang->cmd_show_all_member);break;
			}

            // retrieve list of groups for each member
            if($output->data) {
                foreach($output->data as $key => $member) {
                    $output->data[$key]->group_list = $oMemberModel->getMemberGroups($member->member_srl,0);
                }
            }

			$config = $oMemberModel->getMemberConfig();
			$memberIdentifiers = array('user_id'=>'user_id', 'user_name'=>'user_name', 'nick_name'=>'nick_name');
			$usedIdentifiers = array();
			foreach($config->signupForm as $signupItem){
				if (!count($memberIdentifiers)) break;
				if(in_array($signupItem->name, $memberIdentifiers) && ($signupItem->required || $signupItem->isUse)){
					unset($memberIdentifiers[$signupItem->name]);
					$usedIdentifiers[$signupItem->name] = $lang->{$signupItem->name};
				}
			}

            Context::set('total_count', $output->total_count);
            Context::set('total_page', $output->total_page);
            Context::set('page', $output->page);
            Context::set('member_list', $output->data);
            Context::set('usedIdentifiers', $usedIdentifiers);
            Context::set('page_navigation', $output->page_navigation);

            $this->setTemplateFile('member_list');
        }

        /**
         * @brief default configuration for member management
         **/
        function dispMemberAdminConfig() {
			global $lang;
            // retrieve configuration via module model instance
            $oModuleModel = &getModel('module');
            $oMemberModel = &getModel('member');
            $config = $oMemberModel->getMemberConfig();

            // Get join form list which is additionally set
            $extendItems = $oMemberModel->getJoinFormList();

            Context::set('config',$config);

            // list of skins for member module
            $skin_list = $oModuleModel->getSkins($this->module_path);
            Context::set('skin_list', $skin_list);

            // retrieve skins of editor
            $oEditorModel = &getModel('editor');
            Context::set('editor_skin_list', $oEditorModel->getEditorSkinList());

            // get an editor
            $option->primary_key_name = 'temp_srl';
            $option->content_key_name = 'agreement';
            $option->allow_fileupload = false;
            $option->enable_autosave = false;
            $option->enable_default_component = true;
            $option->enable_component = true;
            $option->resizable = true;
            $option->height = 300;
            $editor = $oEditorModel->getEditor(0, $option);
            Context::set('editor', $editor);

			// get denied ID list
            $denied_list = $oMemberModel->getDeniedIDs();
			Context::set('deniedIDs', $denied_list);

            $this->setTemplateFile('member_config');
        }

        /**
         * @brief display member information
         **/
        function dispMemberAdminInfo() {
            $oMemberModel = &getModel('member');
            $oModuleModel = &getModel('module');
            $member_config = $oModuleModel->getModuleConfig('member');
            Context::set('member_config', $member_config);
			$extendForm = $oMemberModel->getCombineJoinForm($this->memberInfo);
            Context::set('extend_form_list', $extendForm);

			$memberInfo = get_object_vars(Context::get('member_info'));
			Context::set('memberInfo', $memberInfo);

			$disableColumns = array('password', 'find_account_question');
			Context::set('disableColumns', $disableColumns);
            $this->setTemplateFile('member_info');
        }

        /**
         * @brief display member insert form
         **/
        function dispMemberAdminInsert() {
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

			$formTags = $this->_getMemberInputTag($memberInfo);
			Context::set('formTags', $formTags);

			$member_config = $oMemberModel->getMemberConfig();
			global $lang;
			if (!$member_config->identifier) $member_config->identifier = 'user_id';
			$identifierForm->title = $lang->{$member_config->identifier};
			$identifierForm->name = $member_config->identifier;
			$identifierForm->value = $memberInfo->{$member_config->identifier};
			Context::set('identifierForm', $identifierForm);
            $this->setTemplateFile('insert_member');
        }

		function _getMemberInputTag($memberInfo){
            $oMemberModel = &getModel('member');
            $extend_form_list = $oMemberModel->getCombineJoinForm($memberInfo);
			$memberInfo = get_object_vars($memberInfo);
			$member_config = $oMemberModel->getMemberConfig();
			$formTags = array();
			global $lang;

			foreach($member_config->signupForm as $no=>$formInfo){
				if (!$formInfo->isUse)continue;
				if ($formInfo->name == $member_config->identifier || $formInfo->name == 'password') continue;
				unset($formTag);
				$inputTag = '';
				$formTag->title = $formInfo->title;
				if ($formInfo->required || $formInfo->mustRequired && $formInfo->name != 'password') $formTag->title = $formTag->title.' <em style="color:red">*</em>';
				$formTag->name = $formInfo->name;

				if($formInfo->isDefaultForm){
					if($formInfo->imageType){
						if($formInfo->name == 'profile_image'){
							$target = $memberInfo['profile_image'];
							$functionName = 'doDeleteProfileImage';
						}elseif($formInfo->name == 'image_name'){
							$target = $memberInfo['image_name'];
							$functionName = 'doDeleteImageName';
						}elseif($formInfo->name == 'image_mark'){
							$target = $memberInfo['image_mark'];
							$functionName = 'doDeleteImageMark';
						}
						if($target->src){
							$inputTag = sprintf('<p class="a"><span id="%s"><img src="%s" alt="%s" /> <button type="button" class="text" onclick="%s(%d);return false;">%s</button></span></p>'
												,$formInfo->name.'tag'
												,$target->src
												,$formInfo->title
												,$functionName
												,$memberInfo['member_srl']
												,$lang->cmd_delete);
						}
						$inputTag .= sprintf('<p class="a"><input type="file" name="%s" id="%s" value="" /> <span class="desc">%s : %dpx, %s : %dpx</span></p>'
											 ,$formInfo->name
											 ,$formInfo->name
											 ,$lang->{$formInfo->name.'_max_width'}
											 ,$member_config->{$formInfo->name.'_max_width'}
											 ,$lang->{$formInfo->name.'_max_height'}
											 ,$member_config->{$formInfo->name.'_max_height'});
					}//end imageType
					elseif($formInfo->name == 'birthday'){
						$inputTag = sprintf('<input type="hidden" name="birthday" id="date_birthday" value="%s" /><input type="text" class="inputDate" id="birthday" value="%s" /> <input type="button" value="%s" class="dateRemover" />'
								,$memberInfo['birthday']
								,zdate($memberInfo['birthday'], 'Y-m-d', false)
								,$lang->cmd_delete);
					}elseif($formInfo->name == 'find_account_question'){
						$inputTag = '<select name="find_account_question" style="width:290px">%s</select><br />';
						$optionTag = array();
						foreach($lang->find_account_question_items as $key=>$val){
							if($key == $memberInfo['find_account_question']) $selected = 'selected="selected"';
							else $selected = '';
							$optionTag[] = sprintf('<option value="%s" %s >%s</option>'
													,$key
													,$selected
													,$val);
						}
						$inputTag = sprintf($inputTag, implode('', $optionTag));
						$inputTag .= '<input type="text" name="find_account_answer" value="'.$memberInfo['find_account_answer'].'" />';
					}else{
						$inputTag = sprintf('<input type="text" name="%s" value="%s" />'
									,$formInfo->name
									,$memberInfo[$formInfo->name]);
					}
				}//end isDefaultForm
				else{
					$extendForm = $extend_form_list[$formInfo->member_join_form_srl];
					$replace = array('column_name' => $extendForm->column_name,
									 'value'		=> $extendForm->value);
					$extentionReplace = array();

					if($extendForm->column_type == 'text' || $extendForm->column_type == 'homepage' || $extendForm->column_type == 'email_address'){
						$template = '<input type="text" name="%column_name%" value="%value%" />';
					}elseif($extendForm->column_type == 'tel'){
						$extentionReplace = array('tel_0' => $extendForm->value[0],
												  'tel_1' => $extendForm->value[1],
												  'tel_2' => $extendForm->value[2]);
						$template = '<input type="text" name="%column_name%[]" value="%tel_0%" size="4" />-<input type="text" name="%column_name%[]" value="%tel_1%" size="4" />-<input type="text" name="%column_name%" value="%tel_2%" size="4" />';
					}elseif($extendForm->column_type == 'textarea'){
						$template = '<textarea name="%column_name%">%value%</textarea>';
					}elseif($extendForm->column_type == 'checkbox'){
						$template = '';
						if($extendForm->default_value){
							$__i = 0;
							foreach($extendForm->default_value as $v){
								$checked = '';
								if(is_array($extendForm->value) && in_array($v, $extendForm->value))$checked = 'checked="checked"';
								$template .= '<input type="checkbox" id="%column_name%'.$__i.'" name="%column_name%[]" value="'.htmlspecialchars($v).'" '.$checked.' /><label for="%column_name%'.$__i.'">'.$v.'</label>';
								$__i++;
							}
						}
					}elseif($extendForm->column_type == 'radio'){
						$template = '';
						if($extendForm->default_value){
							$template = '<ul class="radio">%s</ul>';
							$optionTag = array();
							foreach($extendForm->default_value as $v){
								if($extendForm->value == $v)$checked = 'checked="checked"';
								else $checked = '';
								$optionTag[] = '<li><input type="radio" name="%column_name%" value="'.$v.'" '.$checked.' />'.$v.'</li>';
							}
							$template = sprintf($template, implode('', $optionTag));
						}
					}elseif($extendForm->column_type == 'select'){
						$template = '<select name="'.$formInfo->name.'">%s</select>';
						$optionTag = array();
						if($extendForm->default_value){
							foreach($extendForm->default_value as $v){
								if($v == $extendForm->value) $selected = 'selected="selected"';
								else $selected = '';
								$optionTag[] = sprintf('<option value="%s" %s >%s</option>'
														,$v
														,$selected
														,$v);
							}
						}
						$template = sprintf($template, implode('', $optionTag));
					}elseif($extendForm->column_type == 'kr_zip'){
						Context::loadFile(array('./modules/member/tpl/js/krzip_search.js', 'body'), true);
						$extentionReplace = array(
										 'msg_kr_address'       => $lang->msg_kr_address,
										 'msg_kr_address_etc'       => $lang->msg_kr_address_etc,
										 'cmd_search'	=> $lang->cmd_search,
										 'cmd_search_again'	=> $lang->cmd_search_again,
										 'addr_0'	=> $extendForm->value[0],
										 'addr_1'	=> $extendForm->value[1],);
						$replace = array_merge($extentionReplace, $replace);
						$template = <<<EOD
						<div class="krZip">
							<div class="a" id="zone_address_search_%column_name%" >
								<label for="krzip_address1_%column_name%">%msg_kr_address%</label><br />
								<input type="text" id="krzip_address1_%column_name%" value="%addr_0%" />
								<button type="button">%cmd_search%</button>
							</div>
							<div class="a" id="zone_address_list_%column_name%" style="display:none">
								<select name="%column_name%[]" id="address_list_%column_name%"><option value="%addr_0%">%addr_0%</select>
								<button type="button">%cmd_search_again%</button>
							</div>
							<div class="a address2">
								<label for="krzip_address2_%column_name%">%msg_kr_address_etc%</label><br />
								<input type="text" name="%column_name%[]" id="krzip_address2_%column_name%" value="%addr_1%" />
							</div>
						</div>
						<script type="text/javascript">jQuery(function($){ $.krzip('%column_name%') });</script>
EOD;
					}elseif($extendForm->column_type == 'jp_zip'){
						$template = '<input type="text" name="%column_name%" value="%value%" />';
					}elseif($extendForm->column_type == 'date'){
						$extentionReplace = array('date' => zdate($extendForm->value, 'Y-m-d'),
												  'cmd_delete' => $lang->cmd_delete);
						$template = '<input type="hidden" name="%column_name%" id="date_%column_name%" value="%value%" /><input type="text" class="inputDate" value="%date%" readonly="readonly" /><span class="button"><input type="button" value="%cmd_delete%" class="dateRemover" /></span>';
					}

					$replace = array_merge($extentionReplace, $replace);
					$inputTag = preg_replace('@%(\w+)%@e', '$replace[$1]', $template);

					if($extendForm->description)
						$inputTag .= '<p style="color:#999;">'.htmlspecialchars($extendForm->description).'</p>';
				}
				$formTag->inputTag = $inputTag;
				$formTags[] = $formTag;
			}
			return $formTags;
		}

        /**
         * @brief display member delete form
         **/
        function dispMemberAdminDeleteForm() {
            if(!Context::get('member_srl')) return $this->dispMemberAdminList();
            $this->setTemplateFile('delete_form');
        }

        /**
         * @brief display group list
         **/
        function dispMemberAdminGroupList() {
            $oModuleModel = &getModel('module');

            $config = $oModuleModel->getModuleConfig('member');
            Context::set('config', $config);

            $group_srl = Context::get('group_srl');

            if($group_srl && $this->group_list[$group_srl]) {
                Context::set('selected_group', $this->group_list[$group_srl]);
                $this->setTemplateFile('group_update_form');
            } else {
                $this->setTemplateFile('group_list');
            }

			$output = $oModuleModel->getModuleFileBoxList();
			Context::set('fileBoxList', $output->data);
        }

        /**
         * @brief Display a list of member join form
         **/
        function dispMemberAdminJoinFormList() {
            // Create a member model object
            $oMemberModel = &getModel('member');
            // Get join form list which is additionally set
            $form_list = $oMemberModel->getJoinFormList();
            Context::set('form_list', $form_list);

            $this->setTemplateFile('join_form_list');
        }

        /**
         * @brief Display an admin page for memebr join forms
         **/
        function dispMemberAdminInsertJoinForm() {
            // Get the value of join_form
            $member_join_form_srl = Context::get('member_join_form_srl');
            if($member_join_form_srl) {
                $oMemberModel = &getModel('member');
                $join_form = $oMemberModel->getJoinForm($member_join_form_srl);

                if(!$join_form) Context::set('member_join_form_srl','',true);
                else Context::set('join_form', $join_form);
            }
            $this->setTemplateFile('insert_join_form');
        }

        /**
         * @brief Display denied ID list
         **/
        function dispMemberAdminDeniedIDList() {
            // Create a member model object
            $oMemberModel = &getModel('member');
            // Get a denied ID list
            $output = $oMemberModel->getDeniedIDList();

            Context::set('total_count', $output->total_count);
            Context::set('total_page', $output->total_page);
            Context::set('page', $output->page);
            Context::set('member_list', $output->data);
            Context::set('page_navigation', $output->page_navigation);

            $this->setTemplateFile('denied_id_list');
        }

        /**
         * @brief Update all the member groups
         **/
        function dispMemberAdminManageGroup() {
            // Get a list of the selected member
            $args->member_srl = trim(Context::get('member_srls'));
            $output = executeQueryArray('member.getMembers', $args);
            Context::set('member_list', $output->data);
            // Get a list of the selected member
            $oMemberModel = &getModel('member');
            Context::set('member_groups', $oMemberModel->getGroups());

            $this->setLayoutFile('popup_layout');
            $this->setTemplateFile('manage_member_group');
        }

        /**
         * @brief Delete all members
         **/
        function dispMemberAdminDeleteMembers() {
            // Get a list of the selected member
            $args->member_srl = trim(Context::get('member_srls'));
            $output = executeQueryArray('member.getMembers', $args);
            Context::set('member_list', $output->data);

            $this->setLayoutFile('popup_layout');
            $this->setTemplateFile('delete_members');
        }
    }
?>
