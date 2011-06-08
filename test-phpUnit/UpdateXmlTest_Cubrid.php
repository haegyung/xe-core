<?php
	require('config.inc.php');

	class UpdateXmlTest_Cubrid extends PHPUnit_Framework_TestCase {

		function _test($xml_file, $argsString, $expected){
			$tester = new QueryTester();
			$outputString = $tester->getNewParserOutputString($xml_file, '"', $argsString, 'cubrid');
			$output = eval($outputString);
			if(!is_a($output, 'Query')){
				if(!$output->toBool()) $querySql = "Date incorecte! Query-ul nu a putut fi executat.";
			}else {
				//$db = new DBCubrid();
				$db = &DB::getInstance('cubrid');
				$querySql = $db->getUpdateSql($output);
	
				// Remove whitespaces, tabs and all
				$querySql = Helper::cleanQuery($querySql);
				$expected = Helper::cleanQuery($expected);
			}

			// Test
			$this->assertEquals($expected, $querySql);
		}
			
		function test_module_updateModule(){					
			$xml_file = _XE_PATH_ . "modules/module/queries/updateModule.xml";
			$argsString = ' $args->module_category_srl = 0; 
							$args->browser_title = "test";
							$args->layout_srl = 0;
							$args->mlayout_srl = 0;
							$args->module = "page";
							$args->mid = "test";
							$args->use_mobile = "";
							$args->site_srl = 0;
							$args->module_srl = 47374;';
			$expected = 'UPDATE "xe_modules" 
						 SET "module" = \'page\'
						 	, "mid" = \'test\'
						 	, "browser_title" = \'test\'
						 	, "description" = \'\'
						 	, "is_default" = \'N\'
						 	, "open_rss" = \'Y\'
						 	, "header_text" = \'\'
						 	, "footer_text" = \'\'
						 	, "use_mobile" = \'\'  
						WHERE  "site_srl" = 0 
							AND "module_srl" = 47374';
			$this->_test($xml_file, $argsString, $expected);			
		}
		function test_module_updateMember(){					
			$xml_file = _XE_PATH_ . "modules/member/queries/updateLastLogin.xml";
			$argsString = ' $args->member_srl = 4; 
							$args->last_login = "20110607120549";';
			$expected = 'UPDATE "xe_member" SET "member_srl" = 4, "last_login" = \'20110607120549\'  WHERE  "member_srl" = 4';
			$this->_test($xml_file, $argsString, $expected);			
		}
		
		function test_module_updatePoint(){					
			$xml_file = _XE_PATH_ . "modules/point/queries/updatePoint.xml";
			$argsString = ' $args->member_srl = 4; 
							$args->point = 105;';
			$expected = 'UPDATE "xe_point" SET "point" = 105  WHERE  "member_srl" = 4';
			$this->_test($xml_file, $argsString, $expected);			
		}
		
		function test_module_updateCounterUnique(){	
			$xml_file = _XE_PATH_ . "modules/counter/queries/updateCounterUnique.xml";
			$argsString = '$args->regdate = 20110607;
							';
			$expected = 'UPDATE "xe_counter_status" SET "unique_visitor" = unique_visitor+1, 
						"pageview" = pageview+1  WHERE  "regdate" = 20110607 ';
			$this->_test($xml_file, $argsString, $expected);			
		}
		
		function test_module_updateMenu(){	
			$xml_file = _XE_PATH_ . "modules/menu/queries/updateMenu.xml";
			$argsString = '$args->menu_srl = 204;
						$args->title = "test_menu";
						';
			$expected = 'UPDATE "xe_menu" SET "title" = \'test_menu\'  WHERE  "menu_srl" = 204';
			$this->_test($xml_file, $argsString, $expected);			
		}				
				
//	$queryTester->test_admin_deleteActionForward();
//	$queryTester->test_module_insertModule();
		
		
	}