<?php
    /**
     * @file   ko.lang.php
     * @author zero (zero@nzeo.com)
     * @brief  한국어 언어팩 (기본적인 내용만 수록)
     **/

    $lang->item_module = "모듈 목록";
    $lang->item_addon  = "애드온 목록";
    $lang->item_plugin = "플러그인 목록";
    $lang->item_layout = "레이아웃 목록";

    $lang->module_name = "모듈 이름";
    $lang->addon_name = "애드온 이름";
    $lang->version = "버전";
    $lang->author = "제작자";
    $lang->table_count = "테이블수";
    $lang->installed_path = "설치경로";

    $lang->msg_is_not_administrator = '관리자만 접속이 가능합니다';
    $lang->msg_manage_module_cannot_delete = '모듈, 애드온, 레이아웃, 플러그인 모듈의 바로가기는 삭제 불가능합니다';
    $lang->msg_default_act_is_null = '기본 관리자 Action이 지정되어 있지 않아 바로가기 등록을 할 수가 없습니다';

    // 관리자 메인 페이지
    $lang->welcome_to_zeroboard_xe = '제로보드XE 관리자 페이지입니다';

    $lang->zeroboard_xe_user_links = '사용자를 위한 링크';
    $lang->zeroboard_xe_developer_links = '개발자를 위한 링크';

    $lang->xe_user_links = array(
        '공식홈페이지' => 'http://www.zeroboard.com',
        '모듈 자료실' => 'http://www.zeroboard.com',
        '애드온 자료실' => 'http://www.zeroboard.com',
        '플러그인 자료실' => 'http://www.zeroboard.com',
        '모듈 스킨 자료실' => 'http://www.zeroboard.com',
        '플러그인 스킨 자료실' => 'http://www.zeroboard.com',
        '레이아웃 스킨 자료실' => 'http://www.zeroboard.com',
    );

    $lang->xe_developer_links = array(
        '개발자 포럼' => 'http://dev.zeroboard.com',
        '매뉴얼' => 'http://www.zeroboard.com/wiki/manual',
        '이슈트래킹' => 'http://trac.zeroboard.com',
        'SVN Repository' => 'http://svn.zeroboard.com',
        'doxygen document' => 'http://doc.zeroboard.com',
        'pdf 문서' => 'http://doc.zeroboard.com/zeroboard_xe.pdf',
    );

    $lang->zeroboard_xe_usefulness_module = '유용한 모듈들';
    $lang->xe_usefulness_modules = array(
        'dispEditorAdminIndex' => '에디터 관리',
        'dispDocumentAdminList' => '문서 관리',
        'dispCommentAdminList' => '댓글 관리',
        'dispFileAdminList' => '첨부파일 관리',
        'dispPollAdminList' => '설문조사 관리',
        'dispSpamfilterAdminConfig' => '스팸필터 관리',
        'dispCounterAdminIndex' => '카운터 로그',

    );

    $lang->xe_license = '제로보드XE는 GPL을 따릅니다';
?>
