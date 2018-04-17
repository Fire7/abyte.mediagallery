<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?
$AJAX_URL = CAbyteMediaGallery::AjaxPath();
$OWNER = ($arParams['USER_ID'] == $USER->GetID());
$SectionEditTitle = CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "SECTION_EDIT");
$SectionDeleteTitle = CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "SECTION_DELETE");
$rsUser = CUser::GetByID($arParams['USER_ID']);
$arUser = $rsUser->Fetch();

if (!function_exists('getNumEnding')) {
		/**
	 * Функция возвращает окончание для множественного числа слова на основании числа и массива окончаний
	 * @param  $number Integer Число на основе которого нужно сформировать окончание
	 * @param  $endingsArray  Array Массив слов или окончаний для чисел (1, 4, 5),
	 *         например array('яблоко', 'яблока', 'яблок')
	 * @return String
	 */
	function getNumEnding($number, $endingArray)
	{
	    $number = $number % 100;
	    if ($number>=11 && $number<=19) {
	        $ending=$endingArray[2];
	    }
	    else {
	        $i = $number % 10;
	        switch ($i)
	        {
	            case (1): $ending = $endingArray[0]; break;
	            case (2):
	            case (3):
	            case (4): $ending = $endingArray[1]; break;
	            default: $ending=$endingArray[2];
	        }
	    }
	    return $ending;
	}
	
}
?>

<div class="mediagallery_section_list">    
<? foreach ($arResult['USER_SECTIONS'] as $UserSection):?>
    
    <h2 class="ab_mg_title"><?if ($OWNER) {
    	$str = GetMessage("AB_MG_SECTION_LIST_TITLE", Array ("#COUNT#" => $arResult['SECTION_COUNT']));
		$str.= ' ';
		$str.= getNumEnding($arResult['SECTION_COUNT'], Array(GetMessage("AB_MG_SECTION_LIST_TITLE_1"),GetMessage("AB_MG_SECTION_LIST_TITLE_4"),GetMessage("AB_MG_SECTION_LIST_TITLE_5")));
        }
        else $str = $UserSection['NAME']; 
?><?=$str?> <?if($OWNER):?><a class="add_new ab_mg_link" href="#" onclick="return abyteMG.edit_video_album();"><?=GetMessage("AB_MG_SECTION_LIST_NEW")?></a><?endif;?></h2>
    
    <div class="ab_mg_section_list">
    <? foreach ($UserSection['SUBSECTIONS'] as $Section):?>
        <? $undelete  = (strpos($Section['CODE'], 'undeletable') !== FALSE) ?>
        <? if (!$undelete) {          
            $this->AddEditAction($Section['ID'], $Section['EDIT_LINK'], $SectionEditTitle);
            $this->AddDeleteAction($Section['ID'], $Section['DELETE_LINK'], $SectionDeleteTitle);
        }  ?>
        <a id="<?=$undelete ? '' : $this->GetEditAreaId($Section['ID']);?>" href="<?=$Section['SECTION_PAGE_URL']?>" title="<?=$Section['NAME']?>" class="item<?=(strpos($Section['CODE'], 'undeletable') !== FALSE?' undeletable':'')?>">
        	<div class="img <?=$Section['PREVIEW_PICTURE']?'':' no-preview'?>" >
        	    <? if (!$undelete && $OWNER) :?>
        		<div class="control delete" title="<?=GetMessage("AB_MG_SECTION_LIST_DELETE")?>" onclick="if (confirm('<?=GetMessage("AB_MG_SECTION_LIST_CONFIRM")?>')) abyteMG.delete_album(<?=$Section['ID']?>);  event.preventDefault(); event.stopPropagation();" >
        		    <span></span>
        		</div>
        		<div class="control edit" title="<?=GetMessage("AB_MG_SECTION_LIST_EDIT")?>" onclick="abyteMG.edit_video_album(<?=$Section['ID']?>,'<?=$Section['NAME']?>'); event.preventDefault(); event.stopPropagation();" >
        		    <span></span>
        		</div>
        		<?endif;?>   
        		<?if($Section['PREVIEW_PICTURE']):?>    		
        		<img class="preview" width="175" height="175" src="<?=CFile::ResizeImageGet($Section['PREVIEW_PICTURE'], array('width'=>175, 'height'=>175), BX_RESIZE_IMAGE_EXACT  , true)['src'] ?>" />        		
        	    <?else:?>
        	    <div class="no-image">
        	        <img src="<?=$templateFolder.'/images/album-no-photo.svg'?>" />
        	    </div>
        	    <?endif;?>
        	</div>
            <h3><?=($undelete && !$OWNER) ? GetMessage("AB_MG_SECTION_LIST_ALBUM_WITH_USER", Array("#USER#" => $arUser['NAME'])) : $Section['NAME']?> <?=($undelete?'':'('.$Section['ELEMENT_CNT'].')')?></h3>
          
        </a> 
   <?endforeach;?>  
   </div>
<?endforeach;?>
</div>