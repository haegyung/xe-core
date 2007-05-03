<?php
    /**
     * @class calendar
     * @author zero (zero@nzeo.com)
     * @brief 보관현황 목록 출력
     * @version 0.1
     **/

    class calendar extends PluginHandler {

        /**
         * @brief 플러그인의 실행 부분
         *
         * ./plugins/플러그인/conf/info.xml 에 선언한 extra_vars를 args로 받는다
         * 결과를 만든후 print가 아니라 return 해주어야 한다
         **/
        function proc($args) {
            // 플러그인 자체적으로 설정한 변수들을 체크
            $title = $args->title;
            $mid_list = explode(",",$args->mid_list);

            // DocumentModel::getDailyArchivedList()를 이용하기 위한 변수 정리
            $obj->mid = $mid_list;
            $obj->regdate = date("Ym");

            // document 모듈의 model 객체를 받아서 getDailyArchivedList() method를 실행
            $oDocumentModel = &getModel('document');
            $output = $oDocumentModel->getDailyArchivedList($obj);

            // 템플릿 파일에서 사용할 변수들을 세팅
            $plugin_info->cur_date = date('Ym');
            $plugin_info->today_str = sprintf('%2d%s %2d%s',date('m'), Context::getLang('unit_month'), date('d'), Context::getLang('unit_day'));
            $plugin_info->last_day = date('t');
            $plugin_info->start_week= date('w', mktime(0,0,0,date('m'),1,date('Y')));

            if(count($mid_list)==1) $plugin_info->module_name = $mid_list[0];
            $plugin_info->title = $title;

            if(count($output->data)) {
                foreach($output->data as $key => $val) $plugin_info->calendar[$val->month] = $val->count;
            }

            preg_match_all('/(width|height)([^[:digit:]]+)([0-9]+)/i',$args->style,$matches);
            $plugin_info->width = trim($matches[3][0]);
            Context::set('plugin_info', $plugin_info);

            // 템플릿의 스킨 경로를 지정 (skin, colorset에 따른 값을 설정)
            $tpl_path = sprintf('%sskins/%s', $this->plugin_path, $args->skin);
            Context::set('colorset', $args->colorset);

            // 템플릿 파일을 지정
            $tpl_file = 'list';

            // 템플릿 컴파일
            $oTemplate = &TemplateHandler::getInstance();
            return $oTemplate->compile($tpl_path, $tpl_file);
        }
    }
?>
