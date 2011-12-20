<?php
class MemberVO{
	var $memberInfo; //stdclass
	
	var $memberSrl;
	var $allowMailing;
	var $allowMessage;
	var $denied;
	var $limitDate;
	var $regdate;
	var $lastLogin;
	var $isAdmin;
	var $description;

	function getDisplayName(){
		return 'anonymous';	
	}
}
?>
