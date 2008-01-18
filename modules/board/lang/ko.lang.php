<?php
    /**
     * @file   ko.lang.php
     * @author zero (zero@nzeo.com)
     * @brief  게시판(board) 모듈의 기본 언어팩
     **/

    $lang->board = "게시판"; 

    $lang->except_notice = "공지사항 제외";

    $lang->cmd_manage_menu = '메뉴관리';
    $lang->cmd_make_child = '하위 카테고리 추가';
    $lang->cmd_enable_move_category = "카테고리 위치 변경 (선택후 위 메뉴를 드래그하세요)";

    // 항목
    $lang->parent_category_title = '상위 카테고리명';
    $lang->category_title = '분류명';
    $lang->expand = '펼침';
    $lang->category_group_srls = '그룹제한';
    $lang->search_result = '검색결과';

    // 버튼에 사용되는 언어
    $lang->cmd_board_list = '게시판 목록';
    $lang->cmd_module_config = '게시판 공통 설정';
    $lang->cmd_view_info = '게시판 정보';

    // 주절 주절..
    $lang->about_category_title = '카테고리 이름을 입력해주세요';
    $lang->about_expand = '선택하시면 늘 펼쳐진 상태로 있게 합니다';
    $lang->about_category_group_srls = '선택하신 그룹만 현재 카테고리가 보이게 됩니다. (xml파일을 직접 열람하면 노출이 됩니다)';
    $lang->about_layout_setup = '블로그의 레이아웃 코드를 직접 수정할 수 있습니다. 위젯 코드를 원하는 곳에 삽입하시거나 관리하세요';
    $lang->about_board_category = '분류를 만드실 수 있습니다.<br />분류가 오동작을 할 경우 캐시파일 재생성을 수동으로 해주시면 해결이 될 수 있습니다.';
    $lang->about_except_notice = "목록 상단에 늘 나타나는 공지사항을 일반 목록에서 공지사항을 출력하지 않도록 합니다.";
    $lang->about_board = "게시판을 생성하고 관리할 수 있는 게시판 모듈입니다.\n생성하신 후 목록에서 모듈이름을 선택하시면 자세한 설정이 가능합니다.\n게시판의 모듈이름은 접속 url이 되므로 신중하게 입력해주세요. (ex : http://도메인/zb/?mid=모듈이름)";
?>
