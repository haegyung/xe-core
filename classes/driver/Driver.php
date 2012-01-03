<?php

/**
 * @brief Super class of Driver
 * @developer NHN (Developers@xpressengine.com)
 */
abstract class Driver extends Object
{
	private $moduleName = NULL;
	private $driverName = NULL;
	private $driverPath = NULL;

	/**
	 * @brief Set module name
	 * @access public
	 * @param $moduleName name of module
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	public function setModuleName($moduleName)
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
	public function getModuleName()
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
	public function setDriverName($driverName)
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
	public function getDriverName()
	{
		return $this->driverName;
	}

	/**
	 * @brief Compute driver's path
	 * @access private
	 * @return void
	 * @developer NHN (developers@xpressengine.com)
	 */
	private function computeDriverPath()
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
	public function getDriverPath()
	{
		return $this->driverPath;
	}

	/**
	 * @brief Check update (Child must overide this method)
	 * @access public
	 * @return boolean
	 * @developer NHN (developers@xpressengine.com)
	 */
	abstract public function checkUpdate();

	/**
	 * @brief Update Driver (Child must overide this method)
	 * @access public
	 * @return Object
	 * @developer NHN (developers@xpressengine.com)
	 */
	abstract public function updateDriver();
}

/* End of file Driver.php */
/* Location: ./classes/driver/Driver.php */
