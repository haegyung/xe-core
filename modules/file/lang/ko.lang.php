<?php
    /**
     * @file   modules/file/lang/ko.lang.php
     * @author zero <zero@nzeo.com>
     * @brief  첨부파일(file) 모듈의 기본 언어팩
     **/

    $lang->file_name = '파일이름';
    $lang->file_size = '파일크기';
    $lang->download_count = '다운로드 받은 수';
    $lang->status = '상태';
    $lang->is_valid = '유효';
    $lang->is_stand_by = '대기';
    $lang->file_list = '첨부 파일 목록';
    $lang->allowed_filesize = '허용 첨부 용량';
    $lang->allowed_filetypes = '허용 첨부 파일 확장자';

    $lang->about_allowed_filesize = '관리자를 제외한 사용자는 정하신 용량만 첨부할 수 있습니다';
    $lang->about_allowed_filetypes = '관리자를 제외한 사용자는 정하신 확장자만 첨부할 수 있습니다.<br />( *.jpg;*.gif; 와 같이 정하시면 됩니다)';

    $lang->cmd_delete_checked_file = '선택항목 삭제';
    $lang->cmd_move_to_document = '문서로 이동';
    $lang->cmd_download = '다운로드';

    $lang->msg_cart_is_null = '삭제할 파일을 선택해주세요';
    $lang->msg_checked_file_is_deleted = '%d개의 첨부파일이 삭제되었습니다';

    $lang->search_target_list = array(
        'filename' => '파일이름',
        'filesize' => '파일크기 (byte, 이상)',
        'download_count' => '다운로드 회수 (이상)',
        'regdate' => '등록일',
        'ipaddress' => 'IP 주소',
    );

?>
