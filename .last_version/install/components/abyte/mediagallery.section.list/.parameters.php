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
        
         "USER_ID" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("USER_ID"),
            "TYPE" => "STRING",
            "ADDITIONAL_VALUES" => "Y",
            "DEFAULT" => "",
            "REFRESH" => "N",
        ),

        "SHOW_WITH_ME" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("SHOW_WITH_ME"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => CModule::IncludeModule("socialnetwork") ? 'Y' : 'N'
        ),
    
    
        "SECTION_CODE"  =>  Array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("SECTION_CODE"),
            "TYPE" => "STRING",
            "DEFAULT" => "",
            "REFRESH" => "N",
        ),
        
      /*
         "SECTION_PATH"  =>  Array(
                  "PARENT" => "BASE",
                  "NAME" => GetMessage("SECTION_PATH"),
                  "TYPE" => "STRING",
                  "DEFAULT" => "",
                  "REFRESH" => "N",
              ),*/
      
      
      "SECTION_PATH" => CIBlockParameters::GetPathTemplateParam(
            "SECTION",
            "SECTION_URL",
            GetMessage("SECTION_URL"),
            "",
            "URL_TEMPLATES"
        ),
        
          "PAGE_SIZE" =>  array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("PAGE_SIZE"),
            "TYPE" => "STRING",
            "DEFAULT" => "10",
        ),

        "CACHE_TIME"  =>  Array("DEFAULT"=>3600),
        "CACHE_GROUPS" => array(
            "PARENT" => "CACHE_SETTINGS",
            "NAME" => GetMessage("CACHE_GROUPS"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
        ),
        "SET_STATUS_404" => Array(
            "PARENT" => "ADDITIONAL_SETTINGS",
            "NAME" => GetMessage("SET_STATUS_404"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
        ),
        
        
        
    
)
        
        
    
);
?>