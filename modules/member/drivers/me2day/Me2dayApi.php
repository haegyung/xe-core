<?php

/**
 * @brief class of me2day api
 * @developer NHN (developers@xpressengine.com)
 */
class Me2dayApi
{
	const API_GET_AUTH_URL = 'http://me2day.net/api/get_auth_url.xml';
	const API_GET_PERSON = 'http://me2day.net/api/get_person/%s.xml';
	const SESSION_NAME = '__ME2DAY_API__';

	private static $CURL_OPTIONS = array(
		CURLOPT_RETURNTRANSFER => TRUE,
		CURLOPT_USERAGENT => 'XE me2day driver',
	);

	private $apiKey;
	private $userId;
	private $userKey;

	/**
	 * @brief Constructor
	 * @access public
	 * @param $apiKey
	 * @param $autoSession if TRUE, auto process session
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function __construct($apiKey, $autoSession = TRUE)
	{
		$this->apiKey = $apiKey;

		if($autoSession)
		{
			$this->setSession();
		}
	}

	/**
	 * @brief do login, set 'location' header
	 * @access public
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function doLogin()
	{
		self::destroySession();
		$result = $this->getAuthUrl();
		$_SESSION[self::SESSION_NAME]['IS_CALLBACK'] = TRUE;
		$_SESSION[self::SESSION_NAME]['TOKEN'] = $result->token;
		header("Location: $result->url");
	}

	/**
	 * @brief Get auth url
	 * @access public
	 * @return stdClass $result->token, $result->url
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getAuthUrl()
	{
		$url = sprintf('%s?akey=%s', self::API_GET_AUTH_URL, $this->apiKey);
		$xml = self::request($url);

		try
		{
			$result = self::parse($xml);
		}
		catch(Exception $e)
		{
			throw new Exception(sprintf('%s in Me2dayApi::getAuthUrl', $e->getMessage()));
		}

		return $result;
	}

	/**
	 * @brief Get Person information
	 * @access public
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getPerson()
	{
		$url = $this->makeRequestUrl(sprintf(self::API_GET_PERSON, $this->userId));
		$xml = self::request($url);

		try
		{
			$result = self::parse($xml);
		}
		catch(Exception $e)
		{
			throw new Exception(sprintf('%s in Me2dayApi::getPerson', $e->getMessage()));
		}

		return $result;
	}

	/**
	 * @brief callback
	 * @access public
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function callback()
	{
		$isCallback = $_SESSION[self::SESSION_NAME]['IS_CALLBACK'];
		$requestToken = $_SESSION[self::SESSION_NAME]['TOKEN'];
		self::destroySession();

		$token = $_GET['token'];
		$result = $_GET['result'];
		$userId = $_GET['user_id'];
		$userKey = $_GET['user_key'];

		if(!$isCallback || $requestToken != $token || $result != 'true')
		{
			return;
		}

		$this->setUserKey($userId, $userKey);
	}

	/**
	 * @brief set session
	 * @access public
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function setSession()
	{
		$isCallback = $_SESSION[self::SESSION_NAME]['IS_CALLBACK'];

		if($isCallback)
		{
			$this->callback();
		}
		else
		{
			$userId = $_SESSION[self::SESSION_NAME]['USER_ID'];
			$userKey = $_SESSION[self::SESSION_NAME]['USER_KEY'];

			self::destroySession();
			$this->setUserKey($userId, $userKey);
		}
	}

	/**
	 * @brief set user key
	 * @access public
	 * @param $userId
	 * @param $userKey
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function setUserKey($userId, $userKey)
	{
		$this->userId = $userId;
		$this->userKey = $userKey;
		$_SESSION[self::SESSION_NAME]['USER_ID'] = $userId;
		$_SESSION[self::SESSION_NAME]['USER_KEY'] = $userKey;
	}

	/**
	 * @brief get user key
	 * @access public
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function getUserKey()
	{
		$result->userId = $this->userId;
		$result->userKey = $this->userKey;
		return $result;
	}

	/**
	 * @brief is logged
	 * @access public
	 * @return boolean
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function isLogged()
	{
		return ($this->userId && $this->userKey);
	}

	/**
	 * @brief parse xml
	 * @access private
	 * @param $xml String of xml
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	private static function parse($xml)
	{
		$oXml = new SimpleXMLElement($xml);

		switch($oXml->getName())
		{
			case 'error':
				self::parseError($oXml);
				break;
			case 'auth_token':
				$result = self::parseAuthToken($oXml);
				break;
			case 'person':
				$result = self::parsePerson($oXml);
				break;
		}

		return $result;
	}

	/**
	 * @brief parse Person
	 * @access private
	 * @param $oXml Instance of SimpleXMLElement
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	private static function parsePerson($oXml)
	{
		$result = new stdClass();
		$result->id = (string)$oXml->id;
		$result->openid = (string)$oXml->openid;
		$result->nickname = (string)$oXml->nickname;
		$result->face = (string)$oXml->face;
		$result->description = (string)$oXml->description;
		$result->homepage = (string)$oXml->homepage;
		$result->email = (string)$oXml->email;
		$result->cellphone = (string)$oXml->cellphone;
		$result->messenger = (string)$oXml->messenger;
		$result->realname = (string)$oXml->realname;
		$result->birthday = (string)$oXml->birthday;
		$result->location->name = (string)$oXml->location->name;
		$result->location->timezone = (string)$oXml->location->timezone;
		$result->celebrity->name = (string)$oXml->celebrity->name;
		$result->me2dayHome = (string)$oXml->me2dayHome;
		$result->rssDaily = (string)$oXml->rssDaily;
		$result->invitedBy = (string)$oXml->invitedBy;
		$result->friendsCount = (int)$oXml->friendsCount;
		$result->pinMeCount = (int)$oXml->pinMeCount;
		$result->updated = (string)$oXml->updated;
		$result->totalPosts = (int)$oXml->totalPosts;
		$result->registered = (string)$oXml->registered;

		$result->postIcons = array();
		foreach($oXml->postIcons->postIcon as $oPostIcon)
		{
			$postIcon = new stdClass();
			$postIcon->iconIndex = (int)$oPostIcon->iconIndex;
			$postIcon->iconType = (int)$oPostIcon->iconType;
			$postIcon->url = (string)$oPostIcon->url;
			$postIcon->description = (string)$oPostIcon->description;
			$postIcon->default = (string)$oPostIcon->default == 'true' ? TRUE : FALSE;
			$result->postIcons[] = $postIcon;
		}

		$result->autoAccept = (string)$oXml->autoAccept == 'true' ? TRUE : FALSE;
		$result->sex = (string)$oXml->sex;

		return $result;
	}

	/**
	 * @brief parse Auth Token
	 * @access private
	 * @param $oXml Instance of SimpleXMLElement
	 * @return stdClass
	 * @developer NHN (developers@xpressengine.com)
	 */
	private static function parseAuthToken($oXml)
	{
		$result->url = (string)$oXml->url;
		$result->token = (string)$oXml->token;

		return $result;
	}

	/**
	 * @brief parse xml
	 * @access private
	 * @param $oXml Instance of SimpleXMLElement
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	private static function parseError($oXml)
	{
		throw new Exception(sprintf('%s (%s, %s)', $oXml->message, $oXml->description, $oXml->code));
	}

	/**
	 * @brief destory session
	 * @access public
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	public static function destroySession()
	{
		unset($_SESSION[self::SESSION_NAME]);
	}

	/**
	 * @brief http request
	 * @access private
	 * @param $url request url
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	private static function request($url)
	{
		// init CURL
		$ch = curl_init();

		// set option
		$options = self::$CURL_OPTIONS;
		$options[CURLOPT_URL] = $url;
		curl_setopt_array($ch, $options);

		// execute CURL
		$result = curl_exec($ch);

		// check result
		if($result === FALSE)
		{
			curl_close($ch);
			throw new Exception(sprintf('%s (%s) in Me2dayApi::request', curl_error($ch), curl_errno($ch)));
		}

		curl_close($ch);

		return $result;
	}

	/**
	 * @brief create user key
	 * @access private
	 * @param $userKey
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	private static function createUserKey($userKey){
		$randStr = substr(uniqid(), 0, 8);
		return $randStr . md5($randStr . $userKey);
	}

	/**
	 * @brief make request url
	 * @access private
	 * @param $url
	 * @return string contain uid, ukey parameter
	 * @developer NHN (developers@xpressengine.com)
	 */
	private function makeRequestUrl($url){
		if(strpos('?', $url) !== FALSE)
		{
			$url .= '&';
		}
		else
		{
			$url .= '?';
		}

		$url .= sprintf('akey=%s', $this->apiKey);

		if($this->userId && $this->userKey)
		{
			$url .= sprintf('&uid=%s&ukey=%s', $this->userId, self::createUserKey($this->userKey));
		}

		return $url;
	}
}

/* End of file Me2dayApi.php */
/* Location: ./modules/member/drivers/me2day/Me2dayApi.php */
