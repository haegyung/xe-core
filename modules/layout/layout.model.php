<?php
    /**
     * @class  layoutModel
     * @author zero (zero@nzeo.com)
     * @version 0.1
     * @brief  layout 모듈의 Model class
     **/

    class layoutModel extends layout {

        /**
         * @brief 초기화
         **/
        function init() {
        }

        /**
         * @brief DB 에 생성된 레이아웃의 목록을 구함
         * 생성되었다는 것은 DB에 등록이 되었다는 것을 의미 
         **/
        function getLayoutList() {
            $output = executeQuery('layout.getLayoutList');
            if(!$output->data) return;

            if(is_array($output->data)) return $output->data;
            return array($output->data);
        }

        /**
         * @brief DB 에 생성된 한개의 레이아웃 정보를 구함
         * 생성된 레이아웃의 DB정보+XML정보를 return
         **/
        function getLayout($layout_srl) {
            // 일단 DB에서 정보를 가져옴
            $args->layout_srl = $layout_srl;
            $output = executeQuery('layout.getLayout', $args);
            if(!$output->data) return;

            // layout, extra_vars를 정리한 후 xml 파일 정보를 정리해서 return
            $layout_info = $this->getLayoutInfo($layout, $output->data);
            return $layout_info;
        }

        /**
         * @brief 레이아웃의 경로를 구함
         **/
        function getLayoutPath($layout_name) {
            $class_path = sprintf('./layouts/%s/', $layout_name);
            if(is_dir($class_path)) return $class_path; 

            return "";
        }

        /**
         * @brief 레이아웃의 종류와 정보를 구함
         * 다운로드되어 있는 레이아웃의 종류 (생성과 다른 의미)
         **/
        function getDownloadedLayoutList() {
            // 다운받은 레이아웃과 설치된 레이아웃의 목록을 구함
            $searched_list = FileHandler::readDir('./layouts');
            $searched_count = count($searched_list);
            if(!$searched_count) return;

            // 찾아진 레이아웃 목록을 loop돌면서 필요한 정보를 간추려 return
            for($i=0;$i<$searched_count;$i++) {
                // 레이아웃의 이름
                $layout = $searched_list[$i];

                // 해당 레이아웃의 정보를 구함
                $layout_info = $this->getLayoutInfo($layout);

                $list[] = $layout_info;
            }
            return $list;
        }

        /**
         * @brief 모듈의 conf/info.xml 을 읽어서 정보를 구함
         * 이것 역시 캐싱을 통해서 xml parsing 시간을 줄인다.. 
         **/
        function getLayoutInfo($layout, $info = null) {
            if($info) {
                $layout_title = $info->title;
                $layout = $info->layout;
                $layout_srl = $info->layout_srl;
                $vars = unserialize($info->extra_vars);

                if($info->module_srl) {
                    $layout_path = preg_replace('/([a-zA-Z0-9\_\.]+)(\.html)$/','',$info->layout_path);
                    $xml_file = sprintf('%sskin.xml', $layout_path);
                }
            } 

            // 요청된 모듈의 경로를 구한다. 없으면 return
            if(!$layout_path) $layout_path = $this->getLayoutPath($layout);
            if(!is_dir($layout_path)) return;

            // 현재 선택된 모듈의 스킨의 정보 xml 파일을 읽음
            if(!$xml_file) $xml_file = sprintf("%sconf/info.xml", $layout_path);
            if(!file_exists($xml_file)) {
                $layout_info->layout = $layout;
                $layout_info->path = $layout_path;
                $layout_info->layout_title = $layout_title;
                return $layout_info;
            }

            // cache 파일을 비교하여 문제 없으면 include하고 $layout_info 변수를 return
            $cache_file = sprintf('./files/cache/layout/%s.%s.cache.php', $layout, Context::getLangType());
            if(file_exists($cache_file)&&filectime($cache_file)>filectime($xml_file)) {
                @include($cache_file);

                if($layout_info->extra_var && $vars) {
                    foreach($vars as $key => $value) {
                        if(!$layout_info->extra_var->{$key} && !$layout_info->{$key}) {
                            $layout_info->{$key} = $value;
                        }
                    }
                }
                return $layout_info;
            }

            // cache 파일이 없으면 xml parsing하고 변수화 한 후에 캐시 파일에 쓰고 변수 바로 return
            $oXmlParser = new XmlParser();
            $tmp_xml_obj = $oXmlParser->loadXmlFile($xml_file);
            if($tmp_xml_obj->layout) $xml_obj = $tmp_xml_obj->layout;
            elseif($tmp_xml_obj->skin) $xml_obj = $tmp_xml_obj->skin;

            if(!$xml_obj) return;

            $buff = '';

            // 레이아웃의 제목, 버전
            $buff .= sprintf('$layout_info->layout = "%s";', $layout);
            $buff .= sprintf('$layout_info->path = "%s";', $layout_path);
            $buff .= sprintf('$layout_info->title = "%s";', $xml_obj->title->body);
            $buff .= sprintf('$layout_info->version = "%s";', $xml_obj->attrs->version);
            $buff .= sprintf('$layout_info->layout_srl = $layout_srl;');
            $buff .= sprintf('$layout_info->layout_title = $layout_title;');

            // 작성자 정보
            $buff .= sprintf('$layout_info->author->name = "%s";', $xml_obj->author->name->body);
            $buff .= sprintf('$layout_info->author->email_address = "%s";', $xml_obj->author->attrs->email_address);
            $buff .= sprintf('$layout_info->author->homepage = "%s";', $xml_obj->author->attrs->link);
            $buff .= sprintf('$layout_info->author->date = "%s";', $xml_obj->author->attrs->date);
            $buff .= sprintf('$layout_info->author->description = "%s";', $xml_obj->author->description->body);

            // 추가 변수 (템플릿에서 사용할 제작자 정의 변수)
            $extra_vars = $xml_obj->extra_vars->var;
            if($extra_vars) {
                if(!is_array($extra_vars)) $extra_vars = array($extra_vars);

                $extra_var_count = count($extra_vars);
                
                $buff .= sprintf('$layout_info->extra_var_count = "%s";', $extra_var_count);
                for($i=0;$i<$extra_var_count;$i++) {
                    unset($var);
                    unset($options);
                    $var = $extra_vars[$i];
                    $name = $var->attrs->name;

                    $buff .= sprintf('$layout_info->extra_var->%s->title = "%s";', $name, $var->title->body);
                    $buff .= sprintf('$layout_info->extra_var->%s->type = "%s";', $name, $var->attrs->type);
                    $buff .= sprintf('$layout_info->extra_var->%s->value = $vars->%s;', $name, $name);
                    $buff .= sprintf('$layout_info->extra_var->%s->description = "%s";', $name, str_replace('"','\"',$var->description->body));

                    $options = $var->options;
                    if(!$options) continue;

                    if(!is_array($options)) $options = array($options);
                    $options_count = count($options);
                    for($j=0;$j<$options_count;$j++) {
                        $buff .= sprintf('$layout_info->extra_var->%s->options["%s"] = "%s";', $var->attrs->name, $options[$j]->value->body, $options[$j]->title->body);
                    }
                }
            }

            // 메뉴
            if($xml_obj->menus->menu) {
                $menus = $xml_obj->menus->menu;
                if(!is_array($menus)) $menus = array($menus);

                $menu_count = count($menus);
                $buff .= sprintf('$layout_info->menu_count = "%s";', $menu_count);
                for($i=0;$i<$menu_count;$i++) {
                    $name = $menus[$i]->attrs->name;
                    if($menus[$i]->attrs->default == "true") $buff .= sprintf('$layout_info->default_menu = "%s";', $name);
                    $buff .= sprintf('$layout_info->menu->%s->name = "%s";',$name, $menus[$i]->attrs->name);
                    $buff .= sprintf('$layout_info->menu->%s->title = "%s";',$name, $menus[$i]->title->body);
                    $buff .= sprintf('$layout_info->menu->%s->maxdepth = "%s";',$name, $menus[$i]->maxdepth->body);

                    $buff .= sprintf('$layout_info->menu->%s->menu_srl = $vars->%s;', $name, $name);
                    $buff .= sprintf('$layout_info->menu->%s->xml_file = "./files/cache/menu/".$vars->%s.".xml.php";',$name, $name);
                    $buff .= sprintf('$layout_info->menu->%s->php_file = "./files/cache/menu/".$vars->%s.".php";',$name, $name);
                }
            }

            $buff = '<?php if(!defined("__ZBXE__")) exit(); '.$buff.' ?>';
            FileHandler::writeFile($cache_file, $buff);
            if(file_exists($cache_file)) @include($cache_file);
            return $layout_info;
        }

    }
?>
