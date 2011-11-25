<?php
    /**
     * @class  communicationView
     * @author NHN (developers@xpressengine.com)
     * @brief View class of communication module
     **/

    class communicationView extends communication {

        /**
         * @brief Initialization
         **/
        function init() {
            $oCommunicationModel = &getModel('communication');

            $this->communication_config = $oCommunicationModel->getConfig();
            $skin = $this->communication_config->skin;

            Context::set('communication_config', $this->communication_config);

			$config_parse = explode('.', $skin);
			if (count($config_parse) > 1){
				$tpl_path = sprintf('./themes/%s/modules/communication/', $config_parse[0]);
			}else{
				$tpl_path = sprintf('%sskins/%s', $this->module_path, $skin);
			}
            $this->setTemplatePath($tpl_path);
        }

        /**
         * @brief Display message box
         **/
        function dispCommunicationMessages() {
            // Error appears if not logged-in
            if(!Context::get('is_logged')) return $this->stop('msg_not_logged');
            $logged_info = Context::get('logged_info');
            // Set the variables
            $message_srl = Context::get('message_srl');
            $message_type = Context::get('message_type');
            if(!in_array($message_type, array('R','S','T'))) {
                $message_type = 'R';
                Context::set('message_type', $message_type);
            }

            $oCommunicationModel = &getModel('communication');
            // extract contents if message_srl exists
            if($message_srl) {
				$columnList = array('message_srl', 'sender_srl', 'receiver_srl', 'message_type', 'title', 'content', 'readed', 'regdate');
                $message = $oCommunicationModel->getSelectedMessage($message_srl, $columnList);
                if($message->message_srl == $message_srl && ($message->receiver_srl == $logged_info->member_srl || $message->sender_srl == $logged_info->member_srl) ) {
					stripEmbedTagForAdmin($message->content, $message->sender_srl);
					Context::set('message', $message);
				}
            }
            // Extract a list
			$columnList = array('message_srl', 'readed', 'title', 'member.member_srl', 'member.nick_name', 'message.regdate', 'readed_date');
            $output = $oCommunicationModel->getMessages($message_type, $columnList);

            // set a template file
            Context::set('total_count', $output->total_count);
            Context::set('total_page', $output->total_page);
            Context::set('page', $output->page);
            Context::set('message_list', $output->data);
            Context::set('page_navigation', $output->page_navigation);

			$oSecurity = new Security();
			$oSecurity->encodeHTML('message_list..nick_name');

            $this->setTemplateFile('messages');
        }

        /**
         * @brief display a new message
         **/
        function dispCommunicationNewMessage() {
            $this->setLayoutFile('popup_layout');
            // Error appears if not logged-in
            if(!Context::get('is_logged')) return $this->stop('msg_not_logged');
            $logged_info = Context::get('logged_info');

            $oCommunicationModel = &getModel('communication');
            // get a new message
			$columnList = array('message_srl', 'member_srl', 'nick_name', 'title', 'content', 'sender_srl');
            $message = $oCommunicationModel->getNewMessage($columnList);
            if($message) {
				stripEmbedTagForAdmin($message->content, $message->sender_srl);
				Context::set('message', $message);
			}
            
            // Delete a flag
            $flag_path = './files/communication_extra_info/new_message_flags/'.getNumberingPath($logged_info->member_srl);
            $flag_file = sprintf('%s%s', $flag_path, $logged_info->member_srl);
            FileHandler::removeFile($flag_file);

            $this->setTemplateFile('new_message');
        }

        /**
         * @brief Display message sending
         **/
        function dispCommunicationSendMessage() {
            $this->setLayoutFile("popup_layout");
            $oCommunicationModel = &getModel('communication');
            $oMemberModel = &getModel('member');
            // Error appears if not logged-in
            if(!Context::get('is_logged')) return $this->stop('msg_not_logged');
            $logged_info = Context::get('logged_info');
            // get receipient's information 
            $receiver_srl = Context::get('receiver_srl');
            if(!$receiver_srl || $logged_info->member_srl == $receiver_srl) return $this->stop('msg_not_logged');
            // get message_srl of the original message if it is a reply
            $message_srl = Context::get('message_srl');
            if($message_srl) {
                $source_message = $oCommunicationModel->getSelectedMessage($message_srl);
                if($source_message->message_srl == $message_srl && $source_message->sender_srl == $receiver_srl) {
                    $source_message->title = "[re] ".$source_message->title;
                    $source_message->content = "\r\n<br />\r\n<br /><div style=\"padding-left:5px; border-left:5px solid #DDDDDD;\">".trim($source_message->content)."</div>";
                    Context::set('source_message', $source_message);
                }
            }

            $receiver_info = $oMemberModel->getMemberInfoByMemberSrl($receiver_srl);
            Context::set('receiver_info', $receiver_info);
            // set a signiture by calling getEditor of the editor module
            $oEditorModel = &getModel('editor');
            $option->primary_key_name = 'receiver_srl';
            $option->content_key_name = 'content';
            $option->allow_fileupload = false;
            $option->enable_autosave = false;
            $option->enable_default_component = true;// false;
            $option->enable_component = false;
            $option->resizable = false;
            $option->disable_html = true;
            $option->height = 300;
            $option->skin = $this->communication_config->editor_skin;
            $option->colorset = $this->communication_config->editor_colorset;
            $editor = $oEditorModel->getEditor($logged_info->member_srl, $option);
            Context::set('editor', $editor);

            $this->setTemplateFile('send_message');
        }

        /**
         * @brief display a list of friends
         **/
        function dispCommunicationFriend() {
            // Error appears if not logged-in
            if(!Context::get('is_logged')) return $this->stop('msg_not_logged');

            $oCommunicationModel = &getModel('communication');
            // get a group list
            $tmp_group_list = $oCommunicationModel->getFriendGroups();
            $group_count = count($tmp_group_list);
            for($i=0;$i<$group_count;$i++) $friend_group_list[$tmp_group_list[$i]->friend_group_srl] = $tmp_group_list[$i];
            Context::set('friend_group_list', $friend_group_list);
            // get a list of friends
            $friend_group_srl = Context::get('friend_group_srl');
			$columnList = array('friend_srl', 'friend_group_srl', 'target_srl', 'member.nick_name', 'friend.regdate');
            $output = $oCommunicationModel->getFriends($friend_group_srl, $columnList);
            $friend_count = count($output->data);
            if($friend_count) {
                foreach($output->data as $key => $val) {
                    $group_srl = $val->friend_group_srl;
                    $group_title = $friend_group_list[$group_srl]->title;
                    if(!$group_title) $group_title = Context::get('default_friend_group');
                    $output->data[$key]->group_title = $group_title;
                }
            }
            // set a template file
            Context::set('total_count', $output->total_count);
            Context::set('total_page', $output->total_page);
            Context::set('page', $output->page);
            Context::set('friend_list', $output->data);
            Context::set('page_navigation', $output->page_navigation);

            $this->setTemplateFile('friends');
        }

        /**
         * @brief Add a friend
         **/
        function dispCommunicationAddFriend() {
            $this->setLayoutFile("popup_layout");
            // error appears if not logged-in
            if(!Context::get('is_logged')) return $this->stop('msg_not_logged');
            $logged_info = Context::get('logged_info');

            $target_srl = Context::get('target_srl');
            if(!$target_srl) return $this->stop('msg_invalid_request');
            // get information of the member
            $oMemberModel = &getModel('member');
            $oCommunicationModel = &getModel('communication');
            $communication_info = $oMemberModel->getMemberInfoByMemberSrl($target_srl);
            if($communication_info->member_srl != $target_srl) return $this->stop('msg_invalid_request');
            Context::set('target_info', $communication_info);
            // get a group list
            $friend_group_list = $oCommunicationModel->getFriendGroups();
            Context::set('friend_group_list', $friend_group_list);

            $this->setTemplateFile('add_friend');
        }

        /**
         * @brief Add a group of friends
         **/
        function dispCommunicationAddFriendGroup() {
            $this->setLayoutFile("popup_layout");
            // error apprears if not logged-in
            if(!Context::get('is_logged')) return $this->stop('msg_not_logged');
            $logged_info = Context::get('logged_info');
            // change to edit mode when getting the group_srl
            $friend_group_srl = Context::get('friend_group_srl');
            if($friend_group_srl) {
                $oCommunicationModel = &getModel('communication');
                $friend_group = $oCommunicationModel->getFriendGroupInfo($friend_group_srl);
                if($friend_group->friend_group_srl == $friend_group_srl) Context::set('friend_group', $friend_group);
            }

            $this->setTemplateFile('add_friend_group');
        }

    }
?>
