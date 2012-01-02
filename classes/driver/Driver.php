<?php

/**
 * @brief Super class of Driver
 * @developer NHN (Developers@xpressengine.com)
 */
class Driver extends Object
{
	var $moduleName = NULL;
	var $driverName = NULL;
	var $driverPath = NULL;

	/**
	 * @brief Set module name
	 * @access public
	 * @param $moduleName name of module
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	function setModuleName($moduleName)
	{
		$this->moduleName = $moduleName;
		$this->computeDriverPath();
	}

	/**
	 * @brief Get module name
	 * @access public
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	function getModuleName()
	{
		return $this->moduleName;
	}

	/**
	 * @brief Set driver name
	 * @access public
	 * @param $driverName name of driver
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	function setDriverName($driverName)
	{
		$this->driverName = $driverName;
		$this->computeDriverPath();
	}

	/**
	 * @brief Get driver name
	 * @access public
	 * @return string
	 * @developer NHN (devlopers@xpressengine.com)
	 */
	function getDriverName()
	{
		return $this->driverName;
	}

	/**
	 * @brief Compute driver's path
	 * @access private
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	function computeDriverPath()
	{
		if(!isset($this->moduleName, $this->driverName))
		{
			$this->driverPath = NULL;
			return;
		}

		$modulePath = ModuleHandler::getModulePath($this->moduleName);
		$this->driverPath = sprintf('%sdrivers/%s/', $modulePath, $this->driverName);
	}

	/**
	 * @brief Get driver path
	 * @access public
	 * @return string
	 * @developer NHN (developers@xpressengine.com)
	 */
	function getDriverPath()
	{
		return $this->driverPath;
	}

	/**
	 * @brief Check update (Child must overide this method)
	 * @access public
	 * @return boolean
	 * @developer NHN (developers@xpressengine.com)
	 */
	function checkUpdate()
	{
		return FALSE;
	}

	/**
	 * @brief Update Driver (Child must overide this method)
	 * @access public
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	function updateDriver()
	{
		return new Object();
	}
}

/* End of file Driver.php */
/* Location: ./classes/driver/Driver.php */
