<?php
    /**
     * @class rss_reader
     * @author Simulz (k10206@naver.com)
     * @brief RSS Reader
     **/

    set_include_path("./libs/PEAR");
    require_once('PEAR.php');
    require_once('HTTP/Request.php');

    class rss_reader extends WidgetHandler {
        /**
         * @brief 위젯의 실행 부분
         *
         * ./widgets/위젯/conf/info.xml 에 선언한 extra_vars를 args로 받는다
         * 결과를 만든후 print가 아니라 return 해주어야 한다
         **/
        function proc($args) {
            // 위젯 자체적으로 설정한 변수들을 체크
            $title = $args->title;
            $PAGE_LIMIT = $args->page_limit ? $args->page_limit : 10;

            // 날짜 형태
            $DATE_FORMAT = $args->date_format ? $args->date_format : "Y-m-d H:i:s";

            $URL_parsed = parse_url($args->rss_url); 

            $host = $URL_parsed["host"]; 
            $port = $URL_parsed["port"]; 

            if ($port == 0) $port = 80; 

            $path = $URL_parsed["path"]; 

            if ($URL_parsed["query"] != "") $path .= "?".$URL_parsed["query"]; 

            $oReqeust = new HTTP_Request($args->rss_url);
            $oReqeust->addHeader('Content-Type', 'application/xml');
            $oReqeust->setMethod('GET');

            $user = $URL_parsed["user"];
            $pass = $URL_parsed["pass"];

            if($user) $oReqeust->setBasicAuth($user, $pass);

            $oResponse = $oReqeust->sendRequest();
            if (PEAR::isError($oResponse)) {
                return new Object(-1, 'msg_fail_to_request_open');
            }
            $buff = $oReqeust->getResponseBody();
            $encoding = preg_match("/<\?xml.*encoding=\"(.+)\".*\?>/i", $buff, $matches);
            if($encoding && !preg_match("/UTF-8/i", $matches[1])) $buff = trim(iconv($matches[1]=="ks_c_5601-1987"?"EUC-KR":$matches[1], "UTF-8", $buff));

            $buff = preg_replace("/<\?xml.*\?>/i", "", $buff);

            $oXmlParser = new XmlParser();
            $xml_doc = $oXmlParser->parse($buff);

            $rss->title = $xml_doc->rss->channel->title->body;
            $rss->link = $xml_doc->rss->channel->link->body;

            $items = $xml_doc->rss->channel->item;
            if(!$items) return; 
            if($items && !is_array($items)) $items = array($items);

            $rss_list = array();

            foreach ($items as $key => $value) {
                if($key >= $PAGE_LIMIT) break;
                unset($item);

                foreach($value as $key2 => $value2) {
                    if(is_array($value2)) $value2 = array_shift($value2);
                    $item->{$key2} = $value2->body;
                }

                $date = $item->pubdate;
                $item->date = date($DATE_FORMAT, strtotime($date));
                $array_date[$key] = strtotime($date);

                $item->description = preg_replace('!<a href=!is','<a onclick="window.open(this.href);return false" href=', $item->description);

                $rss_list[$key] = $item;
            }
            array_multisort($array_date, SORT_DESC, $rss_list);


            $widget_info->rss = $rss;
            $widget_info->rss_list = $rss_list;
            $widget_info->title = $title;
            $widget_info->rss_height = $args->rss_height ? $args->rss_height : 200;

            Context::set('widget_info', $widget_info);

            // 템플릿의 스킨 경로를 지정 (skin, colorset에 따른 값을 설정)
            $tpl_path = sprintf('%sskins/%s', $this->widget_path, $args->skin);
            Context::set('colorset', $args->colorset);

            // 템플릿 컴파일
            $oTemplate = &TemplateHandler::getInstance();
            $output = $oTemplate->compile($tpl_path, 'list');
            return $output;
        }
    }
?>