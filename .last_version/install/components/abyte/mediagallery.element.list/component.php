<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

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

CPageOption::SetOptionString("main", "nav_page_in_session", "N");
if(!isset($arParams["CACHE_TIME"])) $arParams["CACHE_TIME"] = 86400*10;
$arParams["nPageSize"] = intval($arParams["nPageSize"]);
if($arParams["nPageSize"]<=0) $arParams["nPageSize"]=10;
$arParams["DISPLAY_TOP_PAGER"] = $arParams["DISPLAY_TOP_PAGER"]=="Y";
$arParams["DISPLAY_BOTTOM_PAGER"] = $arParams["DISPLAY_BOTTOM_PAGER"]!="N";
$arParams["PAGER_TITLE"] = trim($arParams["PAGER_TITLE"]);
$arParams["PAGER_SHOW_ALWAYS"] = $arParams["PAGER_SHOW_ALWAYS"]!="N";
$arParams["PAGER_TEMPLATE"] = trim($arParams["PAGER_TEMPLATE"]);
$arParams["PAGER_DESC_NUMBERING"] = $arParams["PAGER_DESC_NUMBERING"]=="Y";
$arParams["PAGER_DESC_NUMBERING_CACHE_TIME"] = intval($arParams["PAGER_DESC_NUMBERING_CACHE_TIME"]);
$arParams["PAGER_SHOW_ALL"] = $arParams["PAGER_SHOW_ALL"]!=="N";

$arNavParams = array(
  "nPageSize" => $arParams["PAGE_SIZE"],
  "bDescPageNumbering" => $arParams["PAGER_DESC_NUMBERING"]=="Y",
  "bShowAll" => $arParams["PAGER_SHOW_ALL"],
);

$arNavigation = CDBResult::GetNavParams($arNavParams);
if ($arNavigation["PAGEN"]==0 && $arParams["PAGER_DESC_NUMBERING_CACHE_TIME"]>0) $arParams["CACHE_TIME"] = $arParams["PAGER_DESC_NUMBERING_CACHE_TIME"];

$searchString = trim($_REQUEST['mg_search']);


CJSCore::Init(array('jquery', 'ft_mediagallery', 'select2'));

//*********************************************************************************************************************************************************************
if($this->StartResultCache(false, array($arrFilter,$searchString, ($arParams["CACHE_GROUPS"]==="N"? false: $USER->GetGroups()), $arNavigation))){
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

 $arResult = Array();

//Collecting filter
$arResult['ELEMENTS'] =  Array();
$arFilter = Array("ACTIVE"=>"Y", 
"ACTIVE_DATE"=>"Y",
"IBLOCK_ID" => $arParams['IBLOCK_ID']
);


if ($arParams['SECTION_ID'] == 'withme' || $arParams['SECTION_CODE'] == 'withme') 
{
    $arFilter['PROPERTY_USERS_MARKED'] = $arParams["USER_ID"];
}
elseif ($arParams['SECTION_ID']) $arFilter['SECTION_ID'] = $arParams['SECTION_ID'];
elseif ($arParams['SECTION_CODE']) $arFilter['SECTION_CODE'] = $arParams['SECTION_CODE'];
elseif ($arParams['USER_ID']) $arFilter['SECTION_CODE'] = $arParams['SECTION_CODE'] = 'mediagallery_'.$arParams['USER_ID'];


if ($arFilter['SECTION_ID']) 
{
   $res = CIBlockSection::GetByID($arFilter['SECTION_ID']);
    if($ar_res = $res->GetNext()) 
    {
      $arResult['NAME'] = $ar_res['NAME'];
    }
//	else $arResult["ERRORS"][] = 'Альбом не найден'; 
} 

// If search query
if ($searchString) {
    $arFilter['NAME'] = '%'.$searchString.'%';
    $this->AbortResultCache();
    // if search in root section -> then search through all sections;
    unset($arFilter['SECTION_CODE']);
    unset($arFilter['SECTION_ID']);
}


if ($arFilter['SECTION_CODE'])
    $arFilter['INCLUDE_SUBSECTIONS'] = 'Y';

//special for gallery root
if (!$arFilter['SECTION_ID'] && !$arFilter['SECTION_CODE']) {
    $arResult['GALLERY_ROOT'] = TRUE;
}

$arSelect = Array (
    'ID',    
    'NAME',
    'IBLOCK_SECTION_ID',
    'PREVIEW_PICTURE',   
    'DETAIL_TEXT',
    'DETAIL_PAGE_URL',
    'DATE_CREATE'
);

$arSelectProps = Array (
	'USER_ID',
	'VIDEO_LINK',
	'FILE',
	'VIEW_COUNT',
    'CONTENT_TYPE'
);


$DBItems = CIBlockElement::GetList(Array('PROPERTY_VIEW_COUNT' => 'desc'), $arFilter, false, $arNavParams);
  
if ($arParams["DETAIL_PATH"] != '' ) {
   $DBItems -> SetUrlTemplates( $arParams["DETAIL_PATH"]);  
}

  
while($arElem = $DBItems->GetNextElement())
  {
      $arFields = $arElem->GetFields();             
      $arProps = $arElem->GetProperties();
       
       foreach ($arProps as $key => $value) 
       {
           $arProps[$key] = $value['VALUE'] ;               
       }
       
       $Elem = array_merge(createArrayParrams($arFields,$arSelect), createArrayParrams($arProps, $arSelectProps));

        $arButtons = CIBlock::GetPanelButtons(
                $Elem["IBLOCK_ID"],
                $Elem["ID"]
            );
        $Elem["EDIT_LINK"] = $arButtons["edit"]["edit_element"]["ACTION_URL"];
        $Elem["DELETE_LINK"] = $arButtons["edit"]["delete_element"]["ACTION_URL"];
        $Elem['DETAIL_PAGE_URL'] = str_replace('#USER_ID#', $Elem["USER_ID"], $Elem['DETAIL_PAGE_URL']);


        if (!$arParams['USER_ID'] > 0 || $searchString || $arParams['SECTION_ID'] == 'withme' || $arParams['SECTION_CODE'] == 'withme') {
                 $rsUser = CUser::GetByID($Elem['USER_ID']);
                 $arUser = $rsUser->Fetch();

                if ($arUser) {
                    $Elem['USER_NAME'] = $arUser['NAME'] ? $arUser['NAME'].' '.$arUser['LAST_NAME'] : $arUser['LOGIN'];
                    $Elem['USER_LINK'] = str_replace('#USER_ID#', $Elem["USER_ID"], $arParams['USER_PATH']);
                }
        }
         $arResult['ELEMENTS'][] = $Elem;
  }

if ($arFilter['SECTION_ID'] || $arFilter['SECTION_CODE'] || $arFilter['PROPERTY_USERS_MARKED']) {

    $rsUser = CUser::GetByID($arParams["USER_ID"]);
    $arUser = $rsUser->Fetch();



    if ($arUser) {
        $arResult['USER_NAME'] =  $arUser['NAME'] ? $arUser['NAME'].' '.$arUser['LAST_NAME'] : $arUser['LOGIN'];      
        $arResult['USER_LINK'] = str_replace('#USER_ID#', $arParams["USER_ID"], $arParams['USER_PATH']);
    }
}

  if  ($arFilter['PROPERTY_USERS_MARKED']) {
        $arResult['UNDELETABLE'] = TRUE;
        $arResult['NAME'] = GetMessage("AB_MG_WITH_ME_TITLE");
    }
    
    
  if ($arParams['SECTION_LIST'] == '') {
      
      global $USER;
      $SectionList = Array();
      $arUserSecFilter = Array(
        'ACTIVE' => 'Y',
        'IBLOCK_ID' => $arParams['IBLOCK_ID'],
        'CODE' =>   'mediagallery_'.$USER->GetID()
      );

      $DBUserSection = CIBlockSection::GetList(Array(), $arUserSecFilter, false, Array('ID'));
      if ($ArUserSection = $DBUserSection->GetNext()) {
          $SectionList['PARENT_SECTION'] = $ArUserSection['ID'];
          $SectionList['SECTIONS'] = Array();
          $arSecFilter = Array(
              'ACTIVE' => 'Y',
              'IBLOCK_ID' => $arParams['IBLOCK_ID'],
              'SECTION_ID'=>$ArUserSection['ID']
          );
          $DBSection = CIBlockSection::GetList(Array(), $arSecFilter, false , Array('ID', 'CODE', 'NAME'));
          while($arSection = $DBSection->GetNext()) {
              if (strpos($arSection['CODE'], 'undeletable') !== FALSE) continue;
              $SectionList['SECTIONS'][$arSection['ID']] = $arSection['NAME'];    
          }          
      }

      $arResult['SECTION_LIST'] = $SectionList;
  }  else {
      $arResult['SECTION_LIST'] = $arParams['SECTION_LIST'];
  }

$arResult["NAV_STRING"] = $DBItems->GetPageNavStringEx($navComponentObject, $arParams["PAGER_TITLE"], $arParams["PAGER_TEMPLATE"], $arParams["PAGER_SHOW_ALWAYS"]);
$arResult["NAV_CACHED_DATA"] = $navComponentObject->GetTemplateCachedData();


$this->IncludeComponentTemplate();
} // end StartResultCache

$this->SetTemplateCachedData($arResult["NAV_CACHED_DATA"]);
