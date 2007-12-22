<?php
/**
 * @class  code_highlighter
 * @author BNU <bnufactory@gmail.com>
 * @brief Code Highlighter
 **/

class code_highlighter extends EditorHandler {

    // editor_sequence 는 에디터에서 필수로 달고 다녀야 함
    var $editor_sequence = 0;
    var $component_path = '';

    /**
     * @brief editor_sequence과 컴포넌트의 경로를 받음
     **/
    function code_highlighter($editor_sequence, $component_path) {
        $this->editor_sequence = $editor_sequence;
        $this->component_path = $component_path;
    }

    /**
     * @brief popup window요청시 popup window에 출력할 내용을 추가하면 된다
     **/
    function getPopupContent() {
        // 템플릿을 미리 컴파일해서 컴파일된 소스를 return
        $tpl_path = $this->component_path.'tpl';
        $tpl_file = 'popup.html';

        Context::set("tpl_path", $tpl_path);

        $oTemplate = &TemplateHandler::getInstance();
        return $oTemplate->compile($tpl_path, $tpl_file);
    }

    /**
     * @brief 에디터 컴포넌트가 별도의 고유 코드를 이용한다면 그 코드를 html로 변경하여 주는 method
     *
     * 이미지나 멀티미디어, 설문등 고유 코드가 필요한 에디터 컴포넌트는 고유코드를 내용에 추가하고 나서
     * DocumentModule::transContent() 에서 해당 컴포넌트의 transHtml() method를 호출하여 고유코드를 html로 변경
     **/
    function transHTML($xml_obj) {
        $code_type = $xml_obj->attrs->code_type;
        $option_file_path = $xml_obj->attrs->file_path;
        $option_description = $xml_obj->attrs->description;
        $option_first_line = $xml_obj->attrs->first_line;
        $option_collapse = $xml_obj->attrs->collapse;
        $option_nogutter = $xml_obj->attrs->nogutter;
        $option_nocontrols = $xml_obj->attrs->nocontrols;
        if($option_collapse == 'true') $option = $option.':collapse';
        if($option_nogutter == 'true') $option = $option.':nogutter';
        if($option_nocontrols == 'true' && $option_collapse != 'true') $option = $option.':nocontrols';
        if($option_first_line) $option = $option.":firstline[$option_first_line]";
        $body = $xml_obj->body;



        $body = preg_replace('@(<br\\s*/?>)(\n)?@i' , "\n", $body);
        $body = strip_tags($body);

        if(!$GLOBALS['_called_code_highlighter_']) {
            $GLOBALS['_called_code_highlighter_'] = true;
            $js_code = <<<dpScript
<script type="text/javascript">
dp.SyntaxHighlighter.ClipboardSwf = '{$this->component_path}script/clipboard.swf';
dp.SyntaxHighlighter.HighlightAll('code');
</script>
dpScript;

            Context::addHtmlFooter($js_code);
        }

        Context::addCSSFile($this->component_path.'css/SyntaxHighlighter.css');

        Context::addJsFile($this->component_path.'script/shCore.js');
        Context::addJsFile($this->component_path.'script/shBrush'.$code_type.'.js');

        $output = null;
        if(isset($option_file_path) || isset($option_description)) {
            $output .= '<div class="ch_infobox">';
            if(isset($option_file_path)) $output .= '<span class="file_path">'.$option_file_path.'</span>';
            if(isset($option_description)) $output .= '<span class="description">'.$option_description.'</span>';
            $output .= '</div>';
        }
        $output .= sprintf('<pre name="code" class="%s">%s</pre>', $code_type.$option, $body);
        return $output;
    }
}
?>