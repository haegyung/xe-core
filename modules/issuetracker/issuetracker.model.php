<?php
    /**
     * @class  issuetrackerModel
     * @author haneul (zero@nzeo.com)
     * @brief  issuetracker 모듈의 model class
     **/

    require_once(_XE_PATH_.'modules/issuetracker/issuetracker.item.php');

    function _compare($a, $b)
    {
        if(!$a->date || !$b->date) return 0;
        return strcmp($a->date, $b->date) * -1;
    }


    class issuetrackerModel extends issuetracker {
        var $oSvn = null;

        function init() {
        }

        function &getProjectInfo($module_srl) {
            static $projectInfo = array();
            if(!isset($projectInfo[$module_srl])) {
                $projectInfo[$module_srl]->milestones = $this->getList($module_srl, 'Milestones');
                $projectInfo[$module_srl]->priorities = $this->getList($module_srl, 'Priorities');
                $projectInfo[$module_srl]->types = $this->getList($module_srl, 'Types');
                $projectInfo[$module_srl]->components = $this->getList($module_srl, 'Components');
                $projectInfo[$module_srl]->packages = $this->getList($module_srl, 'Packages');
                $projectInfo[$module_srl]->releases = $this->getModuleReleases($module_srl);
            }
            return $projectInfo[$module_srl];
        }

        function getIssue($document_srl=0, $is_admin = false) {
            if(!$document_srl) return new issueItem();

            if(!$GLOBALS['__IssueItem__'][$document_srl]) {
                $oIssue = new issueItem($document_srl);
                if($is_admin) $oIssue->setGrant();
                $GLOBALS['__IssueItem__'][$document_srl] = $oIssue;
            }

            return $GLOBALS['__IssueItem__'][$document_srl];
        }

        function getIssuesCount($module_srl,$target, $value, $status = null) {
            $args->module_srl = $module_srl;
            $args->{$target} = $value;
            if($status !== null) $args->status = $status;
            $output = executeQuery('issuetracker.getIssuesCount', $args);
            if(!$output->toBool() || !$output->data) return -1;
            return $output->data->count;
        }

        function getHistoryCount($target_srl) {
            $args->target_srl = $target_srl;
            $output = executeQuery('issuetracker.getHistoryCount', $args);
            if(!$output->toBool() || !$output->data) return 0;
            return $output->data->count;
        }

        function getIssueList($args) {
            // 기본으로 사용할 query id 지정 (몇가지 검색 옵션에 따라 query id가 변경됨)
            $query_id = 'issuetracker.getIssueList';

            // 검색 옵션 정리
            if($args->search_target && $args->search_keyword) {
                switch($args->search_target) {
                    case 'title' :
                    case 'content' :
                            if($args->search_keyword) $args->search_keyword = str_replace(' ','%',$args->search_keyword);
                            $args->{"s_".$args->search_target} = $args->search_keyword;
                        break;
                    case 'title_content' :
                            if($args->search_keyword) $args->search_keyword = str_replace(' ','%',$args->search_keyword);
                            $args->s_title = $args->search_keyword;
                            $args->s_content = $args->search_keyword;
                        break;
                    case 'user_id' :
                            if($args->search_keyword) $args->search_keyword = str_replace(' ','%',$args->search_keyword);
                            $args->s_user_id = $args->search_keyword;
                        break;
                    case 'user_name' :
                    case 'nick_name' :
                            $args->{"s_".$args->search_target} = $args->search_keyword;
                        break;
                    case 'member_srl' :
                            $args->{"s_".$args->search_target} = (int)$args->search_keyword;
                        break;
                    case 'tag' :
                            $args->s_tags = str_replace(' ','%',$args->search_keyword);
                            $query_id = 'issuetracker.getIssueListWithinTag';
                        break;
                    default :
                            preg_match('/^extra_vars([0-9]+)$/',$args->search_target,$matches);
                            if($matches[1]) {
                                $query_id = 'issuetracker.getIssueListWithExtraVars';
                                $args->var_idx = $matches[1];
                                $args->var_value = str_replace(' ','%',$args->search_keyword);
                            }
                        break;
                }
            }

            if(in_array($query_id, array('issuetracker.getIssueListWithinTag'))) {
                $group_args = clone($args);
                $group_output = executeQueryArray($query_id, $group_args);
                if(!$group_output->toBool()||!count($group_output->data)) return $output;

                foreach($group_output->data as $key => $val) {
                    if($val->document_srl) {
                        $target_srls[$key] = $val->document_srl;
                        $order_srls[$val->document_srl] = $key;
                    }
                }

                $target_args->target_srl = implode(',',$target_srls);
                $output = executeQueryArray('issuetracker.getIssues', $target_args);
                if($output->toBool() && count($output->data)) {
                    $data = $output->data;
                    $output->data = array();
                    foreach($data as $key => $val) {
                        $output->data[$order_srls[$val->document_srl]] = $val;
                    }
                    $output->total_count = $group_output->data->total_count;
                    $output->total_page = $group_output->data->total_page;
                    $output->page = $group_output->data->page;
                }
            } else {
                $output = executeQueryArray($query_id, $args);
            }

            // 결과가 없거나 오류 발생시 그냥 return
            if(!$output->toBool()||!count($output->data)) return $output;

            $idx = 0;
            $data = $output->data;
            unset($output->data);

            $keys = array_keys($data);
            $virtual_number = $keys[0];

            foreach($data as $key => $attribute) {
                $document_srl = $attribute->document_srl;
                $oIssue = null;
                $oIssue = new issueItem();
                $oIssue->setAttribute($attribute);
                $oIssue->setProjectInfo($attribute);
                if($is_admin) $oIssue->setGrant();

                $output->data[$virtual_number] = $oIssue;
                $virtual_number --;
            }

            return $output;
        }

        function getList($module_srl, $listname) 
        {
            if(!$module_srl) return array();

            $args->module_srl = $module_srl;
            $output = executeQueryArray("issuetracker.get".$listname, $args);
            if(!$output->toBool() || !$output->data) return array();
            return $output->data;
        }

        function getHistories($target_srl) {
            $args->target_srl = $target_srl;
            $output = executeQueryArray('issuetracker.getHistories', $args);
            $histories = $output->data;
            $cnt = count($histories);
            for($i=0;$i<$cnt;$i++) {
                $history = unserialize($histories[$i]->history);
                if($history && count($history)) {
                    $h = array();
                    foreach($history as $key => $val) {
                        if($val[0]) $str = Context::getLang('history_format');
                        else $str = Context::getLang('history_format_not_source');
                        $str = str_replace('[source]', $val[0], $str);
                        $str = str_replace('[target]', $val[1], $str);
                        $str = str_replace('[key]', Context::getLang($key), $str);
                        $h[] = $str;
                    }
                    $histories[$i]->history = $h;
                } else {
                    $histories[$i]->history = null;
                }

                preg_match_all('/r([0-9]+)/',$histories[$i]->content, $mat);
                for($k=0;$k<count($mat[1]);$k++) {
                    $histories[$i]->content = str_replace('r'.$mat[1][$k], sprintf('<a href="%s" onclick="window.open(this.href); return false;">%s</a>',getUrl('','mid',Context::get('mid'),'act','dispIssuetrackerViewSource','type','compare','erev',$mat[1][$k],'brev',''), 'r'.$mat[1][$k]), $histories[$i]->content);
                }
                preg_match_all('/\[([0-9]+)\]/',$histories[$i]->content, $mat);
                for($k=0;$k<count($mat[1]);$k++) {
                    $histories[$i]->content = str_replace('['.$mat[1][$k].']', sprintf('<a href="%s" onclick="window.open(this.href); return false;">%s</a>',getUrl('','mid',Context::get('mid'),'act','dispIssuetrackerViewSource','type','compare','erev',$mat[1][$k],'brev',''), '['.$mat[1][$k].']'), $histories[$i]->content);
                }
            }
            return $histories;
        }

        function getPackageList($module_srl, $package_srl=0, $each_releases_count = 0) 
        {
            if(!$module_srl) return array();

            if(!$package_srl) {
                $args->module_srl = $module_srl;
                $output = executeQueryArray("issuetracker.getPackages", $args);
            } else {
                $args->package_srl = $package_srl;
                $output = executeQueryArray("issuetracker.getPackages", $args);
            }
            if(!$output->toBool() || !$output->data) return array();

            $packages = array();
            foreach($output->data as $package) {
                $package->release_count = $this->getReleaseCount($package->package_srl);
                $package->releases = $this->getReleaseList($package->package_srl, $each_releases_count);
                $packages[$package->package_srl] = $package;
            }

            return $packages;
        }

        function getReleaseCount($package_srl) {
            if(!$package_srl) return 0;

            $args->package_srl = $package_srl;
            $output = executeQuery("issuetracker.getReleaseCount", $args);
            return $output->data->count;
        }

        function getModuleReleases($module_srl) {
            if(!$module_srl) return array();

            $args->module_srl = $module_srl;
            $output = executeQueryArray("issuetracker.getReleases", $args);
            if(!$output->toBool() || !$output->data) return array();
            return $output->data;
        }

        function getReleasesWithPackageTitle($module_srl) {
            if(!$module_srl) return array();
            $args->module_srl = $module_srl;
            $output = executeQueryArray("issuetracker.getReleasesWithPackage", $args);
            if(!$output->toBool() || !$output->data) return array();
            return $output->data;
        }

        function getReleaseList($package_srl, $list_count =0) {
            if(!$package_srl) return array();

            $args->package_srl = $package_srl;

            if($list_count ) {
                $args->list_count = $list_count;
                $output = executeQueryArray("issuetracker.getReleaseList", $args);
            } else {
                $output = executeQueryArray("issuetracker.getReleases", $args);
            }
            if(!$output->toBool() || !$output->data) return array();

            $list = $output->data;
            $output = array();
            $oFileModel = &getModel('file');
            foreach($list as $release) {
                $files = $oFileModel->getFiles($release->release_srl);
                $release->files = $files;
                $output[$release->release_srl] = $release;
            }
            return $output;
        }

        function getPriorityCount($module_srl)
        {
            if(!$module_srl) return -1;
            $args->module_srl = $module_srl;
            $output = executeQuery("issuetracker.getPriorityCount", $args);
            if(!$output->toBool()) return -1;
            else return $output->data->count;
        }

        function getPriorityMaxListorder($module_srl)
        {
            if(!$module_srl) return -1;
            $args->module_srl = $module_srl;
            $output = executeQuery("issuetracker.getPriorityMaxListorder", $args);
            if(!$output->toBool()) return -1;
            else return $output->data->count;
        }

        function getMilestone($milestone_srl)
        {
            $args->milestone_srl = $milestone_srl;
            $output = executeQuery("issuetracker.getMilestone", $args);
            return $output;
        }

        function getCompletedMilestone($module_srl)
        {
            $args->module_srl = $module_srl;
            $args->is_completed = 'Y';
            $output = executeQueryArray("issuetracker.getMilestones", $args);
            if(!$output->toBool())
            {
                return array();
            }

            if(!$output->data)
            {
                return array();
            }
            return $output->data;
        }

        function getPriority($priority_srl)
        {
            $args->priority_srl = $priority_srl;
            $output = executeQuery("issuetracker.getPriority", $args);
            return $output;
        }

        function getType($type_srl)
        {
            $args->type_srl = $type_srl;
            $output = executeQuery("issuetracker.getType", $args);
            return $output;
        }

        function getComponent($component_srl)
        {
            $args->component_srl = $component_srl;
            $output = executeQuery("issuetracker.getComponent", $args);
            return $output;
        }

        function getPackage($package_srl)
        {
            $args->package_srl = $package_srl;
            $output = executeQuery("issuetracker.getPackage", $args);
            if(!$output->toBool()||!$output->data) return;
            return $output->data;
        }

        function getRelease($release_srl)
        {
            $args->release_srl = $release_srl;
            $output = executeQuery("issuetracker.getRelease", $args);
            if(!$output->toBool()||!$output->data) return;
            $release = $output->data;
            $oFileModel = &getModel('file');
            $files = $oFileModel->getFiles($release->release_srl);
            if($files) $release->files = $files;
            return $release;
        }

        function getGroupMembers($module_srl, $grant_name) {
            $args->module_srl = $module_srl;
            $args->name = $grant_name;
            $output = executeQueryArray('issuetracker.getGroupMembers', $args);
            return $output->data;
        }

        function getLatestRevision($module_srl) {
            $args->module_srl = $module_srl;
            $output = executeQuery('issuetracker.getLatestRevision', $args);
            if($output->data && $output->data->revision)
            {
                return $output->data->revision;
            }
            else return 0;
        }

        function _linkDocument($matches) {
            $document_srl = $matches[1];
            return sprintf('<a href="%s" onclick="window.open(this.href); return false;">#%d</a>', getUrl('','document_srl',$document_srl), $document_srl);
        }

        function _linkXE($message)
        {
            return preg_replace_callback('/^\#?([0-9]+)( |\:)/', array($this, '_linkDocument'), $message);
        }


        function getChangesets($module_srl, $enddate = null, $limit = 10, $targets)
        {
            if(!$enddate)
            {
                $enddate = date("Ymd");
            }
            $args->enddate = date("Ymd", ztime($enddate)+24*60*60);
            $args->startdate = date("Ymd", ztime($enddate)-24*60*60*$limit);
            $args->module_srl = $module_srl;
            if(in_array('commit', $targets))
            {
                $output = executeQueryArray("issuetracker.getChangesets", $args);
                if(!$output->toBool())
                {
                    return array();
                }
            }
            if(!$output->data)
            {
                $output->data = array();
            }
            foreach($output->data as $key => $changeset)
            {
                $changeset->message = $this->_linkXE($changeset->message);
            }

            if(in_array('issue_changed', $targets))
            {
                $solvedHistory = array();
                $output2 = executeQueryArray("issuetracker.getHistories", $args);
                if(count($output2->data)) {
                    foreach($output2->data as $history)
                    {
                        $hist = unserialize($history->history);
                        $h = array();
                        if(!is_array($hist)) continue;
                        $res = "";
                        $bFirst = true;
                        foreach($hist as $key => $val) {
                            if($bFirst) { $bFirst = false; }
                            else { $res .= "<br />"; }
                            if($val[0]) $str = Context::getLang('history_format');
                            else $str = Context::getLang('history_format_not_source');
                            $str = str_replace('[source]', $val[0], $str);
                            $str = str_replace('[target]', $val[1], $str);
                            $str = str_replace('[key]', Context::getLang($key), $str);
                            $res .= $str;
                        }
                        $obj = null;
                        $obj->date = $history->regdate;
                        $obj->type = "changed";
                        $obj->message = $res;
                        $obj->target_srl = $history->target_srl;
                        $obj->author = $history->nick_name;
                        $output->data[] = $obj;
                    }
                }
            }

            if(in_array('issue_created', $targets))
            {
                $output2 = executeQueryArray("issuetracker.getDocumentListForChangeset", $args);
                if(count($output2->data)) {
                    foreach($output2->data as $history)
                    {
                        $obj = null;
                        $obj->date = $history->regdate;
                        $obj->type = "created";
                        $obj->author = $history->nick_name;
                        $obj->target_srl = $history->document_srl;
                        $output->data[] = $obj;
                    }
                }
            }

            usort($output->data, _compare);

            return $output->data;
        }
    }
?>
