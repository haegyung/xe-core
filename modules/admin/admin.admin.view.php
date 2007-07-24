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

            // shortcut 가져오기
            $oAdminModel = &getAdminModel('admin');
            $shortcut_list = $oAdminModel->getShortCuts();
            Context::set('shortcut_list', $shortcut_list);

            // 현재 실행중인 모듈을 구해 놓음
            $running_module = strtolower(preg_replace('/([a-z]+)([A-Z]+)([a-z]+)(.*)/', '\\2\\3', $this->act));
            Context::set('running_module', $running_module);

            $db_info = Context::getDBInfo();

            Context::set('time_zone_list', $GLOBALS['time_zone']);
            Context::set('time_zone', $GLOBALS['_time_zone']);
            Context::set('use_rewrite', $db_info->use_rewrite=='Y'?'Y':'N');

            Context::setBrowserTitle("ZeroboardXE Admin Page");
        }

        /**
         * @brief 관리자 메인 페이지 출력
         **/
        function dispAdminIndex() {
            // 공식사이트에서 최신 뉴스를 가져옴
            $newest_news_url = sprintf("http://news.zeroboard.com/%s/news.php", Context::getLangType());
            $cache_file = sprintf("./files/cache/newest_news.%s.cache.php", Context::getLangType());

            // 1시간 단위로 캐싱 체크
            if(!file_exists($cache_file) || filectime($cache_file)+ 60*60 < time()) {
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
            }

            $this->setTemplateFile('index');
        }

        /**
         * @brief 관리자 메뉴 숏컷 출력
         **/
        function dispAdminShortCut() {
            $this->setTemplateFile('shortcut_list');
        }
    }
?>
