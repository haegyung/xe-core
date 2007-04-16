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
         * @brief 특정 menu_srl의 정보를 이용하여 템플릿을 구한후 return
         * 관리자 페이지에서 특정 메뉴의 정보를 추가하기 위해 서버에서 tpl을 컴파일 한후 컴파일 된 html을 직접 return
         **/
        function getLayoutAdminMenuTplInfo() {
            // 해당 메뉴의 정보를 가져오기 위한 변수 설정
            $menu_id = Context::get('menu_id');
            $menu_srl = Context::get('menu_srl');
            $layuot = Context::get('layout');
            $parent_srl = Context::get('parent_srl');

            // 회원 그룹의 목록을 가져옴
            $oMemberModel = &getModel('member');
            $group_list = $oMemberModel->getGroups();
            Context::set('group_list', $group_list);

            // parent_srl이 있고 menu_srl이 없으면 하부 메뉴 추가임
            if(!$menu_srl && $parent_srl) {
                // 상위 메뉴의 정보를 가져옴
                $parent_info = $this->getLayoutMenuInfo($parent_srl);

                // 추가하려는 메뉴의 기본 변수 설정 
                $menu_info->menu_srl = getNextSequence();
                $menu_info->parent_srl = $parent_srl;
                $menu_info->parent_menu_name = $parent_info->name;

            // root에 메뉴 추가하거나 기존 메뉴의 수정일 경우
            } else {
                // menu_srl 이 있으면 해당 메뉴의 정보를 가져온다
                if($menu_srl) $menu_info = $this->getLayoutMenuInfo($menu_srl);

                // 찾아진 값이 없다면 신규 메뉴 추가로 보고 menu_srl값만 구해줌
                if(!$menu_info->menu_srl) {
                    $menu_info->menu_srl = getNextSequence();
                }
            }

            Context::set('menu_info', $menu_info);

            // template 파일을 직접 컴파일한후 tpl변수에 담아서 return한다.
            require_once("./classes/template/TemplateHandler.class.php");
            $oTemplate = new TemplateHandler();
            $tpl = $oTemplate->compile($this->module_path.'tpl', 'layout_menu_info');

            // return 할 변수 설정
            $this->add('menu_id', $menu_id);
            $this->add('tpl', $tpl);
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
            
            // layout, extra_vars를 정리한 후 xml 파일 정보를 불러옴 (불러올때 결합)
            $info = $output->data;
            $layout_title = $info->title;
            $layout = $info->layout;
            $vars = unserialize($info->extra_vars);

            return $this->getLayoutInfo($layout, $layout_srl, $layout_title, $vars);
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
        function getLayoutInfo($layout, $layout_srl = 0, $layout_title = "", $vars = null) {
            // 요청된 모듈의 경로를 구한다. 없으면 return
            $layout_path = $this->getLayoutPath($layout);
            if(!$layout_path) return;

            // 현재 선택된 모듈의 스킨의 정보 xml 파일을 읽음
            $xml_file = sprintf("%sconf/info.xml", $layout_path);
            if(!file_exists($xml_file)) return;

            // cache 파일을 비교하여 문제 없으면 include하고 $layout_info 변수를 return
            $cache_file = sprintf('./files/cache/layout/%s.%s.cache.php', $layout, Context::getLangType());
            if(file_exists($cache_file)&&filectime($cache_file)>filectime($xml_file)) {
                include $cache_file;
                return $layout_info;
            }

            // cache 파일이 없으면 xml parsing하고 변수화 한 후에 캐시 파일에 쓰고 변수 바로 return
            $oXmlParser = new XmlParser();
            $tmp_xml_obj = $oXmlParser->loadXmlFile($xml_file);
            $xml_obj = $tmp_xml_obj->layout;
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
            if(!is_array($xml_obj->extra_vars->var)) $extra_vars[] = $xml_obj->extra_vars->var;
            else $extra_vars = $xml_obj->extra_vars->var;
            $extra_var_count = count($extra_vars);
            $buff .= sprintf('$layout_info->extra_var_count = "%s";', $extra_var_count);
            for($i=0;$i<$extra_var_count;$i++) {
                unset($var);
                unset($options);
                $var = $extra_vars[$i];

                $buff .= sprintf('$layout_info->extra_var->%s->name = "%s";', $var->attrs->id, $var->name->body);
                $buff .= sprintf('$layout_info->extra_var->%s->type = "%s";', $var->attrs->id, $var->type->body);
                $buff .= sprintf('$layout_info->extra_var->%s->value = $vars->%s;', $var->attrs->id, $var->attrs->id);
                $buff .= sprintf('$layout_info->extra_var->%s->description = "%s";', $var->attrs->id, str_replace('"','\"',$var->description->body));

                $options = $var->options;
                if(!$options) continue;

                if(!is_array($options)) $options = array($options);
                $options_count = count($options);
                for($j=0;$j<$options_count;$j++) {
                    $buff .= sprintf('$layout_info->extra_var->%s->options["%s"] = "%s";', $var->attrs->id, $options[$j]->value->body, $options[$j]->name->body);
                }
            }

            // 메뉴
            if(!is_array($xml_obj->menus->menu)) $menus[] = $xml_obj->menus->menu;
            else $menus = $xml_obj->menus->menu;

            $menu_count = count($menus);
            $buff .= sprintf('$layout_info->menu_count = "%s";', $menu_count);
            for($i=0;$i<$menu_count;$i++) {
                $id = $menus[$i]->attrs->id;
                if($menus[$i]->attrs->default == "true") $buff .= sprintf('$layout_info->default_menu = "%s";', $id);
                $buff .= sprintf('$layout_info->menu->{%s}->id = "%s";',$id, $menus[$i]->attrs->id);
                $buff .= sprintf('$layout_info->menu->{%s}->name = "%s";',$id, $menus[$i]->name->body);
                $buff .= sprintf('$layout_info->menu->{%s}->maxdepth = "%s";',$id, $menus[$i]->maxdepth->body);
                $buff .= sprintf('$layout_info->menu->{%s}->xml_file = "./files/cache/layout/".$layout_srl."_%s.xml.php";',$id, $id);
                $buff .= sprintf('$layout_info->menu->{%s}->php_file = "./files/cache/layout/".$layout_srl."_%s.php";',$id, $id);
            }

            $buff = '<?php if(!defined("__ZBXE__")) exit(); '.$buff.' ?>';
            FileHandler::writeFile($cache_file, $buff);

            if(file_exists($cache_file)) include $cache_file;
            return $layout_info;
        }

        /**
         * @brief 특정 menu_srl의 정보를 return
         * 이 정보중에 group_srls의 경우는 , 로 연결되어 들어가며 사용시에는 explode를 통해 array로 변환 시킴
         **/
        function getLayoutMenuInfo($menu_srl) {
            // menu_srl 이 있으면 해당 메뉴의 정보를 가져온다
            $args->menu_srl = $menu_srl;
            $output = executeQuery('layout.getLayoutMenu', $args);
            if(!$output->toBool()) return $output;

            $node = $output->data;
            if($node->group_srls) $node->group_srls = explode(',',$node->group_srls);
            return $node;
        }
    }
?>
