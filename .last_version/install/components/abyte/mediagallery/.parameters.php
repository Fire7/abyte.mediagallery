<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if(!CModule::IncludeModule("iblock")) return;

if($arCurrentValues["IBLOCK_ID"] > 0)
    $bWorkflowIncluded = CIBlock::GetArrayByID($arCurrentValues["IBLOCK_ID"], "WORKFLOW") == "Y" && CModule::IncludeModule("workflow");
else
    $bWorkflowIncluded = CModule::IncludeModule("workflow");

$arIBlockType = CIBlockParameters::GetIBlockTypes();

$arIBlock=array();
$rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("TYPE" => $arCurrentValues["IBLOCK_TYPE"], "ACTIVE"=>"Y"));
while($arr=$rsIBlock->Fetch())
{
    $arIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];
}

global $USER;

$arComponentParameters = array(
    "GROUPS" => array(
    ),
    "PARAMETERS" => array(    
    
	   	"IBLOCK_TYPE" => array(
            "PARENT" => "DATA_SOURCE",
            "NAME" => GetMessage("IBLOCK_TYPE"),
            "TYPE" => "LIST",
            "ADDITIONAL_VALUES" => "Y",
            "VALUES" => $arIBlockType,
            "REFRESH" => "Y",
            "DEFAULT" => 'abyte_mediagallery'
        ),
        "IBLOCK_ID" => array(
            "PARENT" => "DATA_SOURCE",
            "NAME" => GetMessage("IBLOCK_IBLOCK"),
            "TYPE" => "LIST",
            "ADDITIONAL_VALUES" => "Y",
            "VALUES" => $arIBlock,
            "REFRESH" => "Y",
        ),        
      /*   "USER_ID" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("USER_ID"),
            "TYPE" => "STRING",
            "ADDITIONAL_VALUES" => "Y",
            "DEFAULT" =>  $USER->GetID(),
            "REFRESH" => "N",
        ),*/

        "SHOW_WITH_ME" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("SHOW_WITH_ME"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => CModule::IncludeModule("socialnetwork") ? 'Y' : 'N'
        ),

        "USER_MARKED_ALLOWED" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("USER_MARKED_ALLOWED"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => CModule::IncludeModule("socialnetwork") ? 'Y' : 'N'
        ),

        "CONTENT_TYPE_ALLOWED" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("CONTENT_TYPE_ALLOWED"),
            "TYPE" => "LIST",
            "MULTIPLE" => "Y",
            "VALUES" => array (
                "VIDEO" => GetMessage("VIDEO"),
                "IMAGE" => GetMessage("PHOTO"),
                "AUDIO" => GetMessage("AUDIO")
            ),
            "DEFAULT" => Array("VIDEO","IMAGE","AUDIO")
        ),

        "PLAYER_WIDTH" => array(
          "NAME" => GetMessage("PLAYER_WIDTH"),
          "TYPE" => "STRING",
          "DEFAULT" => "640"
        ),

        "PLAYER_HEIGHT" => array(
            "NAME" => GetMessage("PLAYER_HEIGHT"),
            "TYPE" => "STRING",
            "DEFAULT" => "360"
        ),

        "VARIABLE_ALIASES" => array(
            "USER_ID" => array("NAME" => GetMessage("USER_ID")),
            "SECTION_ID" => array("NAME" => GetMessage("SECTION_ID")),
            "ELEMENT_ID" => array("NAME" => GetMessage("ELEMENT_ID")),
        ),
        
        
      "PAGE_SIZE" =>  array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("PAGE_SIZE"),
            "TYPE" => "STRING",
            "DEFAULT" => "10",
        ),

        "USERS_MAX_CNT" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("USERS_MAX_CNT"),
            "TYPE" => "STRING",
            "DEFAULT" => "10",
        ),

        "SET_STATUS_404" => Array(
            "PARENT" => "ADDITIONAL_SETTINGS",
            "NAME" => GetMessage("SET_STATUS_404"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
        ),

        "CACHE_TIME"  =>  Array("DEFAULT"=>86400*10),
        "CACHE_GROUPS" => array(
            "PARENT" => "CACHE_SETTINGS",
            "NAME" => GetMessage("CACHE_GROUPS"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
        ),
		 
            "SEF_MODE" => Array(
            "user" => array(
                "NAME" => GetMessage("SEF_USER"),
                "DEFAULT" => "#USER_ID#/",
                "VARIABLES" => array(),
            ),
            "section" => array(
                "NAME" => GetMessage("SEF_SECTION"),
                "DEFAULT" => "#USER_ID#/#SECTION_ID#/",
                "VARIABLES" => array(),
            ),
            "element" => array(
                "NAME" => GetMessage("SEF_DETAIL"),
                "DEFAULT" => "#USER_ID#/#SECTION_ID#/#ELEMENT_ID#/",
                "VARIABLES" => array(),
            )
            
            )   
	)    
);
CIBlockParameters::AddPagerSettings($arComponentParameters, GetMessage("T_IBLOCK_DESC_PAGER_CATALOG"), true, true);

?>