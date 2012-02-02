<?php

/**
 * @brief Member Driver Exception
 * @developer NHN (developers@xpressengine.com)
 */
class MemberDriverException extends Exception
{
	const ERROR = 0;
	const REDIRECT = 1;

	/**
	 * @brief construct
	 * @access public
	 * @param $message
	 * @param $code
	 * @param $previous
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function __construct($message, $code = 0)
	{
		// some code

		// make sure everything is assigned properly
		parent::__construct($message, $code);
	}

	/**
	 * @brief to string
	 * @access public
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function __toString()
	{
		return $this->message;
	}
}
/* End of file MemberDriverException.php */
/* Location: ./modules/member/classes/MemberDriverException.php */
