<?php
/**
  * @class  planetWAP
  * @author misol (misol@korea.ac.kr)
  * @brief  planet ����� WAP class
  * WML, mHTML �� ����� ������ ������ ���(����Ʈ�� ����^^)
  **/

class planetWAP extends planet {
    function procWAP(&$oMobile) {
        $content = '';

        // �÷����� �⺻ ������ ��¥�� �̸� ��� ��⿡ �ִ� �����ε�... �ֵ���� ��⺸�� ���� ����ȴ�;
        $last_date = $this->planet->getContentLastDay();
        $date = Context::get('date');
        if(!$date || $date > $last_date) $date = $last_date;
        Context::set('date', $date);
        Context::set('prev_date', $this->planet->getPrevDate($date));
        Context::set('next_date', $this->planet->getNextDate($date));

        $type = Context::get('type');
        if(!$type) $type = 'all';
        Context::set('type',$type);
        $tagtab = null;

        switch($type) {
            case 'wantyou':
                    $sort_index = 'documents.voted_count';
                    $order = 'desc';
                break;
            case 'best':
                    $sort_index = 'documents.comment_count';
                    $order = 'desc';
                break;

            case 'all':
                    $sort_index = 'documents.list_order';
                    $order = 'asc';
                break;
        }

        $page = Context::get('page');
        $oPlanetModel = &getModel('planet');

        $output = $oPlanetModel->getNewestContentList(null, $date, $page, 9, $sort_index, $order,$tagtab );

        $title = Context::getBrowserTitle().' ['.zdate($date,'Y').Context::getLang('unit_year').
                 zdate($date,'m').Context::getLang('unit_month').
                 zdate($date,'d').Context::getLang('unit_day').']';

        // ��� ���� �� ���
        if($this->act == 'dispPlanetContentCommentList') {
            $page = Context::get('page');
            $document_srl = Context::get('document_srl');
            $oPlanetModel = &getModel('planet');
            $output = $oPlanetModel->getReplyList($document_srl,$page);
            $reply_list = $output->data;

            $title .= ' - '.Context::getLang('comment');
            if(is_array($reply_list)) {
                foreach($reply_list as $key => $reply) {
                    $content .= '[<strong>'.$reply->nick_name.'</strong>] ';
                    $content .= $reply->content;
                }
            }

            // ���� �������� ������� ���ư���� ����
            $oMobile->setUpperUrl( getUrl('act',''), Context::getLang('cmd_go_upper') );

        } else {
            if($output->page_navigation->total_page>1) {
                if($output->page_navigation->cur_page < $output->page_navigation->last_page) {
                    // next/prevUrl ����
                    $oMobile->setPrevUrl(getUrl('page',$output->page_navigation->cur_page+1), sprintf('%s (%d/%d)', Context::getLang('cmd_prev'), $output->page_navigation->cur_page+1, $output->page_navigation->total_page));
                }
                if($output->page_navigation->cur_page > 1) $oMobile->setNextUrl(getUrl('page',$output->page_navigation->cur_page-1), sprintf('%s (%d/%d)', Context::getLang('cmd_next'), $output->page_navigation->cur_page-1, $output->page_navigation->total_page));
            }

        if(!$output->data || !count($output->data)) $content .= Context::getLang('no_documents');


            foreach($output->data as $no => $item) {
                $obj = null;
                $obj['href'] = getUrl('mid',$_GET['mid'],'document_srl',$item->get('document_srl'), 'act', 'dispPlanetContentCommentList');
                $obj['link'] = $item->get('document_srl').'['.htmlspecialchars($item->getNickName()).'] '."\n";
                $obj['link'] .= htmlspecialchars(strip_tags($item->getContent()));
                if($item->getPostScript()) $obj['extra'] = Context::getLang('planet_postscript').htmlspecialchars($item->getPostScript());
                if(is_array($item->getArrTags())) {
                    $obj['extra'] .= '<br />TAG:';
                    $obj['extra'] .= implode(', ',$item->getArrTags());
                }
                $obj['link'] = $obj['text'] = $obj['link'];
                $childs[] = $obj;
            }
            $oMobile->setChilds($childs); 


            $prev_date = Context::get('prev_date');
            if($prev_date) $oMobile->setEtcBtn(getUrl('date',$prev_date, 'document_srl',''), '<');
            $next_date = Context::get('next_date');
            if($next_date) $oMobile->setEtcBtn(getUrl('date',$next_date, 'document_srl',''), '>');
        }
        $oMobile->setTitle($title);
        $oMobile->setContent($content);
    }


}