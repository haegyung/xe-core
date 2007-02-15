<?php
    /**
     * @class  memberController
     * @author zero (zero@nzeo.com)
     * @biref  기본 모듈중의 하나인 member module
     **/

    class memberController extends Module {

        /**
         * @brief 초기화
         **/
        function init() {
        }

        /**
         * @brief user_id, password를 체크하여 로그인 시킴
         **/
        function doLogin($user_id, $password) {
            // 변수 정리
            $user_id = trim($user_id);
            $password = trim($password);

            // 이메일 주소나 비밀번호가 없을때 오류 return
            if(!$user_id) return new Object(-1,'null_user_id');
            if(!$password) return new Object(-1,'null_password');

            // DB 객체 생성
            $oDB = &DB::getInstance();

            // user_id 에 따른 정보 가져옴
            $args->user_id = $user_id;
            $member_info = $this->getMemberInfo($user_id, false);

            // return 값이 없거나 비밀번호가 틀릴 경우
            if($member_info->user_id != $user_id) return new Object(-1, 'invalid_user_id');
            if($member_info->password != md5($password)) return new Object(-1, 'invalid_password');

            // 로그인 처리
            $_SESSION['is_logged'] = true;
            $_SESSION['ipaddress'] = $_SERVER['REMOTE_ADDR'];

            unset($member_info->password);

            // 세션에 로그인 사용자 정보 저장
            $_SESSION['member_srl'] = $member_info->member_srl;
            $_SESSION['logged_info'] = $member_info;

            // 사용자 정보의 최근 로그인 시간을 기록
            $args->member_srl = $member_info->member_srl;
            $output = $oDB->executeQuery('member.updateLastLogin', $args);

            return $output;
        }

        /**
         * @brief 로그아웃
         **/
        function doLogout() {
            $_SESSION['is_logged'] = false;
            $_SESSION['ipaddress'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['logged_info'] = NULL;
            return new Object();
        }

        /**
         * @brief 관리자를 추가한다
         **/
        function insertAdmin($args) {
            $args->is_admin = 'Y';
            return $this->insertMember($args);
        }

        /**
         * @brief member 테이블에 사용자 추가
         **/
        function insertMember($args) {
            // 필수 변수들의 조절
            if($args->allow_mailing!='Y') $args->allow_mailing = 'N';
            if($args->denied!='Y') $args->denied = 'N';
            if($args->is_admin!='Y') $args->is_admin = 'N';
            list($args->email_id, $args->email_host) = explode('@', $args->email_address);

            // 모델 객체 생성
            $oMemberModel = getModule('member','model');

            // 금지 아이디인지 체크
            if($oMemberModel->isDeniedID($args->user_id)) return new Object(-1,'denied_user_id');

            // 아이디, 닉네임, email address 의 중복 체크
            $member_srl = $oMemberModel->getMemberSrlByUserID($args->user_id);
            if($member_srl) return new Object(-1,'msg_exists_user_id');

            $member_srl = $oMemberModel->getMemberSrlByNickName($args->nick_name);
            if($member_srl) return new Object(-1,'msg_exists_nick_name');

            $member_srl = $oMemberModel->getMemberSrlByEmailAddress($args->email_address);
            if($member_srl) return new Object(-1,'msg_exists_email_address');

            // DB 객체 생성
            $oDB = &DB::getInstance();

            // DB에 입력
            $args->member_srl = $oDB->getNextSequence();
            if($args->password) $args->password = md5($args->password);
            else unset($args->password);

            $output = $oDB->executeQuery('member.insertMember', $args);
            if(!$output->toBool()) return $output;

            // 기본 그룹을 입력
            $default_group = $oMemberModel->getDefaultGroup();

            // 기본 그룹에 추가
            $output = $this->addMemberToGroup($args->member_srl,$default_group->group_srl);
            if(!$output->toBool()) return $output;

            $output->add('member_srl', $args->member_srl);
            return $output;
        }

        /**
         * @brief member 정보 수정
         **/
        function updateMember($args) {
            // 모델 객체 생성
            $oMemberModel = getModule('member','model');

            // 수정하려는 대상의 원래 정보 가져오기
            $member_info = $oMemberModel->getMemberInfoByMemberSrl($args->member_srl);

            // 필수 변수들의 조절
            if($args->allow_mailing!='Y') $args->is_default = 'N';
            if($args->denied!='Y') $args->denied = 'N';
            if($args->is_admin!='Y') $args->use_category = 'N';
            list($args->email_id, $args->email_host) = explode('@', $args->email_address);

            // 아이디, 닉네임, email address 의 중복 체크
            $member_srl = $oMemberModel->getMemberSrlByUserID($args->user_id);
            if($member_srl&&$args->member_srl!=$member_srl) return new Object(-1,'msg_exists_user_id');

            $member_srl = $oMemberModel->getMemberSrlByNickName($args->nick_name);
            if($member_srl&&$args->member_srl!=$member_srl) return new Object(-1,'msg_exists_nick_name');

            $member_srl = $oMemberModel->getMemberSrlByEmailAddress($args->email_address);
            if($member_srl&&$args->member_srl!=$member_srl) return new Object(-1,'msg_exists_email_address');

            // DB 객체 생성
            $oDB = &DB::getInstance();

            // DB에 update
            if($args->password) $args->password = md5($args->password);
            else $args->password = $member_info->password;

            $output = $oDB->executeQuery('member.updateMember', $args);
            if(!$output->toBool()) return $output;

            // 그룹에 추가
            $output = $oDB->executeQuery('member.deleteMemberGroupMember', $args);
            if(!$output->toBool()) return $output;

            $group_srl_list = explode(',', $args->group_srl_list);
            for($i=0;$i<count($group_srl_list);$i++) {
                $output = $this->addMemberToGroup($args->member_srl,$group_srl_list[$i]);

                if(!$output->toBool()) return $output;
            }

            $output->add('member_srl', $args->member_srl);
            return $output;
        }

        /**
         * @brief 사용자 삭제
         **/
        function deleteMember($member_srl) {

            // 모델 객체 생성
            $oMemberModel = getModule('member','model');

            // 해당 사용자의 정보를 가져옴
            $member_info = $oMemberModel->getMemberInfoByMemberSrl($member_srl);
            if(!$member_info) return new Object(-1, 'msg_not_exists_member');

            // 관리자의 경우 삭제 불가능
            if($member_info->is_admin == 'Y') return new Object(-1, 'msg_cannot_delete_admin');

            // DB 객체 생성
            $oDB = &DB::getInstance();

            // member_group_member에서 해당 항목들 삭제
            $args->member_srl = $member_srl;
            $output = $oDB->executeQuery('member.deleteMemberGroupMember', $args);
            if(!$output->toBool()) return $output;

            // member 테이블에서 삭제
            return $oDB->executeQuery('member.deleteMember', $args);
        }

        /**
         * @brief member_srl에 group_srl을 추가
         **/
        function addMemberToGroup($member_srl,$group_srl) {
            $args->member_srl = $member_srl;
            $args->group_srl = $group_srl;

            // DB 객체 생성
            $oDB = &DB::getInstance();

            // 추가
            return  $oDB->executeQuery('member.addMemberToGroup',$args);
        }

        /**
         * @brief 회원의 그룹값을 변경
         **/
        function changeGroup($source_group_srl, $target_group_srl) {
            // DB객체 생성
            $oDB = &DB::getInstance();

            $args->source_group_srl = $source_group_srl;
            $args->target_group_srl = $target_group_srl;

            return $oDB->executeQuery('member.changeGroup', $args);
        }

        /**
         * @brief 그룹 등록
         **/
        function insertGroup($args) {
            $oDB = &DB::getInstance();

            // is_default값을 체크, Y일 경우 일단 모든 is_default에 대해서 N 처리
            if($args->is_default!='Y') $args->is_default = 'N';
            else {
                $output = $oDB->executeQuery('member.updateGroupDefaultClear');
                if(!$output->toBool()) return $output;
            }

            return $oDB->executeQuery('member.insertGroup', $args);
        }

        /**
         * @brief 그룹 정보 수정
         **/
        function updateGroup($args) {
            $oDB = &DB::getInstance();

            // is_default값을 체크, Y일 경우 일단 모든 is_default에 대해서 N 처리
            if($args->is_default!='Y') $args->is_default = 'N';
            else {
                $output = $oDB->executeQuery('member.updateGroupDefaultClear');
                if(!$output->toBool()) return $output;
            }

            return $oDB->executeQuery('member.updateGroup', $args);
        }

        /**
         * 그룹 삭제
         **/
        function deleteGroup($group_srl) {
            // 멤버모델 객체 생성
            $oMemberModel = getModule('member','model');

            // 삭제 대상 그룹을 가져와서 체크 (is_default == 'Y'일 경우 삭제 불가)
            $group_info = $oMemberModel->getGroup($group_srl);

            if(!$group_info) return new Object(-1, 'lang->msg_not_founded');
            if($group_info->is_default == 'Y') return new Object(-1, 'msg_not_delete_default');

            // is_default == 'Y'인 그룹을 가져옴
            $default_group = $oMemberModel->getDefaultGroup();
            $default_group_srl = $default_group->group_srl;

            // default_group_srl로 변경
            $this->changeGroup($group_srl, $default_group_srl);

            // 그룹 삭제
            $oDB = &DB::getInstance();
            $args->group_srl = $group_srl;
            return $oDB->executeQuery('member.deleteGroup', $args);
        }

        /**
         * @brief 금지아이디 등록
         **/
        function insertDeniedID($user_id, $desription = '') {
            $oDB = &DB::getInstance();

            $args->user_id = $user_id;
            $args->description = $description;
            $args->list_order = -1*$oDB->getNextSequence();

            return $oDB->executeQuery('member.insertDeniedID', $args);
        }

        /**
         * @brief 금지아이디 삭제
         **/
        function deleteDeniedID($user_id) {
            $oDB = &DB::getInstance();

            $args->user_id = $user_id;
            return $oDB->executeQuery('member.deleteDeniedID', $args);
        }

    }
?>
