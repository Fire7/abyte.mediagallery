<?
// if(CModule::IncludeModule("user_video"))


if ($_REQUEST['mediagallery_ajax'] == 'Y')
{
	
	if (!function_exists('check_permission ')) 
	{
		// function to check user access to section
		function check_permission ($ID) 
		{
				if ($ID) 
				{
					$return = Array();
					$DBSection = CIBlockSection::GetByID($ID);
					if($section = $DBSection->GetNext())
					{
						if ($section['CREATED_BY'] != $GLOBALS["USER"]->GetID()) return Array('STATUS'=>0, 'ERROR' =>'Permission Denied', 'SECTION' => $section);
						if (strpos($section['STATUS'], 'undeletable') !== FALSE) return Array('STATUS'=>0, 'ERROR' => 'Undeletable section', 'SECTION' => $section);
						
						return Array('STATUS' => 1, 'SECTION' => $section);
					}
					else return Array('STATUS' => 0, 'ERROR' => 'SECTION NOT FOUND');	
				}
				else return Array('STATUS' => 0, 'ERROR' => 'ID IS NULL');			
		}
		
	}
	
	if (!function_exists('section_update'))
	{
		function section_update ($ID,$NAME,$SECTION_ID=false, &$err)
		{		
			$return= Array();
			
			// Check permisson
			
				
				if ($ID>0) {
					// if id -> update section
					$data = check_permission($ID);
					$section_id = $data['SECTION']['IBLOCK_SECTION_ID'];
					$return['PARENT_SECTION'] = $data['SECTION'];
				}
				elseif ($SECTION_ID) {
					// if no id -> create new section (album)
					$data = check_permission($SECTION_ID);
					$section_id = $data['SECTION']['ID'];
					$return['PARENT_SECTION'] = $data['SECTION'];
				}                
					if ($data['STATUS'])
					{
							$bs = new CIBlockSection;
							$arFields = Array(
							  "ACTIVE" => 'Y',
							  "IBLOCK_SECTION_ID" => $section_id,
							  "IBLOCK_ID" => $data['SECTION']['IBLOCK_ID'],
							  "NAME" => $NAME,
							  "SORT" => $data['SECTION']['SORT'],
							  "PICTURE" => '',
							  "DESCRIPTION" => '',
							  "DESCRIPTION_TYPE" => ''
							  );
							
							if($ID)
							{								
								$res = $bs->Update($ID, $arFields);	
								 $return['ID'] = $ID;																			
							}
							else
							{
							  	 $return['ID'] = $bs->Add($arFields);
							  	$res = ($return['ID']>0);
							}
							
							if(!$res)
							  $err[] = $bs->LAST_ERROR;
							
					}
					else $err[] = $data['ERROR'];			
									 
			 if (count($err)) $return['STATUS'] = 0;
			 else $return['STATUS'] = 1;
			 			 
			return $return;
		}
	}
	
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
	$action = (isset($_REQUEST["action"]) && is_string($_REQUEST["action"])) ? trim($_REQUEST["action"]): "";
	
	
	if (CModule::IncludeModule("iblock"))
	{
		$arReturn = Array('ERROR'=>Array());

        //fix encoding
        if (isset( $_REQUEST['NAME']))
            $_REQUEST['NAME'] = iconv(mb_detect_encoding ($_REQUEST['NAME']), LANG_CHARSET, $_REQUEST['NAME']);

        if (isset($_REQUEST['SECTION_NAME']))
            $_REQUEST['SECTION_NAME'] = iconv(mb_detect_encoding ($_REQUEST['SECTION_NAME']), LANG_CHARSET, $_REQUEST['SECTION_NAME']);

        if (isset($_REQUEST['VIDEO_LINK']))
            $_REQUEST['VIDEO_LINK'] = iconv(mb_detect_encoding ($_REQUEST['VIDEO_LINK']), LANG_CHARSET, $_REQUEST['VIDEO_LINK']);

        if (isset($_REQUEST['DETAIL_TEXT']))
            $_REQUEST['DETAIL_TEXT'] = iconv(mb_detect_encoding ($_REQUEST['DETAIL_TEXT']), LANG_CHARSET, $_REQUEST['DETAIL_TEXT']);

        if (isset($_REQUEST['FILE_INFO']))
            $_REQUEST['FILE_INFO'] = iconv(mb_detect_encoding ($_REQUEST['FILE_INFO']), LANG_CHARSET, $_REQUEST['FILE_INFO']);


		if (!$GLOBALS["USER"]->IsAuthorized())
			$arReturn[0] = "*a";
		elseif (!check_bitrix_sessid())
			$arReturn[0] = "*s";


		
        
		// Section (album) action
		elseif ($action == 'section_update') 
		{
			if ($_REQUEST['NAME'] == "") $arReturn['ERROR'][] = "Empty Name";
            
            // Get section ID by param or by USER_ID
            $SECTION_ID = $_REQUEST['SECTION_ID'];            
            if (!$SECTION_ID && $_REQUEST['USER_ID']) {                
                     $DBUserSection = CIBlockSection::GetList(Array(), array('CODE'=>'mediagallery_'.$_REQUEST['USER_ID']), false, Array('ID'));
                     if($ArUserSection = $DBUserSection->GetNext()) {
                         $SECTION_ID = $ArUserSection['ID'];
                     }                               
            }            
            
            if (!$SECTION_ID > 0) {
                $arReturn['ERROR'][] = 'Section ID error';
            }
           
            
			if (!count($arReturn['ERROR'])) 
			{			    
				section_update($_REQUEST['ID'], $_REQUEST['NAME'], $SECTION_ID, $arReturn['ERROR']);			
			}			
		}
        
        // delete section        
		elseif ($action == 'section_delete')
		{
			$data = check_permission($_REQUEST['ID']);
			
			if ($data['STATUS']) 
			{
				global $DB;
				$DB->StartTransaction();
			    if(!CIBlockSection::Delete($_REQUEST['ID']))
			    {
			        $arReturn['ERROR'][] = 'DB err';
			        $DB->Rollback();
			    }
			    else
			        $DB->Commit();
			}
			else $arReturn['ERROR'][] = $data['ERROR'];	
			
		}
		
		
		// update/add element
		elseif ($action == 'element_update')
		{
			// if we already have section_ID
			if ($_REQUEST['SECTION_ID'] > 0)
			{
				$data = check_permission($_REQUEST['SECTION_ID']);
				$section_id = $_REQUEST['SECTION_ID'];
				$iblock_id = $data['SECTION']['IBLOCK_ID'];
			}          
			else
			{			  
				//check fields for section add
				if ($_REQUEST['NAME'] == "") $arReturn['ERROR'][] = "Empty Section Name";
				if (!$_REQUEST['PARENT_SECTION']>0) $arReturn['ERROR'][] = "Parent section Error";
				
				if (!count($arReturn['ERROR']))
				{
				 	$data = section_update(false, $_REQUEST['SECTION_NAME'], $_REQUEST['PARENT_SECTION'], $arReturn['ERROR']);
					$section_id = $data['ID'];
					$iblock_id = $data['PARENT_SECTION']['IBLOCK_ID'];
					
				}
			}
			
			$arReturn['REQUEST'] = $_REQUEST;
		
			// check fields
			if ($_REQUEST['NAME'] == "") $arReturn['ERROR'][] = "Empty Name";
			if ($_REQUEST['VIDEO_LINK'] == "" && !$_REQUEST['FILE'] && !$_REQUEST['FILE_INFO']) 	$arReturn['ERROR'][] = "Video Error";	
							
			if ($data['STATUS'] && !count($arReturn['ERROR']))
			{
				// if update
			  	if ($_REQUEST['ID'] > 0)
			  	{
			  	    // SELECT all props for update
			  	    $PROPS = Array(                       
                        'VIDEO_LINK' => $_REQUEST['VIDEO_LINK'],
                        // 'USER_MARKED' => $_REQUEST['USER_MARKED'],                    
                    );			  	    
				    $res = CIBlockElement::GetByID($_REQUEST['ID']);
                            if($obRes = $res->GetNextElement())
                            {
                                $arProps = $obRes->GetProperties();
                                foreach ($arProps as $key => $value) 
                               {
                                   if ($value['PROPERTY_TYPE'] == 'L')
                                    $arProps[$key] = Array("VALUE" => $value['VALUE_ENUM_ID']);
                                   else
                                    $arProps[$key] = $value['VALUE'] ;
                               }
                            }

                $PROPS = array_merge($arProps,$PROPS);
                $arReturn['PROPS'] = $PROPS;
                
				}
                // if new
				else 
				{
					$PROPS = Array(
						'USER_ID' => $_REQUEST['USER_ID'],
						'VIDEO_LINK' => $_REQUEST['VIDEO_LINK'],
						'FILE' => $_REQUEST['FILE']						
					);
				}
                
                $DETAIL_PICTURE = null;
				
				// if file then add
				if ($_REQUEST['FILE_INFO'])
				{
					$video_file = json_decode($_REQUEST['FILE_INFO']);											
					$arFile = (array)$video_file;                   
                   
                    if (strpos($arFile['type'], 'video') !== FALSE) {                                                                   
                        $type = 'VIDEO';  
                        //creating preview
                        $file = $arFile['tmp_name'];
                        exec("avconv -i $file -f image2 -frames:v 1 $file.png");                      
                        $DETAIL_PICTURE = CFile::MakeFileArray(CFile::SaveFile(CFile::MakeFileArray("$file.png"), "mediagallery"));
                    } 
                    elseif (strpos($arFile['type'], 'image') !== FALSE) {                        
                            $type = 'IMAGE';                               
                    }
                    elseif (strpos($arFile['type'], 'audio') !== FALSE) {
                             $type = 'AUDIO';     
                    }
                    else {
                        $arReturn['ERROR'][] = "Invalid file type";  
                        $type = 'undefined';                      
                    }  
                    
                     $property_enums = CIBlockPropertyEnum::GetList(Array(), Array("IBLOCK_ID"=>$iblock_id, "CODE"=>"CONTENT_TYPE", "VALUE" => $type));
                        if($enum_fields = $property_enums->GetNext()) {
                            $PROPS['CONTENT_TYPE'] = Array("VALUE" => $enum_fields['ID']);                            
                        }
                        else {
                             $arReturn['ERROR'][] = "Iblock file type error";  
                        }   
                    
                    if (!count($arReturn['ERROR'])) {
                        // if have old file then delete it!
                        if (isset($PROPS['FILE'])) 
                        {
                            $arFile['old_file'] = $PROPS['FILE'];
                            $arFile['del'] = 'Y';
                        }                                         
                      
                        $arReturn['ARFILE'] = $arFile;                        
                        $PROPS['FILE'] = CFile::SaveFile($arFile, "mediagallery");  
                        
                        // preview for images
                        if ($type == 'IMAGE') {
                            $DETAIL_PICTURE = CFile::MakeFileArray($PROPS['FILE']);
                        }
                            
                    }                    
				}
                
                // creating preview for youtube video
                if ($_REQUEST['VIDEO_LINK'])
                {
                    preg_match("#([\/|\?|&]vi?[\/|=]|youtu\.be\/|embed\/)(\w+)#", $_REQUEST['VIDEO_LINK'], $matches);
                    $YouTubeId = end($matches); 
                    
                    //TODO: copy images, mb audios ?
                    if ($YouTubeId)
                    {
                        $DETAIL_PICTURE = CFile::MakeFileArray(CFile::SaveFile(CFile::MakeFileArray("http://img.youtube.com/vi/$YouTubeId/hqdefault.jpg"), "mediagallery"));
                        $type = 'VIDEO';

                        $property_enums = CIBlockPropertyEnum::GetList(Array(), Array("IBLOCK_ID"=>$iblock_id, "CODE"=>"CONTENT_TYPE", "VALUE" => $type));
                        if($enum_fields = $property_enums->GetNext()) {
                            $PROPS['CONTENT_TYPE'] = Array("VALUE" => $enum_fields['ID']);
                        }
                        else {
                            $arReturn['ERROR'][] = "Iblock file type error";
                        }
                    }
                    else {
                        IncludeModuleLangFile(__FILE__);
                        $arReturn['ERROR'][] = GetMessage("AB_MG_INVALID_LINK");
                    }
                }
				
                
                if (!count($arReturn['ERROR'])) {
                    $el = new CIBlockElement;
                                
                    $arLoadProductArray = Array(
                      "MODIFIED_BY"    => $USER->GetID(),
                      "IBLOCK_SECTION_ID" => $section_id,
                      "IBLOCK_ID"      => $iblock_id,
                      "PROPERTY_VALUES"=> $PROPS,
                      "NAME"           => $_REQUEST['NAME'],
                      "ACTIVE"         => "Y",            // активен                     
                      "DETAIL_TEXT"    => $_REQUEST['DETAIL_TEXT'],
                      "PREVIEW_PICTURE" => $DETAIL_PICTURE      
                      );                 
                    
                    $arReturn['IBLOCK_ARRAY'] = $arLoadProductArray;

                    if ($_REQUEST['ID'] > 0)                        
                        $res = $el->Update($_REQUEST['ID'], $arLoadProductArray);
                    else   
                        $res = $el->Add($arLoadProductArray);

                    
                    if ($res)
                       $arReturn['Element add/update'] = $res;
                    else
                       $arReturn['ERROR'][] = "Error: ".$el->LAST_ERROR;
                }				
				
			}
			
			
		}

        // copy element!
        elseif ($action == 'element_copy')
        {
            if ($_REQUEST['ID'] > 0)
            {
                // first found section to add
                $section_id = false;
                $USER_ID = $GLOBALS["USER"]->GetID();
                $DBUserSection = CIBlockSection::GetList(Array(), array('CODE'=>'mediagallery_'.$USER_ID), true, Array());  
                  if($ArUserSection = $DBUserSection->GetNext())
                  {
                       $arFilter = Array (   'SECTION_ID'=>$ArUserSection['ID'], 
                                    'ACTIVE'=>'Y', 
                                    'ACTIVE_DATE'=>'Y' ,
                                     "IBLOCK_ID" => $ArUserSection['IBLOCK_ID'] 
                                     );
                        $DBSection = CIBlockSection::GetList(Array(), $arFilter);  
                        while($arSection = $DBSection->GetNext())  
                        {
                             if (strpos($arSection['CODE'], 'undeletable') === FALSE) {$section_id = $arSection['ID']; break;}
                        }

                        // if no section the create new with default name
                        if (!$section_id)
                        {
                            IncludeModuleLangFile(__FILE__);
                            $data = section_update(false, GetMessage("AB_MG_NEW_ALBUM"), $ArUserSection['ID'], $arReturn['ERROR']);
                            $section_id = $data['ID'];
                        }
                        
                        // now create new elem
                        if (!count($arReturn['ERROR']));
                        {
                            
                            $DBres = CIBlockElement::GetByID($_REQUEST['ID']);
                             if($ArElem = $DBres->GetNextElement())
                             {

                                 $arFields = $ArElem->GetFields();
                                 $arProps = $ArElem->GetProperties(); 
                                 
                                   $PROPS = Array(
                                        'USER_ID' =>  $USER_ID,
                                        'VIDEO_LINK' =>  $arProps['VIDEO_LINK']['VALUE'],
                                        'FILE' => $arProps['FILE']['VALUE'],
                                        'CONTENT_TYPE' => $arProps['CONTENT_TYPE']['VALUE_ENUM_ID']
                                    );
                            
                            $el = new CIBlockElement;

                                $arLoadProductArray = Array(
                                  "MODIFIED_BY"    => $USER_ID,
                                  "IBLOCK_SECTION_ID" => $section_id,
                                  "IBLOCK_ID"      => $ArUserSection['IBLOCK_ID'],
                                  "PROPERTY_VALUES"=> $PROPS,
                                  "NAME"           => $arFields['NAME'],
                                  "ACTIVE"         => "Y",            // активен                     
                                  "DETAIL_TEXT"    => $arFields['DETAIL_TEXT'],
                                  "PREVIEW_PICTURE" => $arFields['PREVIEW_PICTURE'] > 0 ? CFile::MakeFileArray( $arFields['PREVIEW_PICTURE']) : ''
                                  );
                            
                            $res = $el->Add($arLoadProductArray);
                    
                            if ($res)
                               $arReturn['Element add/update'] = $res;
                            else
                               $arReturn['ERROR'][] = "Error: ".$el->LAST_ERROR;
                                    
                             }                                                          
                         }                        
                  }            
            }
            else $arReturn['ERROR'][] = 'ID error';
            
        }


        // delete element
        elseif ($action == 'element_delete')
        {
            $data = check_permission($_REQUEST['SECTION_ID']);
            
            if ($data['STATUS']) 
            {                  
                 // check FILE
               $db_props = CIBlockElement::GetProperty($data['SECTION']['IBLOCK_ID'], $_REQUEST['ID'], array(), Array("CODE"=>"FILE"));
                    if($ar_props = $db_props->Fetch())
                    {                         
                         $arFilter = Array(
                         "!ID" => $_REQUEST['ID'],
                             "IBLOCK_ID" => $data['SECTION']['IBLOCK_ID'],
                            "PROPERTY_FILE" => $ar_props["VALUE"]
                            );
                            
                            $save_flag = false;
                            
                           $DBItems = CIBlockElement::GetList(Array(), $arFilter, false, false );  
                            while($arElem = $DBItems->GetNext()) 
                            {
                                $save_flag = true;                               
                                break;             
                            }
                            
                            $arReturn['SAVE_VIDEO'] =   $save_flag;
                            
                            // if noone more uses this file delete it
                            if (!$save_flag) {
                                 CFile::Delete($ar_props["VALUE"]);
                            }
                        
                    }
                       
                    else
                          $arReturn['DELETE_VIDEO_FILE'] = "NO FILE";
                          
                      
                 // delete iblock element              
                global $DB;
                $DB->StartTransaction();
                if(!CIBlockElement::Delete($_REQUEST['ID']))
                {
                    $arReturn['ERROR'][] = 'DB err';
                    $DB->Rollback();
                }
                else
                    $DB->Commit();
                    
                    
            }
            else $arReturn['ERROR'][] = $data['ERROR']; 
            
        }


        // user_marked action        
        elseif ($action == 'user_marked')
        {
            if ($_REQUEST['ID']>0)
            {
                 $update = false; 
                 $res = CIBlockElement::GetByID($_REQUEST['ID']);
                 if($obRes = $res->GetNextElement())
                 {
                     $arFields = $obRes->GetFields();
                     $arProps = $obRes->GetProperties(); 
                     $arMarked = $arProps['USERS_MARKED']['VALUE'];
                                        
                     $OWN = ($arProps['USER_ID']['VALUE'] == $GLOBALS["USER"]->GetID()); 
                                     
                        if ($_REQUEST['data']['added']) 
                        {
                            if (!$OWN) $arReturn['ERROR'][] = 'Permission Denied';                    
                            if (!in_array($_REQUEST['data']['added']['id'], $arMarked) && !count($arReturn['ERROR'])) 
                            {
                              $update = true;                          
                              $arMarked[] = $_REQUEST['data']['added']['id'];                                        
                              $arReturn['user_marked_2'] = $arMarked;
                            } 
                                         
                        } 
                        elseif ($_REQUEST['data']['removed'])
                        {
                            if (!($OWN || $_REQUEST['data']['removed']['id'] == $GLOBALS["USER"]->GetID())) $arReturn['ERROR'][] = 'Permission Denied';
                            if (in_array($_REQUEST['data']['removed']['id'], $arMarked) && !count($arReturn['ERROR'])) 
                            {
                              $update = true;                          
                              unset($arMarked[array_search($_REQUEST['data']['removed']['id'], $arMarked)]);                                        
                              $arReturn['user_marked_2'] = $arMarked;
                            } 
                        }
                }
                else $arReturn['ERROR'][] = 'DB_ID error';
                if (!count ($arReturn['ERROR']) && $update)
                {
                     CIBlockElement::SetPropertyValues($arFields['ID'], $arFields['IBLOCK_ID'], $arMarked, 'USERS_MARKED');
                                             
                                             $test = CIBlockElement::GetByID($_REQUEST['ID']);
                                                if($obtest = $test->GetNextElement())
                                                {
                                                     $testProps = $obtest->GetProperties();
                                                      $arReturn['USERS_MARKED_RESULT'] = $testProps['USERS_MARKED']['VALUE'];
                                                }
                }
                
                
            }
            else $arReturn['ERROR'][] = 'ID error';
           
        }

		echo \Bitrix\Main\Web\Json::encode($arReturn);
	}
}


?>