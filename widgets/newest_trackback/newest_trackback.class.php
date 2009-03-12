<?php
    /**
     * @class newest_trackback
     * @author zero (zero@nzeo.com)
     * @brief 최근 엮인글을 출력하는 위젯
     * @version 0.1
     **/

    class newest_trackback extends WidgetHandler {

        /**
         * @brief 위젯의 실행 부분
         *
         * ./widgets/위젯/conf/info.xml 에 선언한 extra_vars를 args로 받는다
         * 결과를 만든후 print가 아니라 return 해주어야 한다
         **/
        function proc($args) {
            // 대상 모듈 (mid_list는 기존 위젯의 호환을 위해서 처리하는 루틴을 유지. module_srls로 위젯에서 변경)
            $oModuleModel = &getModel('module');
            if($args->mid_list) {
                $mid_list = explode(",",$args->mid_list);
                if(count($mid_list)) {
                    $module_srls = $oModuleModel->getModuleSrlByMid($mid_list);
                    if(count($module_srls)) $args->module_srls = implode(',',$module_srls);
                    else $args->module_srls = null;
                } 
            }

            // 제목
            $title = $args->title;

            // 정렬 대상
            $order_target = $args->order_target;
            if(!in_array($order_target, array('list_order','update_order'))) $order_target = 'list_order';

            // 정렬 순서
            $order_type = $args->order_type;
            if(!in_array($order_type, array('asc','desc'))) $order_type = 'asc';

            // 출력된 목록 수
            $list_count = (int)$args->list_count;
            if(!$list_count) $list_count = 5;

            // 대상 모듈이 선택되어 있지 않으면 해당 사이트의 전체 모듈을 대상으로 함
            $site_module_info = Context::get('site_module_info');
            if($args->module_srls) $obj->module_srl = $args->module_srls;
            else if($site_module_info) $obj->site_srl = (int)$site_module_info->site_srl;
            $obj->sort_index = $order_target;
            $obj->list_count = $list_count;

            // trackback 모듈의 model 객체를 받아서 getTrackbackList() method를 실행
            $oTrackbackModel = &getModel('trackback');
            $output = $oTrackbackModel->getNewestTrackbackList($obj);
            
            $widget_info->title = $title;
            $widget_info->trackback_list = $output->data;

            preg_match_all('/(width|height)([^[:digit:]]+)([0-9]+)/i',$args->style,$matches);
            $widget_info->width = trim($matches[3][0]);
            Context::set('widget_info', $widget_info);

            // 템플릿의 스킨 경로를 지정 (skin, colorset에 따른 값을 설정)
            $tpl_path = sprintf('%sskins/%s', $this->widget_path, $args->skin);
            Context::set('colorset', $args->colorset);

            // 템플릿 파일을 지정
            $tpl_file = 'list';

            // 템플릿 컴파일
            $oTemplate = &TemplateHandler::getInstance();
            return $oTemplate->compile($tpl_path, $tpl_file);
        }
    }
?>
