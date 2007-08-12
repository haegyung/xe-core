<?php
    /**
     * @class  editorView
     * @author zero (zero@nzeo.com)
     * @brief  editor 모듈의 view 클래스
     **/

    class editorView extends editor {

        /**
         * @brief 초기화
         **/
        function init() {
        }

        /**
         * @brief 컴포넌트의 팝업 출력을 요청을 받는 action
         **/
        function dispEditorPopup() {
            // css 파일 추가
            Context::addCssFile($this->module_path."tpl/css/editor.css");

            // 변수 정리
            $upload_target_srl = Context::get('upload_target_srl');
            $component = Context::get('component');

            // component 객체를 받음
            $oEditorModel = &getModel('editor');
            $oComponent = &$oEditorModel->getComponentObject($component, $upload_target_srl);
            if(!$oComponent->toBool()) {
                Context::set('message', sprintf(Context::getLang('msg_component_is_not_founded'), $component));
                $this->setTemplatePath($this->module_path.'tpl');
                $this->setTemplateFile('component_not_founded');
            } else {

                // 컴포넌트의 popup url을 출력하는 method실행후 결과를 받음
                $popup_content = $oComponent->getPopupContent();
                Context::set('popup_content', $popup_content);

                // 레이아웃을 popup_layout으로 설정
                $this->setLayoutFile('popup_layout');

                // 템플릿 지정
                $this->setTemplatePath($this->module_path.'tpl');
                $this->setTemplateFile('popup');
            }
        }

        /**
         * @brief 컴퍼넌트 정보 보기 
         **/
        function dispEditorComponentInfo() {
            $component_name = Context::get('component_name');

            $oEditorModel = &getModel('editor');
            $component = $oEditorModel->getComponent($component_name);
            Context::set('component', $component);

            $this->setTemplatePath($this->module_path.'tpl');
            $this->setTemplateFile('view_component');
            $this->setLayoutFile("popup_layout");
        }
    }
?>
