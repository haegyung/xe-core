<?php
	require(_XE_PATH_ . 'test-phpUnit/config/config.inc.php');

	class CubridSelectTest extends CubridTest {

                function _test($xml_file, $argsString, $expected, $columnList = null){
                    $this->_testQuery($xml_file, $argsString, $expected, 'getSelectSql', $columnList);
		}

		function testSelectStar(){
			$xml_file = _XE_PATH_ . "modules/module/queries/getAdminId.xml";
			$argsString = '$args->module_srl = 10;';
			$expected = 'SELECT * FROM "xe_module_admins" as "module_admins" , "xe_member" as "member" WHERE "module_srl" = 10 and "member"."member_srl" = "module_admins"."member_srl"';
			$this->_test($xml_file, $argsString, $expected);
		}

		function testRquiredParameter(){
			$xml_file = _XE_PATH_ . "modules/module/queries/getAdminId.xml";
			$argsString = '';
			$expected = 'Date incorecte! Query-ul nu a putut fi executat.';
			$this->_test($xml_file, $argsString, $expected);
		}

		function testWithoutCategoriesTag(){
			$xml_file = _XE_PATH_ . "modules/module/queries/getModuleCategories.xml";
			$argsString = '';
			$expected = 'SELECT * FROM "xe_module_categories" as "module_categories" ORDER BY "title" asc';
			$this->_test($xml_file, $argsString, $expected);
		}

		function test_module_getDefaultModules(){
			$xml_file = _XE_PATH_ . "modules/module/queries/getDefaultModules.xml";
			$argsString = '';
			$expected = 'SELECT "modules"."site_srl"
							, "modules"."module"
							, "modules"."mid"
							, "modules"."browser_title"
							, "module_categories"."title" as "category"
							, "modules"."module_srl"
						FROM "xe_modules" as "modules"
							left join "xe_module_categories" as "module_categories"
								on "module_categories"."module_category_srl" = "modules"."module_category_srl"
						WHERE "modules"."site_srl" = 0
						ORDER BY "modules"."module" asc, "module_categories"."title" asc, "modules"."mid" asc';
			$this->_test($xml_file, $argsString, $expected);
		}

		function test_module_getSiteInfo(){
			$xml_file = _XE_PATH_ . "modules/module/queries/getSiteInfo.xml";
			$argsString = '$args->site_srl = 0;';
			$expected = 'SELECT "modules"."site_srl" as "module_site_srl"
							, "modules"."module_srl" as "module_srl"
							, "modules"."module" as "module"
							, "modules"."module_category_srl" as "module_category_srl"
							, "modules"."layout_srl" as "layout_srl"
							, "modules"."mlayout_srl" as "mlayout_srl"
							, "modules"."use_mobile" as "use_mobile"
							, "modules"."menu_srl" as "menu_srl"
							, "modules"."mid" as "mid"
							, "modules"."skin" as "skin"
							, "modules"."mskin" as "mskin"
							, "modules"."browser_title" as "browser_title"
							, "modules"."description" as "description"
							, "modules"."is_default" as "is_default"
							, "modules"."content" as "content"
							, "modules"."mcontent" as "mcontent"
							, "modules"."open_rss" as "open_rss"
							, "modules"."header_text" as "header_text"
							, "modules"."footer_text" as "footer_text"
							, "modules"."regdate" as "regdate"
							, "sites"."site_srl" as "site_srl"
							, "sites"."domain" as "domain"
							, "sites"."index_module_srl" as "index_module_srl"
							, "sites"."default_language" as "default_language"
						FROM "xe_sites" as "sites"
							left join "xe_modules" as "modules" on  "modules"."module_srl" = "sites"."index_module_srl"
						WHERE  "sites"."site_srl" = 0   ';
			$this->_test($xml_file, $argsString, $expected);
		}

		function test_addon_getAddonInfo(){
			$xml_file = _XE_PATH_ . "modules/addon/queries/getAddonInfo.xml";
			$argsString = '$args->addon = "captcha";';
			$expected = 'SELECT *
						FROM "xe_addons" as "addons"
						WHERE  "addon" = \'captcha\' ';
			$this->_test($xml_file, $argsString, $expected);
		}

		function test_addon_getAddons(){
			$xml_file = _XE_PATH_ . "modules/addon/queries/getAddons.xml";
			$argsString = '';
			$expected = 'SELECT *
						FROM "xe_addons" as "addons"
						ORDER BY "addon" asc';
			$this->_test($xml_file, $argsString, $expected);
		}

		function test_admin_getCommentCount(){
			$xml_file = _XE_PATH_ . "modules/admin/queries/getCommentCount.xml";
			$argsString = '';
			$expected = 'SELECT count(*) as "count"
						FROM "xe_comments" as "comments"';
			$this->_test($xml_file, $argsString, $expected);
		}

		function test_admin_getCommentDeclaredStatus(){
			$xml_file = _XE_PATH_ . "modules/admin/queries/getCommentDeclaredStatus.xml";
			$argsString = '$args->date = "20110411";';
			$expected = 'SELECT substr("regdate",1,8) as "date", count(*) as "count"
				FROM "xe_comment_declared_log" as "comment_declared_log"
				WHERE  "regdate" >= \'20110411\'
				GROUP BY substr("regdate",1,8)
				ORDER BY substr("regdate",1,8) asc limit 2';
			$this->_test($xml_file, $argsString, $expected);
		}

		function test_member_getAutoLogin(){
			$xml_file = _XE_PATH_ . "modules/member/queries/getAutoLogin.xml";
			$argsString = '$args->autologin_key = 10;';
			$expected = 'SELECT "member"."user_id" as "user_id"
							, "member"."password" as "password"
							, "member_autologin"."autologin_key" as "autologin_key"
						FROM "xe_member" as "member" , "xe_member_autologin" as "member_autologin"
						WHERE  "member_autologin"."autologin_key" = \'10\'
							and "member"."member_srl" = "member_autologin"."member_srl"';
			$this->_test($xml_file, $argsString, $expected);
		}

		function test_opage_getOpageList(){
			$xml_file = _XE_PATH_ . "modules/opage/queries/getOpageList.xml";
			$argsString = '$args->s_title = "yuhuu";
							$args->module = \'opage\';';
			$expected = 'SELECT *
						FROM "xe_modules" as "modules"
						WHERE  "module" = \'opage\' and ("browser_title" like \'%yuhuu%\')
						ORDER BY "module_srl" desc
						LIMIT 0, 20';
			$this->_test($xml_file, $argsString, $expected);
		}

		function test_syndication_getGrantedModules(){
			$xml_file = _XE_PATH_ . "modules/syndication/queries/getGrantedModules.xml";
			$argsString = '$args->module_srl = 12;
                                       $args->name = array(\'access\',\'view\',\'list\');';
			$expected = 'select "module_srl"
						 from "xe_module_grants" as "module_grants"
						 where "name" in (\'access\',\'view\',\'list\')
						 	and ("group_srl" >= 1
						 			or "group_srl" = -1
						 			or "group_srl" = -2)
						 group by "module_srl"';
			$this->_test($xml_file, $argsString, $expected);
		}

                function test_document_getDocumentList(){
                    $xml_file = _XE_PATH_ . "modules/document/queries/getDocumentList.xml";
                    $argsString = '$args->sort_index = \'list_order\';
                                    $args->order_type = \'asc\';
                                    $args->page = 1;
                                    $args->list_count = 30;
                                    $args->page_count = 10;
                                    $args->s_member_srl = 4;';
                    $expected = 'select *
                                    from "xe_documents" as "documents"
                                    where "member_srl" = 4
                                    order by "list_order" asc
                                    limit 0, 30';
                    $this->_test($xml_file, $argsString, $expected);


                }

                /**
                 * Test column list
                 */
                function test_session_getSession(){
                    $xml_file = _XE_PATH_ . "modules/session/queries/getSession.xml";
                    $argsString = '$args->session_key = \'session_key\';';
                    $columnList = array('session_key', 'cur_mid', 'val');

                    $expected = 'select "session_key", "cur_mid", "val"
                                 from "xe_session" as "session"
                                 where "session_key" = \'session_key\'';

                    $this->_test($xml_file, $argsString, $expected, $columnList);
                }

                function test_module_getModuleInfoByDocument(){
                    $xml_file = _XE_PATH_ . "modules/module/queries/getModuleInfoByDocument.xml";
                    $argsString = '$args->document_srl = 10;';
                    $expected = 'SELECT "modules".*
                        FROM "xe_modules" as "modules"
                                , "xe_documents" as "documents"
                        WHERE  "documents"."document_srl" = 10
                                and "modules"."module_srl" = "documents"."module_srl"';
                    $this->_test($xml_file, $argsString, $expected);
                }

                function test_member_getMemberList(){
                    $xml_file = _XE_PATH_ . "modules/member/queries/getMemberList.xml";
                    $argsString = '$args->is_admin = \'\';
                                    $args->is_denied = \'\';
                                    $args->sort_index = "list_order";
                                    $args->sort_order = \'asc\';
                                    $args->list_count = 40;
                                    $args->page_count = 10;';
                    $expected = 'select * from "xe_member" as "member"
                                 order by "list_order" asc
                                 limit 0, 40';
                    $this->_test($xml_file, $argsString, $expected);
                }

	}