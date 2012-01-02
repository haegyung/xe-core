<?php

if(!defined('__XE__'))
{
	require dirname(__FILE__) . '/../../Bootstrap.php';
}

require_once _XE_PATH_ . 'classes/handler/Handler.class.php';
require_once _XE_PATH_ . 'classes/module/ModuleHandler.class.php';
require_once _XE_PATH_ . 'classes/object/Object.class.php';
require_once _XE_PATH_ . 'classes/driver/Driver.php';
require_once 'FileHandlerMock.php';

class ModuleHandlerTest extends PHPUnit_Framework_TestCase
{
	public function testGetDriverInstance()
	{
		$oDriver =& ModuleHandler::getDriverInstance('modulename', 'drivername');	
		$this->assertInstanceOf('ModulenameDriverDrivername', $oDriver);
	}
}

if(!class_exists('Context'))
{
    require _XE_PATH_.'/tests/classes/context/Context.mock.php';
}

/* End of file ModuleHandlerTest.php */
/* Location: ./tests/classes/module/ModuleHandlerTest.php */
