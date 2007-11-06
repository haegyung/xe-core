<?php
    /**
     * @class  documentModel
     * @author zero (zero@nzeo.com)
     * @brief  document 모듈의 model 클래스
     **/

    class documentModel extends document {

        /**
         * @brief 초기화
         **/
        function init() {
        }

        /**
         * @brief document에 대한 권한을 세션값으로 체크
         **/
        function isGranted($document_srl) {
            return $_SESSION['own_document'][$document_srl];
        }

        /**
         * @brief 문서 가져오기
         **/
        function getDocument($document_srl=0, $is_admin = false) {
            $oDocument = new documentItem($document_srl);
            if($is_admin) $oDocument->setGrant();

            return $oDocument;
       }

        /**
         * @brief 선택된 게시물의 팝업메뉴 표시
         *
         * 인쇄, 스크랩, 추천, 비추천, 신고 기능 추가
         **/
        function getDocumentMenu() {

            // 요청된 게시물 번호와 현재 로그인 정보 구함
            $document_srl = Context::get('target_srl');
            $mid = Context::get('cur_mid');
            $logged_info = Context::get('logged_info');
            $act = Context::get('cur_act');
            
            // menu_list 에 "표시할글,target,url" 을 배열로 넣는다
            $menu_list = array();

            // trigger 호출
            ModuleHandler::triggerCall('document.getDocumentMenu', 'before', $menu_list);

            // 인쇄 버튼 추가
            $menu_str = Context::getLang('cmd_print');
            $menu_link = sprintf("%s?document_srl=%s&act=dispDocumentPrint",Context::getRequestUri(),$document_srl);
            $menu_list[] = sprintf("\n%s,%s,winopen('%s','MemberModifyInfo')", '' ,$menu_str, $menu_link);

            // 추천 버튼 추가
            $menu_str = Context::getLang('cmd_vote');
            $menu_link = sprintf("doCallModuleAction('document','procDocumentVoteUp','%s')", $document_srl);
            $menu_list[] = sprintf("\n%s,%s,%s", '', $menu_str, $menu_link);

            // 비추천 버튼 추가
            $menu_str = Context::getLang('cmd_vote_down');
            $menu_link = sprintf("doCallModuleAction('document','procDocumentVoteDown','%s')", $document_srl);
            $menu_list[] = sprintf("\n%s,%s,%s", '', $menu_str, $menu_link);

            // 신고 기능 추가
            $menu_str = Context::getLang('cmd_declare');
            $menu_link = sprintf("doCallModuleAction('document','procDocumentDeclare','%s')", $document_srl);
            $menu_list[] = sprintf("\n%s,%s,%s", '', $menu_str, $menu_link);

            // 회원이어야만 가능한 기능
            if($logged_info->member_srl) {

                // 스크랩 버튼 추가
                $menu_str = Context::getLang('cmd_scrap');
                $menu_link = sprintf("doCallModuleAction('member','procMemberScrapDocument','%s')", $document_srl);
                $menu_list[] = sprintf("\n%s,%s,%s", '', $menu_str, $menu_link);
            }

            // trigger 호출 (after)
            ModuleHandler::triggerCall('document.getDocumentMenu', 'after', $menu_list);

            // 정보를 저장
            $this->add("menu_list", implode("\n",$menu_list));
        }

        /**
         * @brief 여러개의 문서들을 가져옴 (페이징 아님)
         **/
        function getDocuments($document_srls, $is_admin = false) {
            if(is_array($document_srls)) $document_srls = implode(',',$document_srls);

            // DB에서 가져옴
            $args->document_srls = $document_srls;
            $output = executeQuery('document.getDocuments', $args);
            $document_list = $output->data;
            if(!$document_list) return;
            if(!is_array($document_list)) $document_list = array($document_list);

            $document_count = count($document_list);
            foreach($document_list as $key => $attribute) {
                if(!$attribute->document_srl) continue;
                $oDocument = null;
                $oDocument = new documentItem();
                $oDocument->setAttribute($attribute);
                if($is_admin) $oDocument->setGrant();

                $result[$attribute->document_srl] = $oDocument;
            }
            return $result;
        }

        /**
         * @brief module_srl값을 가지는 문서의 목록을 가져옴
         **/
        function getDocumentList($obj, $except_notice = false) {
            // 정렬 대상과 순서 체크 
            if(!in_array($obj->sort_index, array('list_order','regdate','last_update','update_order','readed_count','voted_count'))) $obj->sort_index = 'list_order'; 
            if(!in_array($obj->order_type, array('desc','asc'))) $obj->order_type = 'asc'; 

            // module_srl 대신 mid가 넘어왔을 경우는 직접 module_srl을 구해줌
            if($obj->mid) {
                $oModuleModel = &getModel('module');
                $obj->module_srl = $oModuleModel->getModuleSrlByMid($obj->mid);
                unset($obj->mid);
            }

            // 넘어온 module_srl은 array일 수도 있기에 array인지를 체크 
            if(is_array($obj->module_srl)) $args->module_srl = implode(',', $obj->module_srl);
            else $args->module_srl = $obj->module_srl;

            // 변수 체크
            $args->category_srl = $obj->category_srl?$obj->category_srl:null;
            $args->sort_index = $obj->sort_index;
            $args->order_type = $obj->order_type;
            $args->page = $obj->page?$obj->page:1;
            $args->list_count = $obj->list_count?$obj->list_count:20;
            $args->page_count = $obj->page_count?$obj->page_count:10;
            $args->start_date = $obj->start_date?$obj->start_date:null;
            $args->end_date = $obj->end_date?$obj->end_date:null;
            if($except_notice) $args->s_is_notice = 'N';

            $query_id = 'document.getDocumentList';

            // 검색 옵션 정리
            $search_target = $obj->search_target;
            $search_keyword = $obj->search_keyword;
            if($search_target && $search_keyword) {
                switch($search_target) {
                    case 'title' :
                            if($search_keyword) $search_keyword = str_replace(' ','%',$search_keyword);
                            $args->s_title = $search_keyword;
                        break;
                    case 'content' :
                            if($search_keyword) $search_keyword = str_replace(' ','%',$search_keyword);
                            $args->s_content = $search_keyword;
                        break;
                    case 'title_content' :
                            if($search_keyword) $search_keyword = str_replace(' ','%',$search_keyword);
                            $args->s_title = $search_keyword;
                            $args->s_content = $search_keyword;
                        break;
                    case 'user_id' :
                            if($search_keyword) $search_keyword = str_replace(' ','%',$search_keyword);
                            $args->s_user_id = $search_keyword;
                            $args->sort_index = 'documents.'.$args->sort_index;
                        break;
                    case 'member_srl' :
                            $args->s_member_srl = (int)$search_keyword;
                        break;
                    case 'user_name' :
                            if($search_keyword) $search_keyword = str_replace(' ','%',$search_keyword);
                            $args->s_user_name = $search_keyword;
                        break;
                    case 'nick_name' :
                            if($search_keyword) $search_keyword = str_replace(' ','%',$search_keyword);
                            $args->s_nick_name = $search_keyword;
                        break;
                    case 'email_address' :
                            if($search_keyword) $search_keyword = str_replace(' ','%',$search_keyword);
                            $args->s_email_address = $search_keyword;
                        break;
                    case 'homepage' :
                            if($search_keyword) $search_keyword = str_replace(' ','%',$search_keyword);
                            $args->s_homepage = $search_keyword;
                        break;
                    case 'is_notice' :
                            if($search_keyword=='Y') $args->s_is_notice = 'Y';
                            else $args->s_is_notice = '';
                        break;
                    case 'is_secret' :
                            if($search_keyword=='Y') $args->s_is_secret = 'Y';
                            else $args->s_is_secret = '';
                        break;
                    case 'tag' :
                            $args->s_tags = str_replace(' ','%',$search_keyword);
                            $query_id = 'document.getDocumentListWithinTag';
                        break;
                    case 'readed_count' :
                            $args->s_readed_count = (int)$search_keyword;
                        break;
                    case 'voted_count' :
                            $args->s_voted_count = (int)$search_keyword;
                        break;
                    case 'comment_count' :
                            $args->s_comment_count = (int)$search_keyword;
                        break;
                    case 'trackback_count' :
                            $args->s_trackback_count = (int)$search_keyword;
                        break;
                    case 'uploaded_count' :
                            $args->s_uploaded_count = (int)$search_keyword;
                        break;
                    case 'regdate' :
                            $args->s_regdate = $search_keyword;
                        break;
                    case 'last_update' :
                            $args->s_last_upate = $search_keyword;
                        break;
                    case 'ipaddress' :
                            $args->s_ipaddress= $search_keyword;
                        break;
                    case 'comment' :
                            $args->s_comment = $search_keyword;
                            $args->sort_index = 'documents.'.$args->sort_index;
                            $query_id = 'document.getDocumentListWithinComment';
                        break;
                    default :
                            preg_match('/^extra_vars([0-9]+)$/',$search_target,$matches);
                            if($matches[1]) {
                                $args->{"s_extra_vars".$matches[1]} = $search_keyword;
                            }
                        break;
                }
            }

            // document.getDocumentList 쿼리 실행
            $output = executeQueryArray($query_id, $args);

            // 결과가 없거나 오류 발생시 그냥 return
            if(!$output->toBool()||!count($output->data)) return $output;

            foreach($output->data as $key => $attribute) {
                $document_srl = $attribute->document_srl;

                $oDocument = null;
                $oDocument = new documentItem();
                $oDocument->setAttribute($attribute);
                if($is_admin) $oDocument->setGrant();

                $output->data[$key] = $oDocument;
            
            }
            return $output;
        }

        /**
         * @brief module_srl값을 가지는 문서의 공지사항만 가져옴
         **/
        function getNoticeList($obj) {
            $args->module_srl = $obj->module_srl;
            $args->category_srl = $obj->category_srl;
            $args->sort_index = 'list_order';
            $args->order_type = 'asc';

            $output = executeQueryArray('document.getNoticeList', $args);

            // 결과가 없거나 오류 발생시 그냥 return
            if(!$output->toBool()||!count($output->data)) return $output;

            foreach($output->data as $key => $attribute) {
                $document_srl = $attribute->document_srl;

                $oDocument = null;
                $oDocument = new documentItem();
                $oDocument->setAttribute($attribute);

                $output->data[$key] = $oDocument;
            
            }
            return $output;
        }

        /**
         * @brief module_srl에 해당하는 문서의 전체 갯수를 가져옴
         **/
        function getDocumentCount($module_srl, $search_obj = NULL) {
            // 검색 옵션 추가
            $args->module_srl = $module_srl;
            $args->s_title = $search_obj->s_title;
            $args->s_content = $search_obj->s_content;
            $args->s_user_name = $search_obj->s_user_name;
            $args->s_member_srl = $search_obj->s_member_srl;
            $args->s_ipaddress = $search_obj->s_ipaddress;
            $args->s_regdate = $search_obj->s_regdate;
            $args->category_srl = $search_obj->category_srl;

            $output = executeQuery('document.getDocumentCount', $args);

            // 전체 갯수를 return
            $total_count = $output->data->count;
            return (int)$total_count;
        }
        /**
         * @brief 해당 document의 page 가져오기, module_srl이 없으면 전체에서..
         **/
        function getDocumentPage($document_srl, $module_srl=0, $list_count) {
            // 변수 설정
            $args->document_srl = $document_srl;
            $args->module_srl = $module_srl;

            // 전체 갯수를 구한후 해당 글의 페이지를 검색
            $output = executeQuery('document.getDocumentPage', $args);
            $count = $output->data->count;
            $page = (int)(($count-1)/$list_count)+1;
            return $page;
        }

        /**
         * @brief 카테고리의 정보를 가져옴
         **/
        function getCategory($category_srl) {
            $args->category_srl = $category_srl;
            $output = executeQuery('document.getCategory', $args);

            $node = $output->data;
            if(!$node) return;

            if($node->group_srls) {
                $group_srls = explode(',',$node->group_srls);
                unset($node->group_srls);
                $node->group_srls = explode(',',$node->group_srls);
            } else {
                unset($node->group_srls);
                $node->group_srls = array();
            }
            return $node;
        }

        /**
         * @brief 특정 카테고리에 child가 있는지 체크
         **/
        function getCategoryChlidCount($category_srl) {
            $output = executeQuery('document.getChildCategoryCount');
            if($output->data->count > 0) return true;
            return false;
        }

        /**
         * @brief 특정 모듈의 카테고리 목록을 가져옴
         **/
        function getCategoryList($module_srl) {
            $args->module_srl = $module_srl;
            $args->sort_index = 'list_order';
            $output = executeQuery('document.getCategoryList', $args);

            $category_list = $output->data;

            if(!$category_list) return NULL;
            if(!is_array($category_list)) $category_list = array($category_list);

            $category_count = count($category_list);
            for($i=0;$i<$category_count;$i++) {
                $category_srl = $category_list[$i]->category_srl;
                $list[$category_srl] = $category_list[$i];
            }
            return $list;
        }

        /**
         * @brief 카테고리에 속한 문서의 갯수를 구함
         **/
        function getCategoryDocumentCount($category_srl) {
            $args->category_srl = $category_srl;
            $output = executeQuery('document.getCategoryDocumentCount', $args);
            return (int)$output->data->count;
        }

        /**
         * @brief 문서 category정보의 xml 캐시 파일을 return
         **/
        function getCategoryXmlFile($module_srl) {
            $xml_file = sprintf('files/cache/document_category/%s.xml.php',$module_srl);
            if(!file_exists($xml_file)) {
                $oDocumentController = &getController('document');
                $oDocumentController->makeCategoryXmlFile($module_srl);
            }
            return $xml_file;
        } 

        /**
         * @brief 월별 글 보관현황을 가져옴
         **/
        function getMonthlyArchivedList($obj) {
            if($obj->mid) {
                $oModuleModel = &getModel('module');
                $obj->module_srl = $oModuleModel->getModuleSrlByMid($obj->mid);
                unset($obj->mid);
            }

            // 넘어온 module_srl은 array일 수도 있기에 array인지를 체크 
            if(is_array($obj->module_srl)) $args->module_srl = implode(',', $obj->module_srl);
            else $args->module_srl = $obj->module_srl;

            $output = executeQuery('document.getMonthlyArchivedList', $args);
            if(!$output->toBool()||!$output->data) return $output;

            if(!is_array($output->data)) $output->data = array($output->data);

            return $output;
        }

        /**
         * @brief 특정달의 일별 글 현황을 가져옴
         **/
        function getDailyArchivedList($obj) {
            if($obj->mid) {
                $oModuleModel = &getModel('module');
                $obj->module_srl = $oModuleModel->getModuleSrlByMid($obj->mid);
                unset($obj->mid);
            }

            // 넘어온 module_srl은 array일 수도 있기에 array인지를 체크 
            if(is_array($obj->module_srl)) $args->module_srl = implode(',', $obj->module_srl);
            else $args->module_srl = $obj->module_srl;
            $args->regdate = $obj->regdate;

            $output = executeQuery('document.getDailyArchivedList', $args);
            if(!$output->toBool()) return $output;

            if(!is_array($output->data)) $output->data = array($output->data);

            return $output;
        }

        /**
         * @brief 특정 모듈의 분류를 구함
         **/
        function getDocumentCategories() {
            $module_srl = Context::get('module_srl');
            $categories= $this->getCategoryList($module_srl);
            if(!$categories) return;

            $output = '';
            foreach($categories as $category_srl => $category) {
                $output .= sprintf("%d,%s\n",$category_srl, $category->title);
            }
            $this->add('categories', $output);
        }

        /**
         * @brief 문서 설정 정보를 구함
         **/
        function getDocumentConfig() {
            if(!$GLOBLAS['__document_config__'])  {
                $oModuleModel = &getModel('module');
                $config = $oModuleModel->getModuleConfig('document');

                if(!$config->thumbnail_type) $config->thumbnail_type = 'crop';
                $GLOBLAS['__document_config__'] = $config;
            }

            return $GLOBLAS['__document_config__'];
        }
    }
?>
