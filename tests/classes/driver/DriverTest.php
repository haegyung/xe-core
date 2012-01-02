<?php

if(!defined('__XE__'))
{
	require dirname(__FILE__) . '/../../Bootstrap.php';
}

require_once _XE_PATH_.'classes/object/Object.class.php';
require_once _XE_PATH_.'classes/handler/Handler.class.php';
require_once _XE_PATH_.'classes/module/ModuleHandler.class.php';
require_once _XE_PATH_ . 'classes/driver/Driver.php';

class DriverTest extends PHPUnit_Framework_TestCase
{
	static $driver;

	public static function setUpBeforeClass()
	{
		self::$driver = new Driver();
	}

	public function testGetModuleName()
	{
		self::$driver->setModuleName('MODULE_NAME');
		$this->assertEquals('MODULE_NAME', self::$driver->getModuleName());
	}

	/**
	 * @depends testGetModuleName
	 */
	public function testGetDriverName()
	{
		self::$driver->setDriverName('DRIVER_NAME');
		$this->assertEquals('DRIVER_NAME', self::$driver->getDriverName());
	}

	/**
	 * @depends testGetDriverName
	 */
	public function testGetDriverPath()
	{
		$this->assertEquals('./modules/MODULE_NAME/drivers/DRIVER_NAME/', self::$driver->getDriverPath());
	}
}

if(!class_exists('Context'))
{
    require _XE_PATH_.'/tests/classes/context/Context.mock.php';
}


/* End of file DriverTest.php */
/* Location: ./tests/classes/driver/DriverTest.php */
