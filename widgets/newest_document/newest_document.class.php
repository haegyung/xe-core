<?php
    /**
     * @class newest_document
     * @author zero (zero@nzeo.com)
     * @brief 최근 게시물을 출력하는 위젯
     * @version 0.1
     **/

    class newest_document extends WidgetHandler {

        /**
         * @brief 위젯의 실행 부분
         *
         * ./widgets/위젯/conf/info.xml 에 선언한 extra_vars를 args로 받는다
         * 결과를 만든후 print가 아니라 return 해주어야 한다
         **/
        function proc($args) {
            // 위젯 자체적으로 설정한 변수들을 체크
            $title = $args->title;
            $order_target = $args->order_target;
            if(!in_array($order_target, array('list_order','update_order'))) $order_target = 'list_order';
            $order_type = $args->order_type;
            if(!in_array($order_type, array('asc','desc'))) $order_type = 'desc';
            $list_count = (int)$args->list_count;
            if(!$list_count) $list_count = 5;
            $mid_list = explode(",",$args->mid_list);
            $subject_cut_size = $args->subject_cut_size;
            if(!$subject_cut_size) $subject_cut_size = 0;

            // module_srl 대신 mid가 넘어왔을 경우는 직접 module_srl을 구해줌
            if($mid_list) {
                $oModuleModel = &getModel('module');
                $module_srl = $oModuleModel->getModuleSrlByMid($mid_list);
            }

            /**
             * order_target=list_order일 경우 document 테이블의 list_order 컬럼말고 document_srl을 이용하게 하고 순서를 반대로 한다
             * 이유는 공지사항을 적용하지 않은 순수한 글 작성 순서로만 사용하기 위해성.ㅁ
             * 2007. 8. 22. zero
             **/
            if($order_target == 'list_order') {
                $order_target = 'document_srl';
                $obj->sort_document_index = '1';
            } else {
                $order_target = 'update_order';
                $obj->sort_update_index = '-1';
            }

            // DocumentModel::getDocumentList()를 이용하기 위한 변수 정리
            $obj->module_srl = implode(',',$module_srl);
            $obj->sort_index = $order_target;
            $obj->order_type = $order_type;
            $obj->list_count = $list_count;

            $output = executeQuery('widgets.newest_document.getNewestDocuments', $obj);

            // document 모듈의 model 객체를 받아서 결과를 객체화 시킴
            $oDocumentModel = &getModel('document');

            // 오류가 생기면 그냥 무시
            if(!$output->toBool()) return;

            // 결과가 있으면 각 문서 객체화를 시킴
            if(count($output->data)) {
                foreach($output->data as $key => $attribute) {
                    $document_srl = $attribute->document_srl;

                    $oDocument = null;
                    $oDocument = new documentItem();
                    $oDocument->setAttribute($attribute);

                    $document_list[$key] = $oDocument;
                }
            } else {

                $document_list = array();
                
            }

            // 템플릿 파일에서 사용할 변수들을 세팅
            if(count($mid_list)==1) $widget_info->module_name = $mid_list[0];
            
            $widget_info->title = $title;
            $widget_info->document_list = $document_list;
            $widget_info->subject_cut_size = $subject_cut_size;

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
