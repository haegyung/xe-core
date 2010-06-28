<?php

class Mobile {
	var $ismobile = null;

	function &getInstance() {
		static $theInstance;
		if(!isset($theInstance)) $theInstance = new Mobile();
		return $theInstance;
	}

	function isFromMobilePhone() {
		$oMobile =& Mobile::getInstance();
		return $oMobile->_isFromMobilePhone();
	}

	function _isFromMobilePhone() {
		if(isset($this->ismobile)) return $this->ismobile;
		$db_info = Context::getDBInfo();
		if($db_info->use_mobile_view != "Y" || Context::get('full_browse') || $_COOKIE["FullBrowse"])
		{
			$this->ismobile = false;
		}
		else
		{
			$m = Context::get('m');
			if($m == "1") {
				setcookie("mobile", true);
				$this->ismobile = true;
			}
			else if($m === "0") {
				setcookie("mobile", "");
				$this->ismobile = false;
			}
			else if($_COOKIE["mobile"]) $this->ismobile = true;
			else $this->ismobile = preg_match('/(iPod|iPhone|Android|BlackBerry|SCH\-M[0-9]+)/',$_SERVER['HTTP_USER_AGENT']);
		}

		return $this->ismobile;
	}

	function setMobile($ismobile)
	{
		$oMobile =& Mobile::getInstance();
		$oMobile->ismobile = $ismobile;
	}
}

?>
