<?php
    /**
     * @class  layoutController
     * @author zero (zero@nzeo.com)
     * @brief  layout 모듈의 Controller class
     **/

    class layoutController extends layout {

        /**
         * @brief 초기화
         **/
        function init() {
        }

        /**
         * @brief 레이아웃 신규 생성
         * 레이아웃의 신규 생성은 제목만 받아서 layouts테이블에 입력함
         **/
        function procLayoutAdminInsert() {
            $args->layout_srl = getNextSequence();
            $args->layout = Context::get('layout');
            $args->title = Context::get('title');

            $output = executeQuery("layout.insertLayout", $args);
            if(!$output->toBool()) return $output;

            $this->add('layout_srl', $args->layout_srl);
        }

        /**
         * @brief 레이아웃 정보 변경
         * 생성된 레이아웃의 제목과 확장변수(extra_vars)를 적용한다
         **/
        function procLayoutAdminUpdate() {
            // module, act, layout_srl, layout, title을 제외하면 확장변수로 판단.. 좀 구리다..
            $extra_vars = Context::getRequestVars();
            unset($extra_vars->module);
            unset($extra_vars->act);
            unset($extra_vars->layout_srl);
            unset($extra_vars->layout);
            unset($extra_vars->title);

            // DB에 입력하기 위한 변수 설정 
            $args = Context::gets('layout_srl','title');
            $args->extra_vars = serialize($extra_vars);

            $output = executeQuery('layout.updateLayout', $args);
            if(!$output->toBool()) return $output;

            $this->setMessage('success_updated');
        }

        /**
         * @brief 레이아웃 삭제
         * 삭제시 메뉴 xml 캐시 파일도 삭제
         **/
        function procLayoutAdminDelete() {
            $layout_srl = Context::get('layout_srl');

            // 캐시 파일 삭제 
            $cache_list = FileHandler::readDir("./files/cache/layout","",false,true);
            if(count($cache_list)) {
                foreach($cache_list as $cache_file) {
                    $pos = strpos($cache_file, $layout_srl.'_');
                    if($pos>0) unlink($cache_file);
                }
            }

            // 레이아웃 삭제
            $output = executeQuery("layout.deleteLayout", $args);
            if(!$output->toBool()) return $output;

            $this->setMessage('success_deleted');
        }

    }
?>
