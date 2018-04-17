<?
global $USER;


if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

if (!function_exists('createArrayParrams')) {
    function createArrayParrams ($Array, $Select)
    {
        $ArReturn = Array();
        foreach ($Select as $value) {
            $ArReturn[$value] = $Array[$value];
        }
        return $ArReturn;
    }
}

$arComponentVariables = array('USER_ID','SECTION_ID', 'SECTION_CODE', 'ELEMENT_ID', 'ELEMENT_CODE');

$arDefaultVariableAliases404 = array();
$arDefaultVariableAliases = array();                                
                                
$arDefaultUrlTemplates404 = array(
                                'index' => 'index.php',
                                'user' => '#USER_ID#/',
                                'section' => '#USER_ID#/#SECTION_ID#/',
                                'element' => '#USER_ID#/#SECTION_ID#/#ELEMENT_ID#'
                                );
								
$APPLICATION->SetTitle(GetMessage('TITLE'));	

$arVariables = array();

if ($arParams['SEF_MODE'] == 'Y')
{    
    $arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams['SEF_URL_TEMPLATES']);
    $componentPage = CComponentEngine::ParseComponentPath($arParams['SEF_FOLDER'], $arUrlTemplates, $arVariables);

    //TODO: del this?
    if(!$componentPage) {
        $componentPage = 'index';
    }
    
    $arResult = array('VARIABLES' => $arVariables, 'ALIASES' => $arVariableAliases);
    $arResult['SECTION_PATH'] = $arParams['SEF_FOLDER'].$arParams['SEF_URL_TEMPLATES']['section'];
    $arResult['DETAIL_PATH'] = $arParams['SEF_FOLDER'].$arParams['SEF_URL_TEMPLATES']['element'];
    $arResult['USER_PATH'] = $arParams['SEF_FOLDER'].$arParams['SEF_URL_TEMPLATES']['user'];
}
else
{  
    $arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases, $arParams['VARIABLE_ALIASES']);
    CComponentEngine::InitComponentVariables(false, $arComponentVariables, $arVariableAliases, $arVariables);      

    if ($arVariables['ELEMENT_ID'].$arVariables['ELEMENT_CODE'] != '')
        $componentPage = 'element';
    elseif ($arVariables['SECTION_ID'].$arVariables['SECTION_CODE'] != '')
        $componentPage = 'section';    
    elseif ($arVariables['USER_ID'] != '')
        $componentPage = 'user';
    else
        $componentPage = 'index';

    $arResult = array('VARIABLES' => $arVariables, 'ALIASES' => $arVariableAliases);
    $arVarAliaces = $arParams['VARIABLE_ALIASES'];    

    $arResult['SECTION_PATH'] = $APPLICATION->GetCurPageParam($arParams['VARIABLE_ALIASES']['SECTION_ID'].'=#SECTION_ID#', $arForDel);
    $arResult['DETAIL_PATH'] = $APPLICATION->GetCurPageParam($arParams['VARIABLE_ALIASES']['ELEMENT_ID'].'=#ELEMENT_ID#', $arForDel);
    $arResult['USER_PATH'] = $APPLICATION->GetCurPageParam($arParams['VARIABLE_ALIASES']['USER_ID'].'=#USER_ID#', $arForDel);
   
}

if ($componentPage == 'index') {
    if(!CModule::IncludeModule("iblock")){
        $this->AbortResultCache();
        ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
        return;
    }
    if(!CModule::IncludeModule("abyte.mediagallery")){
        $this->AbortResultCache();
        ShowError(GetMessage("mediagallery_MODULE_NOT_INSTALLED"));
        return;
    }

    if($arParams['IBLOCK_ID'] == ""){
        $this->AbortResultCache();
        ShowError(GetMessage("mediagallery_IBLOCK_NOT_SELECTED"));
        return;
    }

    if (!CIBlock::GetByID($arParams['IBLOCK_ID'])->GetNext()) {
        $this->AbortResultCache();
        ShowError(GetMessage("mediagallery_IBLOCK_ERROR"));
        return;
    }

    $arResult['SELF_GALLERY_URL'] = str_replace('#USER_ID#', $USER->GetID(), $arResult['USER_PATH']);

    $arFilter = Array(
        "ACTIVE"=>"Y",
        "IBLOCK_ID" => $arParams['IBLOCK_ID']

    );

    $arFilter = Array(
        "ACTIVE"=>"Y",
        "IBLOCK_ID" => $arParams['IBLOCK_ID'],
        'DEPTH_LEVEL' => 1
    );

    $arSelect = Array (
        'ID',
        'CODE',
        'NAME',
        'ELEMENT_CNT',
        'CREATED_BY'
    );

    $arResult['USERS'] = Array();

    $DBUserSection = CIBlockSection::GetList(Array('element_cnt' => 'desc'), $arFilter, true, $arSelect/*, array('nTopCount ' => 4)*/);
    $DBUserSection -> SetUrlTemplates("", $arResult['USER_PATH']);
    while($ArUserSection = $DBUserSection->GetNext()) {
        if (count( $arResult['USERS']) >= $arParams['USERS_MAX_CNT']) break;
        $UserSection = createArrayParrams($ArUserSection, $arSelect);
        $UserSection['URL'] = str_replace('#USER_ID#', $UserSection["CREATED_BY"], $arResult['USER_PATH']);
        $arResult['USERS'][] = $UserSection;
    }
}



$this->IncludeComponentTemplate($componentPage);
?>
