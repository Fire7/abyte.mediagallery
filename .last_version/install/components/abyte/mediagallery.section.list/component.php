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
CJSCore::Init(array('jquery', 'ft_mediagallery'));
//*********************************************************************************************************************************************************************
if($this->StartResultCache(false, array($arrFilter, ($arParams["CACHE_GROUPS"]==="N"? false: $USER->GetGroups())))){
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
$arCutResult = Array(); // Cutted list of section

//Select user section
$arResult['USER_SECTIONS'] = Array();
$arFilter = Array("ACTIVE"=>"Y",

 "IBLOCK_ID" => $arParams['IBLOCK_ID']
 
 );
 // Default -> just show all elements;
 if ($arParams['USER_ID'] == '') {
             $this->AbortResultCache();
            return;
 } else {
     $rsUser = CUser::GetByID($arParams['USER_ID']);
     if (!$arUser = $rsUser->Fetch()) {
         $this->AbortResultCache();
         ShowError(GetMessage("mediagallery_USER_ID_ERROR"));
         return;
     }
 }

if ($arParams['SECTION_CODE']) $arFilter['CODE'] = $arParams['SECTION_CODE'];
elseif ($arParams['USER_ID']) $arFilter['CODE'] = 'mediagallery_'.$arParams['USER_ID'];

// Page nav params
/*$arNavParams = array(
  "nPageSize" => $arParams["PAGE_SIZE"], 
  "bShowAll" => $arParams["bShowAll"],
);*/

$arSelect = Array (
    'ID',
    'CODE',
    'NAME',
    'DATE_CREATE',   
    'IBLOCK_ID',
    'PICTURE',
    'DETAIL_PICTURE',
    'DEPTH_LEVEL',
    'ELEMENT_CNT',
    'LIST_PAGE_URL',
    'SECTION_PAGE_URL',
    'DESCRIPTION'
   
);

  $DBUserSection = CIBlockSection::GetList(Array(), $arFilter, true, Array(), $arNavParams );    
  while($ArUserSection = $DBUserSection->GetNext())
  {
  	     
        
        $UserSection = createArrayParrams($ArUserSection, array_merge ($arSelect, Array('LEFT_MARGIN' , 'RIGHT_MARGIN')));   
      // Selecting subsections (albums);
      
      $arSections = Array();        
      $arSectionsFilter = Array (   'SECTION_ID'=>$UserSection['ID'], 
                                    'ACTIVE'=>'Y', 
                                    'ACTIVE_DATE'=>'Y' ,
                                     "IBLOCK_ID" => $arParams['IBLOCK_ID'] 
                                    // test, include subsections (future functionality)
                                  //  ,'<=LEFT_MARGIN' => $UserSection['LEFT_MARGIN'],  
                                  //  '>=RIGHT_MARGIN' => $UserSection['RIGHT_MARGIN']
                                 );
       $DBSection = CIBlockSection::GetList(Array(), $arSectionsFilter, true , Array(), $arNavParams);  
       
       if ($arParams["SECTION_PATH"] != '' ) {
           $DBSection -> SetUrlTemplates("", $arParams["SECTION_PATH"]);       }
       
       
       while($arSection = $DBSection->GetNext()) 
           {
               $Section = createArrayParrams($arSection, $arSelect);
               if (strpos($arSection['CODE'], 'undeletable') !== FALSE) {
                   if($arParams['SHOW_WITH_ME'] == 'Y' && CModule::IncludeModule("socialnetwork")) {
                             $Section['SECTION_PAGE_URL'] =  str_replace($Section['ID'], "withme", $Section['SECTION_PAGE_URL'] );
                   }
                   else {
                       continue;
                   }
               }
               else
                   $arCutResult[$Section['ID']] =  $Section['NAME'];

               $Section['SECTION_PAGE_URL'] = str_replace('#USER_ID#', $arParams["USER_ID"], $Section['SECTION_PAGE_URL']);


               $DBItems = CIBlockElement::GetList(Array(), Array('IBLOCK_ID' => $arParams['IBLOCK_ID'] , 'SECTION_ID'=> $Section['ID'], '>PREVIEW_PICTURE' => 0), false,  Array ("nTopCount" => 1), Array('PREVIEW_PICTURE'));
                 if ($arElement = $DBItems->GetNext()){                  
                     $Section['PREVIEW_PICTURE'] = $arElement['PREVIEW_PICTURE'];
                 }
              
              $arButtons = CIBlock::GetPanelButtons($Section["IBLOCK_ID"], 0, $Section["ID"], array(
                "SESSID" => false,
                "CATALOG" => true
                ));

                $Section["EDIT_LINK"] = $arButtons["edit"]["edit_section"]["ACTION_URL"];
                $Section["DELETE_LINK"] = $arButtons["edit"]["delete_section"]["ACTION_URL"];
                           
                             
               $arSections[]= $Section;
              
               
           }
     
    $UserSection['SUBSECTIONS'] = $arSections;         
    $arResult['USER_SECTIONS'][] = $UserSection; 
	$arResult['SECTION_COUNT'] = count($arCutResult);
    $arResult['CUT_RESULT'] = Array ('PARENT_SECTION' => $ArUserSection['ID'] , 'SECTIONS' => $arCutResult);
  }
	// If the new user and the section does not exist
	  if ( !count($arResult['USER_SECTIONS']) ) 
	  {
	  	
		// Creating user section
			$rsUser = CUser::GetByID($arParams['USER_ID']);
			$arUser = $rsUser->Fetch();
			
			if ($arUser['NAME']) $name = $arUser['NAME'].' '.$arUser['LAST_NAME'];
			else $name = $arUser['LOGIN'];
				
				$bs = new CIBlockSection;
				$arFields = Array(
						  "ACTIVE" => 'Y',							 
						  "IBLOCK_ID" => $arParams['IBLOCK_ID'],
						  "NAME" => $name,
						  "CODE" => 'mediagallery_'.$arParams['USER_ID'],
                          "CREATED_BY" =>  $arParams['USER_ID'],
						  "PICTURE" => '',
						  "DESCRIPTION" => '',
						  "DESCRIPTION_TYPE" => ''
						);
				$ID = $bs->Add($arFields);
				 $res = ($ID>0);									
				if(!$res)
				echo $bs->LAST_ERROR;
				// crating undeletable album  "Items with me " 
				else 
					{
						$bs = new CIBlockSection;
						$arFields['IBLOCK_SECTION_ID'] = $ID;
						$arFields['NAME'] = GetMessage("AB_MG_SECTION_LIST_UNDELETABLE");
						$arFields['CODE'] = $ID.'_undeletable';
						
						$undID = $bs->Add($arFields);
						 $res = ($undID>0);									
						if(!$res)
						echo $bs->LAST_ERROR;
						
					}
			// restart component to select new albums
        $this->AbortResultCache();
		$this->executeComponent();
        return;
	  }

$this->IncludeComponentTemplate();
}

return  $arResult['CUT_RESULT'];