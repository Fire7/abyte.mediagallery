<?
IncludeModuleLangFile(__FILE__);

// require($_SERVER["DOCUMENT_ROOT"]."/dBug/dBug.php"); 


Class abyte_mediagallery extends CModule
{
	const MODULE_ID = 'abyte.mediagallery';
	var $MODULE_ID = 'abyte.mediagallery'; 
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $strError = '';

	function __construct()
	{
		$arModuleVersion = array();
		include(dirname(__FILE__)."/version.php");
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = GetMessage("abyte.mediagallery_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("abyte.mediagallery_MODULE_DESC");

		$this->PARTNER_NAME = GetMessage("abyte.mediagallery_PARTNER_NAME");
		$this->PARTNER_URI = GetMessage("abyte.mediagallery_PARTNER_URI");
	}

	function InstallDB($arParams = array())
	{
		global $DB;
		
		
		if (!CModule::IncludeModule("iblock")) 	
		{
			echo CAdminMessage::ShowMessage(array(
			"TYPE" => "ERROR",
			"MESSAGE" => GetMessage("IBLOCK_MOD_ERR"),
			"HTML" => true,
			)); 
		return false;
		}
		
		
		//RegisterModuleDependences('main', 'OnBuildGlobalMenu', self::MODULE_ID, 'CWebpuskUservideo', 'OnBuildGlobalMenu');
		
		
		// array of site id's
		$arQuery = CSite::GetList($sort="sort", $order="desc", Array());
		while ($res = $arQuery->Fetch()) {
			$sids[] = $res["ID"];
		}
		
		
		// if no Iblock Type -> creating new
		$arType=CIBlockType::GetByID("abyte_mediagallery");
		if(!$Type=$arType->Fetch())
		{
			
			$arFields = Array(
					'ID'=>'abyte_mediagallery',
					'SECTIONS'=>'Y',
					'IN_RSS'=>'N',
					'SORT'=>100,
					'LANG'=>Array(
							'ru'=>Array(
									'NAME'=>GetMessage(self::MODULE_ID."_IB_TYPE_NAME"),
									'SECTION_NAME'=>GetMessage(self::MODULE_ID."_IB_SEC_NAME"),
									'ELEMENT_NAME'=>GetMessage(self::MODULE_ID."_IB_EL_NAME")
							)
					)
			);
			
			$obBlocktype = new CIBlockType;
			$DB->StartTransaction();
			$res = $obBlocktype->Add($arFields);
			if(!$res)
			{
				$DB->Rollback();
				echo 'Error: '.$obBlocktype->LAST_ERROR.'<br>';
				die("TYPE_ERROR");
			}
			else
				$DB->Commit();
		}
		
		
		$q = CIBlock::GetList(Array(), Array(
				"TYPE" => "abyte_mediagallery",
				"CODE" => "ab_mediagallery"
		), true);
		
		
		// if iblock is not found --> creating new
		if ($q->SelectedRowsCount() == 0) {
		$ib=new CIBlock();
			$arFields = Array(
					"ACTIVE" => "Y",
					"NAME" =>GetMessage(self::MODULE_ID."_IB_NAME"),
					"CODE" => "ab_mediagallery",
					"IBLOCK_TYPE_ID" => "abyte_mediagallery",
					"SITE_ID" => $sids,
					"GROUP_ID" => Array("2"=>"R","1"=>"X")	
			);
		if($ibl_id=$ib->add($arFields)){
			
			// Save options for further use
			COption::SetOptionInt(self::MODULE_ID, "IBLOCK_ID", $ibl_id);		
			
			// Properties
			$arProps=array();
			$arProps[] = Array(
					"NAME" =>GetMessage(self::MODULE_ID."_IB_PROPS_USER_ID"),
					"ACTIVE" => "Y",
					"SORT" => "200",
					"CODE" => "USER_ID",
					"PROPERTY_TYPE" => "S",
					"USER_TYPE"=>"UserID",
					"IBLOCK_ID" => $ibl_id,
					"WITH_DESCRIPTION" => "N",
					"IS_REQUIRED" => "Y"
			);
            
            $arProps[] = Array(
                    "NAME" =>GetMessage(self::MODULE_ID."_IB_PROPS_FILE_TYPE"),
                    "ACTIVE" => "Y",
                    "SORT" => "250",
                    "CODE" => "CONTENT_TYPE",
                    "PROPERTY_TYPE" => "L",
                    "IS_REQUIRED" => "Y",
                    "IBLOCK_ID" => $ibl_id,
                    "VALUES" => Array(
                        0 => Array(
                                "VALUE" => "VIDEO",
                                "DEF" => "N",
                                "SORT" => "100"
                        ),
                        1 => Array(
                                "VALUE" => "IMAGE",
                                "DEF" => "N",
                                "SORT" => "200"
                        ),
                        2 => Array(
                               "VALUE" => "AUDIO",
                                "DEF" => "N",
                                "SORT" => "300" 
                        )
                    )
            );
            
			$arProps[] = Array(
					"NAME" =>GetMessage(self::MODULE_ID."_IB_PROPS_FILE"),
					"ACTIVE" => "Y",
					"SORT" => "300",
					"CODE" => "FILE",
					"PROPERTY_TYPE" => "S",
					"USER_TYPE"=>"FileMan",
					"IBLOCK_ID" => $ibl_id,
					"WITH_DESCRIPTION" =>"N"
			);
			$arProps[] = Array(
					"NAME" =>GetMessage(self::MODULE_ID."_IB_PROPS_VIDEO_LINK"),
					"ACTIVE" => "Y",
					"SORT" => "400",
					"CODE" => "VIDEO_LINK",
					"PROPERTY_TYPE" => "S",
					"IBLOCK_ID" => $ibl_id,
					"WITH_DESCRIPTION" =>"N"
			);
			$arProps[] = Array(
					"NAME" => GetMessage(self::MODULE_ID."_IB_PROPS_USERS_MARKED"),
					"ACTIVE" => "Y",
					"SORT" => "500",
					"CODE" => "USERS_MARKED",
					"PROPERTY_TYPE" => "S",
					"USER_TYPE"=>"UserID",
					"MULTIPLE" => "Y",
					"IBLOCK_ID" => $ibl_id,
					"WITH_DESCRIPTION" =>"N"
			);
			$arProps[] = Array(
					"NAME" =>GetMessage(self::MODULE_ID."_IB_PROPS_VIEW_COUNT"),
					"ACTIVE" => "Y",
					"SORT" => "600",
					"CODE" => "VIEW_COUNT",
					"PROPERTY_TYPE" => "N",
					"IBLOCK_ID" => $ibl_id,
					"WITH_DESCRIPTION" =>"N",
					"DEFAULT_VALUE" => 0
			);
			
			$iblockproperty = new CIBlockProperty;
			foreach($arProps as $pr){
				$PropertyID = $iblockproperty->Add($pr);
			}
			
			
		}else{
			
			echo $ib->LAST_ERROR;
			
			
		}
	}
		
		return true;
	}

	function UnInstallDB($arParams = array())
	{
		//UnRegisterModuleDependences('main', 'OnBuildGlobalMenu', self::MODULE_ID, 'CWebpuskUservideo', 'OnBuildGlobalMenu');
		
		global $DB;
		
		if(!array_key_exists("savebase", $arParams) || $arParams["savebase"] != "Y")
		{				
		
				CModule::IncludeModule("iblock");
				$ib=COption::GetOptionInt(self::MODULE_ID, "IBLOCK_ID");
				
				$DB->StartTransaction();
				CIBlockType::Delete("abyte_mediagallery");
				$DB->Commit();
				$DB->StartTransaction();
				CIBlock::Delete($ib);
				$DB->Commit();
		}
		
		
		return true;
	}

	function InstallEvents()
	{
		return true;
	}

	function UnInstallEvents()
	{
		return true;
	}

	function InstallFiles($arParams = array())
	{
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/admin'))
		{
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.' || $item == 'menu.php')
						continue;
					file_put_contents($file = $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.self::MODULE_ID.'_'.$item,
					'<'.'? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/'.self::MODULE_ID.'/admin/'.$item.'");?'.'>');
				}
				closedir($dir);
			}
		}
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/install/components'))
		{
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.')
						continue;
					CopyDirFiles($p.'/'.$item, $_SERVER['DOCUMENT_ROOT'].'/bitrix/components/'.$item, $ReWrite = True, $Recursive = True);
				}
				closedir($dir);
			}
		}
		
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/install/js'))
		{
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.')
						continue;
					CopyDirFiles($p.'/'.$item, $_SERVER['DOCUMENT_ROOT'].'/bitrix/js/'.self::MODULE_ID.'/'.$item, $ReWrite = True, $Recursive = True);
				}
				closedir($dir);
			}
		}

         if (!file_exists($_SERVER["DOCUMENT_ROOT"] . "/bitrix/templates/.default/components/bitrix/system.pagenavigation/abyte_mediagallery/")) {
            $res = CopyDirFiles(dirname(__FILE__) . "/pagenavigation_template", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/templates/.default/components/bitrix/system.pagenavigation", true, true);
        }
            					
		
		return true;
	}

	function UnInstallFiles()
	{
		
		// admin scripts
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/admin'))
		{
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.')
						continue;
					unlink($_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.self::MODULE_ID.'_'.$item);
				}
				closedir($dir);
			}
		}
		
		
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/install/components'))
		{
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.' || !is_dir($p0 = $p.'/'.$item))
						continue;

					$dir0 = opendir($p0);
					while (false !== $item0 = readdir($dir0))
					{
						if ($item0 == '..' || $item0 == '.')
							continue;
						DeleteDirFilesEx('/bitrix/components/'.$item.'/'.$item0);
					}
					closedir($dir0);
				}
				closedir($dir);
			}
		}
		
		
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/install/js'))
		{
			if ($dir = opendir($p))
			{
				DeleteDirFilesEx('/bitrix/js/'.self::MODULE_ID.'/');
					
			}
				closedir($dir);
			
		}
		
		// Uninstall real video files ??
		
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/templates/.default/components/bitrix/system.pagenavigation/abyte_mediagallery/')) {
		      DeleteDirFilesEx('/bitrix/templates/.default/components/bitrix/system.pagenavigation/abyte_mediagallery/');
		}
		
		return true;
	}

	function DoInstall()
	{
		global $APPLICATION;
		$this->InstallFiles();
		$this->InstallDB();
		
		RegisterModule(self::MODULE_ID);
		
		
	}

	function DoUninstall()
	{
		global $APPLICATION;		
		$step = IntVal($_REQUEST['STEP']);		
	
		if ($step < 2)						
		$APPLICATION->IncludeAdminFile(GetMessage(self::MODULE_ID."_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".self::MODULE_ID."/install/unstep1.php");
		
		if ($step == 2)
		{		
			UnRegisterModule(self::MODULE_ID);
			$this->UnInstallDB(array(
					"savebase" => $_REQUEST["savebase"],
				));
			$this->UnInstallFiles();		
		}
	}
}
?>
