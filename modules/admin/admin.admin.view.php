<?php
    /**
     * @class  adminAdminView
     * @author zero (zero@nzeo.com)
     * @brief  admin 모듈의 admin view class
     **/

    class adminAdminView extends admin {

        /**
         * @brief 초기화
         **/
        function init() {
            if(!$this->grant->is_admin) return;

            // template path 지정
            $this->setTemplatePath($this->module_path.'tpl');

            // 접속 사용자에 대한 체크
            $oMemberModel = &getModel('member');
            $logged_info = $oMemberModel->getLoggedInfo();

            // 관리자용 레이아웃으로 변경
            $this->setLayoutPath($this->getTemplatePath());
            $this->setLayoutFile('layout.html');

            // 설치된 모듈 목록 가져오기
            $oModuleModel = &getModel('module');
            $installed_module_list = $oModuleModel->getModulesXmlInfo();
            foreach($installed_module_list as $key => $val) {
                $action_spec = $oModuleModel->getModuleActionXml($val->module);
                $actions = array();
                if($action_spec->default_index_act) $actions[] = $action_spec->default_index_act;
                if($action_spec->admin_index_act) $actions[] = $action_spec->admin_index_act;
                if($action_spec->action) foreach($action_spec->action as $k => $v) $actions[] = $k;
                $installed_module_list[$key]->actions = $actions;
            }
            Context::set('installed_module_list', $installed_module_list);

            $db_info = Context::getDBInfo();

            Context::set('time_zone_list', $GLOBALS['time_zone']);
            Context::set('time_zone', $GLOBALS['_time_zone']);
            Context::set('use_rewrite', $db_info->use_rewrite=='Y'?'Y':'N');
            Context::set('use_optimizer', $db_info->use_optimizer!='N'?'Y':'N');
            Context::set('qmail_compatibility', $db_info->qmail_compatibility=='Y'?'Y':'N');
            Context::set('use_ssl', $db_info->use_ssl?$db_info->use_ssl:"none");
            if($db_info->http_port)
            {
                Context::set('http_port', $db_info->http_port);
            }
            if($db_info->https_port)
            {
                Context::set('https_port', $db_info->https_port);
            }

            Context::setBrowserTitle("XE Admin Page");
        }

        /**
         * @brief 관리자 메인 페이지 출력
         **/
        function dispAdminIndex() {
            $newest_news_url = sprintf("http://news.zeroboard.com/%s/news.php", Context::getLangType());
            $cache_file = sprintf("%sfiles/cache/newest_news.%s.cache.php", _XE_PATH_,Context::getLangType());
            if(!file_exists($cache_file) || filemtime($cache_file)+ 60*60 < time()) {
                FileHandler::getRemoteFile($newest_news_url, $cache_file);
            }

            if(file_exists($cache_file)) {
                $oXml = new XmlParser();
                $buff = $oXml->parse(FileHandler::readFile($cache_file));

                $item = $buff->zbxe_news->item;
                if($item) {
                    if(!is_array($item)) $item = array($item);

                    foreach($item as $key => $val) {
                        $obj = null;
                        $obj->title = $val->body;
                        $obj->date = $val->attrs->date;
                        $obj->url = $val->attrs->url;
                        $news[] = $obj;
                    }
                    Context::set('news', $news);
                }

                Context::set('released_version', $buff->zbxe_news->attrs->released_version);
                Context::set('download_link', $buff->zbxe_news->attrs->download_link);
            }

            $db_info = Context::getDBInfo();
            Context::set('selected_lang', $db_info->lang_type);

            Context::set('current_version', __ZBXE_VERSION__);
            Context::set('installed_path', realpath('./'));

            $oModuleModel = &getModel('module');
            $module_list = $oModuleModel->getModuleList();
            Context::set('module_list', $module_list);

            $oAddonModel = &getAdminModel('addon');
            $addon_list = $oAddonModel->getAddonList();
            Context::set('addon_list', $addon_list);

            $args->date = date("Ymd000000", time()-60*60*24);
            $today = date("Ymd");

            // 회원
            $output = executeQueryArray("admin.getMemberStatus", $args);
            if($output->data) {
                foreach($output->data as $var) {
                    if($var->date == $today) {
                        $status->member->today = $var->count;
                    } else {
                        $status->member->yesterday = $var->count;
                    }
                }
            }
            $output = executeQuery("admin.getMemberCount", $args);
            $status->member->total = $output->data->count;

            // 문서
            $output = executeQueryArray("admin.getDocumentStatus", $args);
            if($output->data) {
                foreach($output->data as $var) {
                    if($var->date == $today) {
                        $status->document->today = $var->count;
                    } else {
                        $status->document->yesterday = $var->count;
                    }
                }
            }
            $output = executeQuery("admin.getDocumentCount", $args);
            $status->document->total = $output->data->count;

            // 댓글
            $output = executeQueryArray("admin.getCommentStatus", $args);
            if($output->data) {
                foreach($output->data as $var) {
                    if($var->date == $today) {
                        $status->comment->today = $var->count;
                    } else {
                        $status->comment->yesterday = $var->count;
                    }
                }
            }
            $output = executeQuery("admin.getCommentCount", $args);
            $status->comment->total = $output->data->count;

            // 엮인글
            $output = executeQueryArray("admin.getTrackbackStatus", $args);
            if($output->data) {
                foreach($output->data as $var) {
                    if($var->date == $today) {
                        $status->trackback->today = $var->count;
                    } else {
                        $status->trackback->yesterday = $var->count;
                    }
                }
            }
            $output = executeQuery("admin.getTrackbackCount", $args);
            $status->trackback->total = $output->data->count;

            // 첨부파일
            $output = executeQueryArray("admin.getFileStatus", $args);
            if($output->data) {
                foreach($output->data as $var) {
                    if($var->date == $today) {
                        $status->file->today = $var->count;
                    } else {
                        $status->file->yesterday = $var->count;
                    }
                }
            }
            $output = executeQuery("admin.getFileCount", $args);
            $status->file->total = $output->data->count;

            // 게시물 신고
            $output = executeQueryArray("admin.getDocumentDeclaredStatus", $args);
            if($output->data) {
                foreach($output->data as $var) {
                    if($var->date == $today) {
                        $status->documentDeclared->today = $var->count;
                    } else {
                        $status->documentDeclared->yesterday = $var->count;
                    }
                }
            }
            $output = executeQuery("admin.getDocumentDeclaredCount", $args);
            $status->documentDeclared->total = $output->data->count;

            // 댓글 신고
            $output = executeQueryArray("admin.getCommentDeclaredStatus", $args);
            if($output->data) {
                foreach($output->data as $var) {
                    if($var->date == $today) {
                        $status->commentDeclared->today = $var->count;
                    } else {
                        $status->commentDeclared->yesterday = $var->count;
                    }
                }
            }
            $output = executeQuery("admin.getCommentDeclaredCount", $args);
            $status->commentDeclared->total = $output->data->count;

            Context::set('status', $status);

            $this->setTemplateFile('index');
        }

        /**
         * @brief 관리자 설정
         **/
        function dispAdminConfig() {
            $db_info = Context::getDBInfo();

            Context::set('selected_lang', $db_info->lang_type);

            Context::set('lang_supported', Context::loadLangSupported());

            Context::set('lang_selected', Context::loadLangSelected());

            Context::set('ftp_info', Context::getFTPInfo());

            $this->setTemplateFile('config');
        }
    }
?>
