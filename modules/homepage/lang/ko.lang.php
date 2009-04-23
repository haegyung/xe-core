<?php
    /**
     * @file   ko.lang.php
     * @author zero (zero@nzeo.com)
     * @brief  CafeXE(homepage) 모듈의 기본 언어팩
     **/

    $lang->cafe = 'CafeXE'; 
    $lang->cafe_id = "카페 접속 ID"; 
    $lang->cafe_title = 'Cafe 이름';
    $lang->cafe_description = 'Cafe 설명';
    $lang->cafe_banner = 'Cafe 배너이미지';
    $lang->module_type = '대상';
    $lang->board = '게시판';
    $lang->page = '페이지';
    $lang->module_id = '모듈 ID';
    $lang->item_group_grant = '보여줄 그룹';
    $lang->cafe_info = '카페 정보';
    $lang->cafe_admin = 'Cafe 관리자';
    $lang->do_selected_member = '선택된 회원을 : ';
    $lang->cafe_latest_documents = '카페 최신 글';
    $lang->cafe_latest_comments = '카페 최신 댓글';
    $lang->mycafe_list = '가입한 카페';
    $lang->cafe_creation_type = '카페 접속 방법';
    $lang->about_cafe_creation_type = '사용자들이 카페를 생성할때 카페 접속 방법을 정해야 합니다. Site ID는 http://기본주소/ID 로 접속 가능하고 Domain 접속은 입력하신 도메인의 2차 도메인(http://domain.mydomain.net) 으로 카페가 생성됩니다';

    $lang->default_layout = '기본 레이아웃';
    $lang->about_default_layout = '카페가 생성될때 설정될 기본 레이아웃을 지정할 수 있습니다';
    $lang->enable_change_layout = '레이아웃 변경';
    $lang->about_change_layout = '선택하시면 개별 카페에서 레이아웃 변경을 허용할 수 있습니다';
    $lang->allow_service = '허용 서비스';
    $lang->about_allow_service = '개별 카페에서 사용할 기본 서비스를 설정할 수 있습니다';

    $lang->cmd_make_cafe = '카페 생성';
    $lang->cmd_import = '가져오기';
    $lang->cmd_export = '내보내기';
    $lang->cafe_creation_privilege = '카페 생성 권한';

    $lang->cafe_main_mid = '카페 메인 ID';
    $lang->about_cafe_main_mid = '카페 메인 페이지를 http://주소/ID 값으로 접속하기 위한 ID값을 입력해주세요.';

    $lang->default_menus = array(
        'home' => '홈',
        'notice' => '공지사항',
        'levelup' => '등업신청',
        'freeboard' => '자유게시판',
        'view_total' => '전체 글 보기',
        'view_comment' => '한줄 이야기',
        'cafe_album' => '카페 앨범',
        'menu' => '메뉴',
        'default_group1' => '대기회원',
        'default_group2' => '준회원',
        'default_group3' => '정회원',
    );

    $lang->cmd_admin_menus = array(
        'dispHomepageManage' => 'Cafe 설정',
        'dispHomepageMemberGroupManage' => '회원그룹관리',
        'dispHomepageMemberManage' => '회원 목록',
        'dispHomepageTopMenu' => '메뉴 관리',
        "dispHomepageComponent" => "기능 설정",
        'dispHomepageCounter' => '접속 통계',
        'dispHomepageMidSetup' => '모듈 세부 설정',
    );
    $lang->cmd_cafe_registration = 'Cafe 생성';
    $lang->cmd_cafe_setup = 'Cafe 설정';
    $lang->cmd_cafe_delete = 'Cafe 삭제';
    $lang->cmd_go_home = '홈으로 이동';
    $lang->cmd_go_cafe_admin = 'Cafe 전체 관리';
    $lang->cmd_change_layout = '변경';
    $lang->cmd_select_index = '초기화면 선택';
    $lang->cmd_add_new_menu = '새로운 메뉴 추가';
    $lang->default_language = '기본 언어';
    $lang->about_default_language = '처음 접속하는 사용자의 언어 설정을 지정할 수 있습니다.';

    $lang->about_cafe_act = array(
        'dispHomepageManage' => 'Cafe의 모양을 꾸밀 수 있습니다',
        'dispHomepageMemberGroupManage' => 'Cafe 내에서 사용되는 그룹 관리를 할 수 있습니다',
        'dispHomepageMemberManage' => 'Cafe에 등록된 회원들을 보거나 관리할 수 있습니다',
        'dispHomepageTopMenu' => 'Cafe의 상단이나 좌측등에 나타나는 일반적인 메뉴를 수정하거나 추가할 수 있습니다',
        "dispHomepageComponent" => "에디터 컴포넌트/ 애드온을 활성화 하거나 설정을 변경할 수 있습니다",
        'dispHomepageCounter' => 'Cafe의 접속 현황을 볼 수 있습니다',
        'dispHomepageMidSetup' => 'Cafe에서 사용하는 게시판, 페이지등의 모듈 세부 설정을 할 수 있습니다',
    );
    $lang->about_cafe = 'Cafe 서비스 관리자는 다수의 Cafe를 만들 수 있고 또 각 Cafe를 편하게 설정할 수 있도록 합니다.';
    $lang->about_cafe_title = 'Cafe 이름은 관리를 위해서만 사용될 뿐 서비스에는 나타나지 않습니다';
    $lang->about_menu_names = 'Cafe에 나타날 메뉴 이름을 언어에 따라서 지정할 수 있습니다.<br/>하나만 입력하셔도 모두 같이 적용됩니다';
    $lang->about_menu_option = '메뉴를 선택시 새창으로 열지를 선택할 수 있습니다.<br />펼침 메뉴는 레이아웃에 따라 동작합니다';
    $lang->about_group_grant = '그룹을 선택하면 선택된 그룹만 메뉴가 보입니다.<br/>모두 해제하면 비회원도 볼 수 있습니다';
    $lang->about_module_type = '게시판,페이지는 모듈을 생성하고 URL은 링크만 합니다.<br/>생성후 수정할 수 없습니다';
    $lang->about_browser_title = '메뉴에 접속시 브라우저의 제목으로 나타날 내용입니다';
    $lang->about_module_id = '게시판,페이지등 접속할때 사용될 주소입니다.<br/>예) http://도메인/[모듈ID], http://도메인/?mid=[모듈ID]';
    $lang->about_menu_item_url = '대상을 URL로 할때 연결할 링크주소입니다.<br/>http://는 빼고 입력해주세요';
    $lang->about_menu_image_button = '메뉴명 대신 이미지로 메뉴를 사용할 수 있습니다.';
    $lang->about_cafe_delete = 'Cafe를 삭제하게 되면 연결되어 있는 모든 모듈(게시판,페이지등)과 그에 따른 글들이 삭제됩니다.<br />주의가 필요합니다';
    $lang->about_cafe_admin = 'Cafe 관리자를 설정할 수 있습니다.<br/>Cafe 관리자는 http://주소/?act=dispHomepageManage 로 관리자 페이지로 접속할 수 있으며 존재하지 않는 사용자는 관리자로 등록되지 않습니다';

    $lang->confirm_change_layout = '레이아웃을 변경할 경우 레이아웃 정보들 중 일부가 사라질 수가 있습니다. 변경하시겠습니까?';
    $lang->confirm_delete_menu_item = '메뉴 항목 삭제시 연결되어 있는 게시판이나 페이지 모듈도 같이 삭제가 됩니다. 그래도 삭제하시겠습니까?';
    $lang->msg_module_count_exceed = '허용된 모듈의 개수를 초과하였기에 생성할 수 없습니다';
    $lang->msg_not_enabled_id = '사용할 수 없는 아이디입니다';
    $lang->msg_same_site = '동일한 가상 사이트의 모듈은 이동할 수가 없습니다';
    $lang->about_move_module = '가상사이트와 기본사이트간의 모듈을 옮길 수 있습니다.<br/>다만 가상사이트끼리 모듈을 이동하거나 같은 이름의 mid가 있을 경우 예기치 않은 오류가 생길 수 있으니 꼭 가상 사이트와 기본 사이트간의 다른 이름을 가지는 모듈만 이동하세요';
?>
