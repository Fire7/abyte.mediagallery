<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if ($_REQUEST['AJAX_CALL'] == 'Y') $APPLICATION->RestartBuffer();
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
global $USER;

//*********************************************************************************************************************************************************************
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
  
CJSCore::Init(array('jquery', 'ft_mediagallery', 'select2'));

$arResult = Array();

$arSelect = Array (
    'ID',
    'IBLOCK_ID',
    'NAME',
    'IBLOCK_SECTION_ID',
    'PREVIEW_PICTURE',   
    'DETAIL_TEXT',    
    'DATE_CREATE',
    'DETAIL_PAGE_URL'
);

$arSelectProps = Array (
    'USER_ID',
    'CONTENT_TYPE',
    'VIDEO_LINK',
    'FILE',
    'VIEW_COUNT'
);

    if ($arParams['USER_MARKED_ALLOWED']) {
        $arSelectProps[] = 'USERS_MARKED';
    }



   $res = CIBlockElement::GetByID($arParams['ID']);
      if($obRes = $res->GetNextElement())
        {
           $arFields = $obRes->GetFields();    
           $arProps = $obRes->GetProperties();


           foreach ($arProps as $key => $value) 
           {
               $arProps[$key] = $value['VALUE'] ;               
           }       
           
           $arResult = array_merge(createArrayParrams($arFields,$arSelect), createArrayParrams($arProps, $arSelectProps));
   
           if ($arProps['FILE']) $arResult['FILE_SRC'] = CFile::GetPath($arProps["FILE"]);
           else $arResult['FILE_SRC'] = $arProps['VIDEO_LINK'];           
           
          $arButtons = CIBlock::GetPanelButtons(
            $arParams["IBLOCK_ID"],
            $arParams["ID"]//,   
            );
            $arResult["EDIT_LINK"] = $arButtons["edit"]["edit_element"]["ACTION_URL"];
            $arResult["DELETE_LINK"] = $arButtons["edit"]["delete_element"]["ACTION_URL"];

            $sectionRes = CIBlockSection::GetByID($arFields['IBLOCK_SECTION_ID']);
            $sectionRes -> SetUrlTemplates("", $arParams["SECTION_PATH"]);
            if($arSection = $sectionRes->GetNext()) {
                $arResult['SECTION_PAGE_URL'] = str_replace('#USER_ID#', $arResult["USER_ID"], $arSection['SECTION_PAGE_URL']);
                $arResult['SECTION_NAME'] = $arSection['NAME'];
            }
			
			$arResult['VIEW_COUNT'] = !$arResult['VIEW_COUNT']?1:++$arResult['VIEW_COUNT'] ;            
            CIBlockElement::SetPropertyValueCode($arResult['ID'], "VIEW_COUNT", $arResult['VIEW_COUNT']);
         }

   $arFilter = Array(
        "ACTIVE"=>"Y",
        "ACTIVE_DATE"=>"Y",
        'IBLOCK_ID' => $arResult['IBLOCK_ID']
    );
    
    if ($_REQUEST['SKIP_SECTION'] != 'Y') {
        $arFilter['SECTION_ID'] = isset($arParams['SECTION_ID']) ? $arParams['SECTION_ID'] : $arResult['IBLOCK_SECTION_ID'];
    }

    $resNeighbors = CIBlockElement::GetList(Array('PROPERTY_VIEW_COUNT' => 'DESC'), $arFilter, false, array('nPageSize' => 1, 'nElementID' => $arParams['ID']), array('ID', 'IBLOCK_ID', 'DETAIL_PAGE_URL', 'PROPERTY_USER_ID'));


    if ($arParams["DETAIL_PATH"] != '' ) {
        $resNeighbors -> SetUrlTemplates( $arParams["DETAIL_PATH"]);
    }

    $arResult['LEFT_ELEM_ID'] = NULL;
    $arResult['LEFT_ELEM_DETAIL_PAGE_URL'] = NULL;
    $arResult['RIGHT_ELEM_ID'] = NULL;
    $arResult['RIGHT_ELEM_DETAIL_PAGE_URL'] = NULL;

    $leftElements = true;
    while($arElem = $resNeighbors->GetNext()) {
        
        if ($arElem['ID'] == $arResult['ID']) {
            $leftElements = false;
            continue;
        }

        if ($leftElements) {
            $arResult['LEFT_ELEM_ID'] = $arElem['ID'];
            $arResult['LEFT_ELEM_DETAIL_PAGE_URL'] = str_replace('#USER_ID#', $arElem["PROPERTY_USER_ID_VALUE"], $arElem['DETAIL_PAGE_URL']);

        }
        else {
            $arResult['RIGHT_ELEM_ID'] = $arElem['ID'];
            $arResult['RIGHT_ELEM_DETAIL_PAGE_URL'] = str_replace('#USER_ID#', $arElem["PROPERTY_USER_ID_VALUE"], $arElem['DETAIL_PAGE_URL']);
        }
    }


$rsUser = CUser::GetByID($arResult['USER_ID']);
            $arUser = $rsUser->Fetch();
            
            if ($arUser['NAME']) $name = $arUser['NAME'].' '.$arUser['LAST_NAME'];
            else $name = $arUser['LOGIN'];

$arResult['USER_NAME'] = $name;

if ($arParams['USER_PATH']) {
    $arResult['USER_LINK'] = str_replace('#USER_ID#', $arResult['USER_ID'], $arParams['USER_PATH']);
}


// is user owner?
$OWNER = ($USER->GetID() == $arResult['USER_ID']);


//get user info for marked users 

if ($arProps['USERS_MARKED'])
{
    $arResult['USERS_MARKED_SELECT2'] = Array();
    $order = array('sort' => 'asc');
    $tmp = 'sort'; 
    $dbUsers = CUser::GetList($order, $tmp, array('ID'=>implode(' | ',$arProps['USERS_MARKED'])));
    while ($arUser = $dbUsers->GetNext()) 
    {
        $arUser = Array (
              'id' => $arUser['ID'],
              'text' => ($arUser['NAME']?$arUser['NAME'].' '.$arUser['LAST_NAME']:$arUser['LOGIN'])
        );
        if (!$OWNER &&  $arUser['id']!= $USER->GetID()) $arUser['locked'] = true;
        
        $arResult['USERS_MARKED_SELECT2'][] = $arUser;
    }
}
         
            
// if USER is owner -> edit functions (friend list)
if ($arParams['USER_MARKED_ALLOWED'] && $OWNER && CModule::IncludeModule("socialnetwork"))
{    

    $arFriends = Array();
        
    $dbFriends = CSocNetUserRelations::GetRelatedUsers(
                        $arResult['USER_ID'], 
                        SONET_RELATIONS_FRIEND,
                        Array()
                       );
				   
					   
   while ($arFriend = $dbFriends->GetNext())
        {
            $key = ($arResult['USER_ID'] != $arFriend['FIRST_USER_ID']?'FIRST_USER_':'SECOND_USER_');
            $Friend = Array (
               'id' => $arFriend[$key.'ID'],
               'text' => ($arFriend[$key.'NAME']?$arFriend[$key.'NAME'].' '.$arFriend[$key.'LAST_NAME']:$arFriend['LOGIN'])		  
            );
            
            $arFriends[] = $Friend;
            
        }
        // add myself
        $arFriends[] =Array (
               'id' => $arResult['USER_ID'],
               'text' => $arResult['USER_NAME']            
            );      
   
   $arResult['FRIENDS'] = $arFriends;         
             
}       
$this->IncludeComponentTemplate();

