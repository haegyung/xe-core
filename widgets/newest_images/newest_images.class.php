<?php
    /**
     * @class newest_images
     * @author zero (zero@nzeo.com)
     * @brief 최근 이미지를 출력하는 위젯
     * @version 0.1
     **/

    class newest_images extends WidgetHandler {

        /**
         * @brief 위젯의 실행 부분
         *
         * ./widgets/위젯/conf/info.xml 에 선언한 extra_vars를 args로 받는다
         * 결과를 만든후 print가 아니라 return 해주어야 한다
         **/
        function proc($args) {
            // 글자 제목 길이
            $widget_info->title_length = (int)$args->title_length;
            if(!$widget_info->title_length) $widget_info->title_length = 10;

            // 썸네일 가로 크기
            $widget_info->thumbnail_width = (int)$args->thumbnail_width;
            if(!$widget_info->thumbnail_width) $widget_info->thumbnail_width = 100;

            // 가로 이미지 수
            $widget_info->rows_list_count = (int)$args->rows_list_count;
            if(!$widget_info->rows_list_count) $widget_info->rows_list_count = 5;

            // 세로 이미지 수
            $widget_info->cols_list_count = (int)$args->cols_list_count;
            if(!$widget_info->cols_list_count) $widget_info->cols_list_count = 1;

            // 노출 여부 체크
            if($args->display_author!='Y') $widget_info->display_author = 'N';
            else $widget_info->display_author = 'Y';
            if($args->display_regdate!='Y') $widget_info->display_regdate = 'N';
            else $widget_info->display_regdate = 'Y';
            if($args->display_readed_count!='Y') $widget_info->display_readed_count = 'N';
            else $widget_info->display_readed_count = 'Y';
            if($args->display_voted_count!='Y') $widget_info->display_voted_count = 'N';
            else $widget_info->display_voted_count = 'Y';

            // 제목
            $widget_info->title = $args->title;

            // 대상 모듈 정리
            $mid_list = explode(",",$args->mid_list);

            // 템플릿 파일에서 사용할 변수들을 세팅
            if(count($mid_list)==1) $widget_info->module_name = $mid_list[0];

            // 변수 정리
            $obj->sort_index = $order_target;
            $obj->list_count = $widget_info->rows_list_count * $widget_info->cols_list_count;

            // mid에 해당하는 module_srl을 구함
            $oModuleModel = &getModel('module');
            $module_srl_list = $oModuleModel->getModuleSrlByMid($mid_list);
            if(is_array($module_srl_list)) $obj->module_srls = implode(",",$module_srl_list);
            else $obj->module_srls = $module_srl_list;
            $obj->direct_download = 'Y';
            $obj->isvalid = 'Y';

            // 정해진 모듈에서 문서별 파일 목록을 구함
            $files_output = executeQuery("file.getOneFileInDocument", $obj);

            // 결과에서 문서 번호만을 따로 추출
            if($files_output->data) {
                foreach($files_output->data as $key => $val) {
                    $document_srl_list[] = $val->upload_target_srl;
                }
            }

            $oDocumentModel = &getModel('document');
            if(count($document_srl_list)) {

                $documents_output = $oDocumentModel->getDocuments($document_srl_list);
                if(!count($documents_output)) return;

                foreach($documents_output as $key => $val) {
                    $document_list[] = $val;
                }

            }

            $document_count = count($document_list);
            $total_count = $widget_info->rows_list_count * $widget_info->cols_list_count;
            for($i=$document_count;$i<$total_count;$i++) $document_list[] = new DocumentItem();
            
            $widget_info->document_list = $document_list;

            Context::set('widget_info', $widget_info);

            // 템플릿의 스킨 경로를 지정 (skin, colorset에 따른 값을 설정)
            $tpl_path = sprintf('%sskins/%s', $this->widget_path, $args->skin);
            Context::set('colorset', $args->colorset);

            // 템플릿 파일을 지정
            $tpl_file = 'list';

            // 템플릿 컴파일
            $oTemplate = &TemplateHandler::getInstance();
            $output = $oTemplate->compile($tpl_path, $tpl_file);
            return $output;
        }
    }
?>
