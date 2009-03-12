<?php
    /**
     * @class newest_comment
     * @author zero (zero@nzeo.com)
     * @brief 최근 댓글을 출력하는 위젯
     * @version 0.1
     **/

    class newest_comment extends WidgetHandler {

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

            // 정렬 순서
            $order_type = $args->order_type;

            // 출력된 목록 수
            $list_count = (int)$args->list_count;
            if(!$list_count) $list_count = 5;

            // 제목 길이 자르기
            $subject_cut_size = $args->subject_cut_size;
            if(!$subject_cut_size) $subject_cut_size = 0;

            // 대상 모듈이 선택되어 있지 않으면 해당 사이트의 전체 모듈을 대상으로 함
            $site_module_info = Context::get('site_module_info');
            if($args->module_srls) $obj->module_srl = $args->module_srls;
            else if($site_module_info) $obj->site_srl = (int)$site_module_info->site_srl;

            $obj->sort_index = $order_target;
            $obj->list_count = $list_count;

            // comment 모듈의 model 객체를 받아서 getCommentList() method를 실행
            $oCommentModel = &getModel('comment');
            $output = $oCommentModel->getNewestCommentList($obj);

            // 템플릿 파일에서 사용할 변수들을 세팅
            if(count($mid_list)==1) $widget_info->module_name = $mid_list[0];

            // 모듈이 하나만 선택되었을 경우 대상 모듈 이름과 링크를 생성
            $module_srl = explode(',',$args->module_srls);
            if(count($module_srl)==1) {
                $oModuleModel = &getModel('module');
                $module_info = $oModuleModel->getModuleInfoByModuleSrl($module_srl[0]);
                if($module_info->site_srl) {
                    $site_info = $oModuleModel->getSiteInfo($module_info->site_srl);
                    if($site_info->domain) {
                        $widget_info->more_link = getSiteUrl('http://'.$site_info->domain, '','mid', $module_info->mid);
                    }
                } else {
                    $widget_info->more_link = getUrl('','mid',$module_info->mid);
                }
                $widget_info->module_name = $module_info->mid;
            }
            
            $widget_info->title = $title;
            $widget_info->comment_list = $output;
            $widget_info->subject_cut_size = $subject_cut_size;
            $widget_info->display_regdate = $args->display_regdate=='N'?'N':'Y';

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
