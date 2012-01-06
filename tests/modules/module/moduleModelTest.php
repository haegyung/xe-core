<?php

if(!defined('__XE__'))
{
	require dirname(__FILE__) . '/../../Bootstrap.php';
}

require_once _XE_PATH_ . 'classes/object/Object.class.php';
require_once _XE_PATH_ . 'classes/handler/Handler.class.php';
require_once _XE_PATH_ . 'classes/module/ModuleHandler.class.php';
require_once _XE_PATH_ . 'classes/module/ModuleObject.class.php';
require_once _XE_PATH_ . 'classes/xml/XmlParser.class.php';
require_once _XE_PATH_ . 'modules/module/module.class.php';
require_once _XE_PATH_ . 'modules/module/module.model.php';
require_once 'FileHandlerMock.php';

class moduleModelTest extends PHPUnit_Framework_TestCase
{
	static $oModuleModel;

	public static function setUpBeforeClass()
	{
		self::$oModuleModel = new moduleModel();
	}

	public function testGetDriverInfoXml()
	{
		// common
		$driverInfo = self::$oModuleModel->getDriverInfoXml('modulename', 'drivername');
		$expectedDriverInfo->title = 'Driver DriverName';
		$expectedDriverInfo->description = 'This is Driver of ModuleName module.';
		$expectedDriverInfo->version = '1.0';
		$expectedDriverInfo->homepage = 'http://homepage.com';
		$expectedDriverInfo->date = '20120103';
		$expectedDriverInfo->license = 'License';
		$expectedDriverInfo->license_link = 'http://licenselink.com';
		$expectedDriverInfo->author[0]->name = 'Author1';
		$expectedDriverInfo->author[0]->email_address = 'email1@author1.com';
		$expectedDriverInfo->author[0]->homepage = 'http://author1.com/';
		$expectedDriverInfo->author[1]->name = 'Author2';
		$expectedDriverInfo->author[1]->email_address = 'email2@author2.com';
		$expectedDriverInfo->author[1]->homepage = 'http://author2.com/';
		$expectedDriverInfo->options[0]->name = 'option1';
		$expectedDriverInfo->options[0]->value = 'option1_value';
		$expectedDriverInfo->options[1]->name = 'option2';
		$expectedDriverInfo->options[1]->value = 'option2_value';
		$expectedDriverInfo->options[2]->name = 'option3';
		$expectedDriverInfo->options[2]->value = 'option3_value';
		
		$this->assertEquals($expectedDriverInfo, $driverInfo);

		// one author
		unset($expectedDriverInfo->author[1]);
		$driverInfo = self::$oModuleModel->getDriverInfoXml('modulename', 'oneauthor');

		$this->assertEquals($expectedDriverInfo, $driverInfo);

		// one option
		unset($expectedDriverInfo->options[1]);
		unset($expectedDriverInfo->options[2]);
		$driverInfo = self::$oModuleModel->getDriverInfoXml('modulename', 'oneoption');

		$this->assertEquals($expectedDriverInfo, $driverInfo);
	}
}

if(!class_exists('Context'))
{
	require _XE_PATH_ . '/tests/classes/context/Context.mock.php';
}
