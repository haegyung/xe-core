<?php
    /**
     * @class  widgetModel
     * @author zero (zero@nzeo.com)
     * @version 0.1
     * @brief  widget 모듈의 Model class
     **/

    class widgetModel extends widget {

        /**
         * @brief 초기화
         **/
        function init() {
        }

        /**
         * @brief 위젯의 경로를 구함
         **/
        function getWidgetPath($widget_name) {
            $path = sprintf('./widgets/%s/', $widget_name);
            if(is_dir($path)) return $path; 

            return "";
        }

        /**
         * @brief 위젯의 종류와 정보를 구함
         * 다운로드되어 있는 위젯의 종류 (생성과 다른 의미)
         **/
        function getDownloadedWidgetList() {
            // 다운받은 위젯과 설치된 위젯의 목록을 구함
            $searched_list = FileHandler::readDir('./widgets');
            $searched_count = count($searched_list);
            if(!$searched_count) return;
            sort($searched_list);

            // 찾아진 위젯 목록을 loop돌면서 필요한 정보를 간추려 return
            for($i=0;$i<$searched_count;$i++) {
                // 위젯의 이름
                $widget = $searched_list[$i];

                // 해당 위젯의 정보를 구함
                $widget_info = $this->getWidgetInfo($widget);

                $list[] = $widget_info;
            }
            return $list;
        }

        /**
         * @brief 모듈의 conf/info.xml 을 읽어서 정보를 구함
         * 이것 역시 캐싱을 통해서 xml parsing 시간을 줄인다.. 
         **/
        function getWidgetInfo($widget) {
            // 요청된 모듈의 경로를 구한다. 없으면 return
            $widget_path = $this->getWidgetPath($widget);
            if(!$widget_path) return;

            // 현재 선택된 모듈의 스킨의 정보 xml 파일을 읽음
            $xml_file = sprintf("%sconf/info.xml", $widget_path);
            if(!file_exists($xml_file)) return;

            // cache 파일을 비교하여 문제 없으면 include하고 $widget_info 변수를 return
            $cache_file = sprintf('./files/cache/widget/%s.%s.cache.php', $widget, Context::getLangType());

            if(file_exists($cache_file)&&filemtime($cache_file)>filemtime($xml_file)) {
                @include($cache_file);
                return $widget_info;
            }

            // cache 파일이 없으면 xml parsing하고 변수화 한 후에 캐시 파일에 쓰고 변수 바로 return
            $oXmlParser = new XmlParser();
            $tmp_xml_obj = $oXmlParser->loadXmlFile($xml_file);
            $xml_obj = $tmp_xml_obj->widget;
            if(!$xml_obj) return;

            $buff = '';

            // 위젯의 제목, 버전
            $buff .= sprintf('$widget_info->widget = "%s";', $widget);
            $buff .= sprintf('$widget_info->path = "%s";', $widget_path);
            $buff .= sprintf('$widget_info->title = "%s";', $xml_obj->title->body);
            $buff .= sprintf('$widget_info->version = "%s";', $xml_obj->attrs->version);
            $buff .= sprintf('$widget_info->widget_srl = $widget_srl;');
            $buff .= sprintf('$widget_info->widget_title = $widget_title;');

            // 작성자 정보
            $buff .= sprintf('$widget_info->author->name = "%s";', $xml_obj->author->name->body);
            $buff .= sprintf('$widget_info->author->email_address = "%s";', $xml_obj->author->attrs->email_address);
            $buff .= sprintf('$widget_info->author->homepage = "%s";', $xml_obj->author->attrs->link);
            $buff .= sprintf('$widget_info->author->date = "%s";', $xml_obj->author->attrs->date);
            $buff .= sprintf('$widget_info->author->description = "%s";', $xml_obj->author->description->body);

            // 추가 변수 (템플릿에서 사용할 제작자 정의 변수)
            $extra_var_groups = $xml_obj->extra_vars->group;
            if(!$extra_var_groups) $extra_var_groups = $xml_obj->extra_vars;
            if(!is_array($extra_var_groups)) $extra_var_groups = array($extra_var_groups);
            foreach($extra_var_groups as $group){
                $extra_vars = $group->var;
                if(!is_array($group->var)) $extra_vars = array($group->var);
    
                if($extra_vars[0]->attrs->id || $extra_vars[0]->attrs->name) {
                    $extra_var_count = count($extra_vars);
    
                    $buff .= sprintf('$widget_info->extra_var_count = "%s";', $extra_var_count);
                    for($i=0;$i<$extra_var_count;$i++) {
                        unset($var);
                        unset($options);
                        $var = $extra_vars[$i];

                        $id = $var->attrs->id?$var->attrs->id:$var->attrs->name;
                        $name = $var->name->body?$var->name->body:$var->title->body;
                        $type = $var->attrs->type?$var->attrs->type:$var->type->body;

                        $buff .= sprintf('$widget_info->extra_var->%s->group = "%s";', $id, $group->title->body);
                        $buff .= sprintf('$widget_info->extra_var->%s->name = "%s";', $id, $name);
                        $buff .= sprintf('$widget_info->extra_var->%s->type = "%s";', $id, $type);
                        $buff .= sprintf('$widget_info->extra_var->%s->value = $vars->%s;', $id, $id);
                        $buff .= sprintf('$widget_info->extra_var->%s->description = "%s";', $id, str_replace('"','\"',$var->description->body));
    
                        $options = $var->options;
                        if(!$options) continue;

                        if(!is_array($options)) $options = array($options);
                        $options_count = count($options);
                        for($j=0;$j<$options_count;$j++) {
                            $buff .= sprintf('$widget_info->extra_var->%s->options["%s"] = "%s";', $id, $options[$j]->value->body, $options[$j]->name->body);
                        }
    
                    }
                }
            }

            $buff = '<?php if(!defined("__ZBXE__")) exit(); '.$buff.' ?>';
            FileHandler::writeFile($cache_file, $buff);

            if(file_exists($cache_file)) @include($cache_file);
            return $widget_info;
        }

    }
?>
