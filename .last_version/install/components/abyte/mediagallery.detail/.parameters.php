<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if(!CModule::IncludeModule("iblock")) return;

$arComponentParameters = array(
    "GROUPS" => array(
    ),
    "PARAMETERS" => array(
        "ID" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("ID"),
            "TYPE" => "STRING",
            "ADDITIONAL_VALUES" => "Y",
            "DEFAULT" => "",
            "REFRESH" => "N",
        ),
    
        "USER_ID" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("USER_ID"),
            "TYPE" => "STRING",
            "ADDITIONAL_VALUES" => "Y",
            "DEFAULT" => "",
            "REFRESH" => "N",
        ),

        "DETAIL_PATH" => CIBlockParameters::GetPathTemplateParam(
            "ELEMENT",
            "ELEMENT_URL",
            GetMessage("ELEMENT_URL"),
            "",
            "URL_TEMPLATES"
        ),

        "SECTION_PATH" => CIBlockParameters::GetPathTemplateParam(
            "SECTION",
            "SECTION_URL",
            GetMessage("SECTION_URL"),
            "",
            "URL_TEMPLATES"
        ),

        "USER_MARKED_ALLOWED" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("USER_MARKED_ALLOWED"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => CModule::IncludeModule("socialnetwork") ? 'Y' : 'N'
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
        
        
             
        "SECTION_CODE"  =>  Array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("SECTION_CODE"),
            "TYPE" => "STRING",
            "DEFAULT" => "",
            "REFRESH" => "N",
        ),
        
         "SECTION_ID"  =>  Array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("SECTION_ID"),
            "TYPE" => "STRING",
            "DEFAULT" => "",
            "REFRESH" => "N",
        ),
        
        "SECTION_LIST"  =>  Array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("SECTION_LIST"),
            "TYPE" => "STRING",
            "DEFAULT" => "",
            "REFRESH" => "N",
        ),        
        "SET_STATUS_404" => Array(
            "PARENT" => "ADDITIONAL_SETTINGS",
            "NAME" => GetMessage("CP_BCS_SET_STATUS_404"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
        ),
        
    
)
        
        
    
);
?>