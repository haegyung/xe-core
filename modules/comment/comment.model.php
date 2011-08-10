<?php
    /**
     * @class  commentModel
     * @author NHN (developers@xpressengine.com)
     * @brief model class of the comment module
     **/

    class commentModel extends comment {

        /**
         * @brief Initialization
         **/
        function init() {
        }

        /**
         * @brief display the pop-up menu of the post
         *
         * Print, scrap, vote-up(recommen), vote-down(non-recommend), report features added
         **/
        function getCommentMenu() {
            // get the post's id number and the current login information
            $comment_srl = Context::get('target_srl');
            $mid = Context::get('cur_mid');
            $logged_info = Context::get('logged_info');
            $act = Context::get('cur_act');
            
            // array values for menu_list, "comment post, target, url"
            $menu_list = array();
            // call a trigger
            ModuleHandler::triggerCall('comment.getCommentMenu', 'before', $menu_list);

            $oCommentController = &getController('comment');
            // feature that only member can do
            if($logged_info->member_srl) {

				$oCommentModel = &getModel('comment');
				$columnList = array('comment_srl', 'module_srl', 'member_srl', 'ipaddress');
				$oComment = $oCommentModel->getComment($comment_srl, false, $columnList);
				$module_srl = $oComment->get('module_srl');
				$member_srl = $oComment->get('member_srl');

				$oModuleModel = &getModel('module');
				$comment_config = $oModuleModel->getModulePartConfig('document',$module_srl);
				if($comment_config->use_vote_up!='N' && $member_srl!=$logged_info->member_srl){
					// Add a vote-up button for positive feedback
					$url = sprintf("doCallModuleAction('comment','procCommentVoteUp','%s')", $comment_srl);
					$oCommentController->addCommentPopupMenu($url,'cmd_vote','./modules/document/tpl/icons/vote_up.gif','javascript');
				}
				if($comment_config->use_vote_down!='N' && $member_srl!=$logged_info->member_srl){
					// Add a vote-down button for negative feedback
					$url = sprintf("doCallModuleAction('comment','procCommentVoteDown','%s')", $comment_srl);
					$oCommentController->addCommentPopupMenu($url,'cmd_vote_down','./modules/document/tpl/icons/vote_down.gif','javascript');
				}

                // Add the report feature against abused posts
                $url = sprintf("doCallModuleAction('comment','procCommentDeclare','%s')", $comment_srl);
                $oCommentController->addCommentPopupMenu($url,'cmd_declare','./modules/document/tpl/icons/declare.gif','javascript');
            }
            // call a trigger (after)
            ModuleHandler::triggerCall('comment.getCommentMenu', 'after', $menu_list);
            // find a comment by IP matching if an administrator. 
            if($logged_info->is_admin == 'Y') {
                $oCommentModel = &getModel('comment');
                $oComment = $oCommentModel->getComment($comment_srl);

                if($oComment->isExists()) {
                    // Find a post of the corresponding ip address
                    $url = getUrl('','module','admin','act','dispCommentAdminList','search_target','ipaddress','search_keyword',$oComment->getIpAddress());
                    $icon_path = './modules/member/tpl/images/icon_management.gif';
                    $oCommentController->addCommentPopupMenu($url,'cmd_search_by_ipaddress',$icon_path,'TraceByIpaddress');

                    $url = sprintf("var params = new Array(); params['ipaddress']='%s'; exec_xml('spamfilter', 'procSpamfilterAdminInsertDeniedIP', params, completeCallModuleAction)", $oComment->getIpAddress());
                    $oCommentController->addCommentPopupMenu($url,'cmd_add_ip_to_spamfilter','./modules/document/tpl/icons/declare.gif','javascript');
                }
            }
            // Changing a language of pop-up menu
            $menus = Context::get('comment_popup_menu_list');
            $menus_count = count($menus);
            for($i=0;$i<$menus_count;$i++) {
                $menus[$i]->str = Context::getLang($menus[$i]->str);
            }
            // get a list of final organized pop-up menus
            $this->add('menus', $menus);
        }


        /**
         * @brief check if you have a permission to comment_srl
         *
         * use only session information
         **/
        function isGranted($comment_srl) {
            return $_SESSION['own_comment'][$comment_srl];
        }

        /**
         * @brief Returns the number of child comments
         **/
        function getChildCommentCount($comment_srl) {
            $args->comment_srl = $comment_srl;
            $output = executeQuery('comment.getChildCommentCount', $args);
            return (int)$output->data->count;
        }

        /**
         * @brief get the comment
         **/
        function getComment($comment_srl=0, $is_admin = false, $columnList = array()) {
            $oComment = new commentItem($comment_srl, $columnList);
            if($is_admin) $oComment->setGrant();

            return $oComment;
        }

        /**
         * @brief get the multiple comments(not paginating)
         **/
        function getComments($comment_srl_list, $columnList = array()) {
            if(is_array($comment_srl_list)) $comment_srls = implode(',',$comment_srl_list);
            // fetch from a database
            $args->comment_srls = $comment_srls;
            $output = executeQuery('comment.getComments', $args, $columnList);
            if(!$output->toBool()) return;
            $comment_list = $output->data;
            if(!$comment_list) return;
            if(!is_array($comment_list)) $comment_list = array($comment_list);

            $comment_count = count($comment_list);
            foreach($comment_list as $key => $attribute) {
                if(!$attribute->comment_srl) continue;
                $oComment = null;
                $oComment = new commentItem();
                $oComment->setAttribute($attribute);
                if($is_admin) $oComment->setGrant();

                $result[$attribute->comment_srl] = $oComment;
            }
            return $result;
        }

        /**
         * @brief get the total number of comments in corresponding with document_srl.
         **/
        function getCommentCount($document_srl) {
            $args->document_srl = $document_srl;
            $output = executeQuery('comment.getCommentCount', $args);
            $total_count = $output->data->count;
            return (int)$total_count;
        }


        /**
         * @brief get the total number of comments in corresponding with module_srl.
         **/
        function getCommentAllCount($module_srl) {
            $args->module_srl = $module_srl;
			$output = executeQuery('comment.getCommentCount', $args);
			$total_count = $output->data->count;
			
            return (int)$total_count;
        }


        /** 
         * @brief get the comment in corresponding with mid.
         **/
        function getNewestCommentList($obj, $columnList = array()) {
            if($obj->mid) {
                $oModuleModel = &getModel('module');
                $obj->module_srl = $oModuleModel->getModuleSrlByMid($obj->mid);
                unset($obj->mid);
            }
            // check if module_srl is an arrary. 
            if(is_array($obj->module_srl)) $args->module_srl = implode(',', $obj->module_srl);
            else $args->module_srl = $obj->module_srl;
            $args->list_count = $obj->list_count;

            $output = executeQuery('comment.getNewestCommentList', $args, $columnList);
            if(!$output->toBool()) return $output;

            $comment_list = $output->data;
            if($comment_list) {
                if(!is_array($comment_list)) $comment_list = array($comment_list);
                $comment_count = count($comment_list);
                foreach($comment_list as $key => $attribute) {
                    if(!$attribute->comment_srl) continue;
                    $oComment = null;
                    $oComment = new commentItem();
                    $oComment->setAttribute($attribute);

                    $result[$key] = $oComment;
                }
                $output->data = $result;
            }
            return $result;
        }

        /** 
         * @brief get a comment list of the doc in corresponding woth document_srl.
         **/
        function getCommentList($document_srl, $page = 0, $is_admin = false, $count = 0) {
        	// cache controll
			$oCacheHandler = &CacheHandler::getInstance('object');
			if($oCacheHandler->isSupport()){
				$cache_key = 'object:'.$document_srl.'_'.$page;
				$output = $oCacheHandler->get($cache_key);
				$cache_object = $oCacheHandler->get('comment_list_document_pages');
				if($cache_object) {
					if(!in_array($cache_key, $cache_object)) {
						$cache_object[]=$cache_key;
						$oCacheHandler->put('comment_list_document_pages',$cache_object);
					}
				} else {
					$cache_object = array();
					$cache_object[] = $cache_key;
					$oCacheHandler->put('comment_list_document_pages',$cache_object);
				}
			}
        	if(!$output){
        		// get the number of comments on the document module
	            $oDocumentModel = &getModel('document');
				$columnList = array('document_srl', 'module_srl', 'comment_count');
	            $oDocument = $oDocumentModel->getDocument($document_srl, false, true, $columnList);
	            // return if no doc exists.
	            if(!$oDocument->isExists()) return;
	            // return if no comment exists
	            if($oDocument->getCommentCount()<1) return;
	            // get a list of comments
	            $module_srl = $oDocument->get('module_srl');
	
	            if(!$count) {
	                $comment_config = $this->getCommentConfig($module_srl);
	                $comment_count = $comment_config->comment_count;
	                if(!$comment_count) $comment_count = 50;
	            } else {
	                $comment_count = $count;
	            }
	            // get a very last page if no page exists
	            if(!$page) $page = (int)( ($oDocument->getCommentCount()-1) / $comment_count) + 1;                
	            // get a list of comments
	            $args->document_srl = $document_srl;
	            $args->list_count = $comment_count;
	            $args->page = $page;
	            $args->page_count = 10;
	            $output = executeQueryArray('comment.getCommentPageList', $args);
	            // return if an error occurs in the query results
	            if(!$output->toBool()) return;
	            // insert data into CommentPageList table if the number of results is different from stored comments
	            if(!$output->data) {
	                $this->fixCommentList($oDocument->get('module_srl'), $document_srl);
	                $output = executeQueryArray('comment.getCommentPageList', $args);
	                if(!$output->toBool()) return;
	            }
	            //insert in cache
	            if($oCacheHandler->isSupport()) $oCacheHandler->put($cache_key,$output);
        		
        	}
        	
            return $output;
        }
            
        /**
         * @brief update a list of comments in corresponding with document_srl
         * take care of previously used data than GA version
         **/
        function fixCommentList($module_srl, $document_srl) {
            // create a lock file to prevent repeated work when performing a batch job
            $lock_file = "./files/cache/tmp/lock.".$document_srl;
            if(file_exists($lock_file) && filemtime($lock_file)+60*60*10<time()) return;
            FileHandler::writeFile($lock_file, '');
            // get a list
            $args->document_srl = $document_srl;
            $args->list_order = 'list_order';
            $output = executeQuery('comment.getCommentList', $args);
            if(!$output->toBool()) return $output;

            $source_list = $output->data;
            if(!is_array($source_list)) $source_list = array($source_list);
            // Sort comments by the hierarchical structure
            $comment_count = count($source_list);

            $root = NULL;
            $list = NULL;
            $comment_list = array();
            // get the log-in information for logged-in users
            $logged_info = Context::get('logged_info');
            // generate a hierarchical structure of comments for loop
            for($i=$comment_count-1;$i>=0;$i--) {
                $comment_srl = $source_list[$i]->comment_srl;
                $parent_srl = $source_list[$i]->parent_srl;
                if(!$comment_srl) continue;
                // generate a list
                $list[$comment_srl] = $source_list[$i];

                if($parent_srl) {
                    $list[$parent_srl]->child[] = &$list[$comment_srl];
                } else {
                    $root->child[] = &$list[$comment_srl];
                }
            }
            $this->_arrangeComment($comment_list, $root->child, 0, null);
            // insert values to the database
            if(count($comment_list)) {
                foreach($comment_list as $comment_srl => $item) {
                    $comment_args = null;
                    $comment_args->comment_srl = $comment_srl;
                    $comment_args->document_srl = $document_srl;
                    $comment_args->head = $item->head;
                    $comment_args->arrange = $item->arrange;
                    $comment_args->module_srl = $module_srl;
                    $comment_args->regdate = $item->regdate;
                    $comment_args->depth = $item->depth;

                    executeQuery('comment.insertCommentList', $comment_args);
                }
            }
            // remove the lock file if successful.
            FileHandler::removeFile($lock_file);
        }

        /**
         * @brief relocate comments in the hierarchical structure
         **/
        function _arrangeComment(&$comment_list, $list, $depth, $parent = null) {
            if(!count($list)) return;
            foreach($list as $key => $val) {

                if($parent) $val->head = $parent->head;
                else $val->head = $val->comment_srl;
                $val->arrange = count($comment_list)+1;

                if($val->child) {
                    $val->depth = $depth;
                    $comment_list[$val->comment_srl] = $val;
                    $this->_arrangeComment($comment_list,$val->child,$depth+1, $val);
                    unset($val->child);
                } else {
                    $val->depth = $depth;
                    $comment_list[$val->comment_srl] = $val;
                }
            }
        }

        /**
         * @brief get all the comments in time decending order(for administrators)
         **/
        function getTotalCommentList($obj, $columnList = array()) {
            $query_id = 'comment.getTotalCommentList';
            // Variables
            $args->sort_index = 'list_order';
            $args->page = $obj->page?$obj->page:1;
            $args->list_count = $obj->list_count?$obj->list_count:20;
            $args->page_count = $obj->page_count?$obj->page_count:10;
            $args->s_module_srl = $obj->module_srl;
            $args->exclude_module_srl = $obj->exclude_module_srl;
            // Search options
            $search_target = $obj->search_target?$obj->search_target:trim(Context::get('search_target'));
            $search_keyword = $obj->search_keyword?$obj->search_keyword:trim(Context::get('search_keyword'));
            if($search_target && $search_keyword) {
                switch($search_target) {
                    case 'content' :
                            if($search_keyword) $search_keyword = str_replace(' ','%',$search_keyword);
                            $args->s_content = $search_keyword;
                        break;
                    case 'user_id' :
                            if($search_keyword) $search_keyword = str_replace(' ','%',$search_keyword);
                            $args->s_user_id = $search_keyword;
                            $query_id = 'comment.getTotalCommentListWithinMember';
                            $args->sort_index = 'comments.list_order';
                        break;
                    case 'user_name' :
                            if($search_keyword) $search_keyword = str_replace(' ','%',$search_keyword);
                            $args->s_user_name = $search_keyword;
                        break;
                    case 'nick_name' :
                            if($search_keyword) $search_keyword = str_replace(' ','%',$search_keyword);
                            $args->s_nick_name = $search_keyword;
                        break;
                    case 'email_address' :
                            if($search_keyword) $search_keyword = str_replace(' ','%',$search_keyword);
                            $args->s_email_address = $search_keyword;
                        break;
                    case 'homepage' :
                            if($search_keyword) $search_keyword = str_replace(' ','%',$search_keyword);
                            $args->s_homepage = $search_keyword;
                        break;
                    case 'regdate' :
                            $args->s_regdate = $search_keyword;
                        break;
                    case 'last_update' :
                            $args->s_last_upate = $search_keyword;
                        break;
                    case 'ipaddress' :
                            $args->s_ipaddress= $search_keyword;
                        break;
                    case 'is_secret' :
                            $args->s_is_secret= $search_keyword;
                        break;
                    case 'member_srl' :
                            $args->{"s_".$search_target} = (int)$search_keyword;
                        break;
                }
            }
            // comment.getTotalCommentList query execution
            $output = executeQueryArray($query_id, $args, $columnList);
            // return when no result or error occurance
            if(!$output->toBool()||!count($output->data)) return $output;
            foreach($output->data as $key => $val) {
                unset($_oComment);
                $_oComment = new CommentItem(0);
                $_oComment->setAttribute($val);
                $output->data[$key] = $_oComment;
            }

            return $output;
        }

        /**
         * @brief get all the comment count in time decending order(for administrators)
         **/
        function getTotalCommentCount($obj) {
            $query_id = 'comment.getTotalCommentCountByGroupStatus';
            // Variables
            $args->s_module_srl = $obj->module_srl;
            $args->exclude_module_srl = $obj->exclude_module_srl;
            // Search options
            $search_target = $obj->search_target?$obj->search_target:trim(Context::get('search_target'));
            $search_keyword = $obj->search_keyword?$obj->search_keyword:trim(Context::get('search_keyword'));
            if($search_target && $search_keyword) {
                switch($search_target) {
                    case 'content' :
                            if($search_keyword) $search_keyword = str_replace(' ','%',$search_keyword);
                            $args->s_content = $search_keyword;
                        break;
                    case 'user_id' :
                            if($search_keyword) $search_keyword = str_replace(' ','%',$search_keyword);
                            $args->s_user_id = $search_keyword;
                            $query_id = 'comment.getTotalCommentCountWithinMemberByGroupStatus';
                        break;
                    case 'user_name' :
                            if($search_keyword) $search_keyword = str_replace(' ','%',$search_keyword);
                            $args->s_user_name = $search_keyword;
                        break;
                    case 'nick_name' :
                            if($search_keyword) $search_keyword = str_replace(' ','%',$search_keyword);
                            $args->s_nick_name = $search_keyword;
                        break;
                    case 'email_address' :
                            if($search_keyword) $search_keyword = str_replace(' ','%',$search_keyword);
                            $args->s_email_address = $search_keyword;
                        break;
                    case 'homepage' :
                            if($search_keyword) $search_keyword = str_replace(' ','%',$search_keyword);
                            $args->s_homepage = $search_keyword;
                        break;
                    case 'regdate' :
                            $args->s_regdate = $search_keyword;
                        break;
                    case 'last_update' :
                            $args->s_last_upate = $search_keyword;
                        break;
                    case 'ipaddress' :
                            $args->s_ipaddress= $search_keyword;
                        break;
                    case 'is_secret' :
                            $args->s_is_secret= $search_keyword;
                        break;
                    case 'member_srl' :
                            $args->{"s_".$search_target} = (int)$search_keyword;
                        break;
                }
            }
            $output = executeQueryArray($query_id, $args);
            // return when no result or error occurance
            if(!$output->toBool()||!count($output->data)) return $output;

            return $output->data;
        }

        /**
         * @brief return a configuration of comments for each module
         **/
        function getCommentConfig($module_srl) {
            $oModuleModel = &getModel('module');
            $comment_config = $oModuleModel->getModulePartConfig('comment', $module_srl);
            if(!isset($comment_config->comment_count)) $comment_config->comment_count = 50;
            return $comment_config;
        }

		function getCommentVotedMemberList()
		{
			$comment_srl = Context::get('comment_srl');
			if(!$comment_srl) return new Object(-1,'msg_invalid_request');

			$point = Context::get('point');
			if($point != -1) $point = 1;

			$oCommentModel = &getModel('comment');
            $oComment = $oCommentModel->getComment($comment_srl, false, false);
			$module_srl = $oComment->get('module_srl');
			if(!$module_srl) return new Object(-1, 'msg_invalid_request');

			$oModuleModel = &getModel('module');
            $comment_config = $oModuleModel->getModulePartConfig('comment',$module_srl);
			if($point == -1){
				if($comment_config->use_vote_down!='S') return new Object(-1, 'msg_invalid_request');
				$args->below_point = 0;
			}else{
				if($comment_config->use_vote_up!='S') return new Object(-1, 'msg_invalid_request');
				$args->more_point = 0;
			}

			$args->comment_srl = $comment_srl;
			$output = executeQueryArray('comment.getVotedMemberList',$args);
			if(!$output->toBool()) return $output;

			$oMemberModel = &getModel('member');
			if($output->data){
				foreach($output->data as $k => $d){
					$profile_image = $oMemberModel->getProfileImage($d->member_srl);
					$output->data[$k]->src = $profile_image->src;
				}
			}

			$this->add('voted_member_list',$output->data);
		}

		function getSecretNameList()
		{
			global $lang;
			if(!isset($lang->secret_name_list))
				return array('Y'=>'Secret', 'N'=>'Public');
			else return $lang->secret_name_list;
		}
    }
?>
