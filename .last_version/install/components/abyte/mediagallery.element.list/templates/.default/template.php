<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
?>
<?
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
global $USER;
$AJAX_URL = CAbyteMediaGallery::AjaxPath();
$USER_ID = $USER->GetID();
$OWNER = ($arParams['USER_ID'] == $USER_ID);
$ElementEditTitle = CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_EDIT");
$ElementDeleteTitle = CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_DELETE");
$VIDEO_ALLOWED = in_array('VIDEO',$arParams['CONTENT_TYPE_ALLOWED']);
$IMAGE_ALLOWED =  in_array('IMAGE',$arParams['CONTENT_TYPE_ALLOWED']);
$AUDIO_ALLOWED =  in_array('AUDIO',$arParams['CONTENT_TYPE_ALLOWED']);

if (isset($arResult['NAME'])) {
	$cnt = count ($arResult['ELEMENTS']);
	$arResult['TITLE_NAME']= $arResult['NAME']." ($cnt ".getNumEnding($cnt, Array(GetMessage("AB_MG_LIST_TITLE_1"),GetMessage("AB_MG_LIST_TITLE_4"),GetMessage("AB_MG_LIST_TITLE_5"))).')';
}
?>

<div class="mediagallery_elements_block" >
	<? if (isset($arResult['NAME'])):?>
	<h2 class="ab_mg_title"><?=$arResult['TITLE_NAME']?>
	<?endif;?>
	<? if (!$arResult['UNDELETABLE'] && isset($arResult['NAME']) && $OWNER):?>
		<!-- ALBUM CONTROLS -->		
		<a class="add_new ab_mg_link" 
	       href="#" 
	       title="<?=GetMessage("AB_MG_SECTION_LIST_DELETE")?>" 
	       onclick="if (confirm('<?=GetMessage("AB_MG_SECTION_LIST_CONFIRM")?>')) 
	                      abyteMG.delete_album(<?=$arParams['SECTION_ID']?>, <?=($arParams['SEF_FOLDER'] == NULL ? 'location.pathname' : "'".$arParams['SEF_FOLDER']."'")?>);  
                    event.preventDefault(); 
                    event.stopPropagation();
                    "><?=GetMessage("AB_MG_SECTION_LIST_DELETE")?></a>
		<a class="add_new ab_mg_link" href="#" title="<?=GetMessage("AB_MG_SECTION_LIST_EDIT")?>" onclick="abyteMG.edit_video_album(<?=$arParams['SECTION_ID']?>,'<?=$arResult['NAME']?>'); event.preventDefault(); event.stopPropagation();" ><?=GetMessage("AB_MG_SECTION_LIST_EDIT")?></a>
		
	<?endif;?></h2>

	<?if($arResult['USER_LINK']):?>
	<a class="back-link ab_mg_link" href="<?=$arResult['USER_LINK']?>" title="<?=$arResult['USER_NAME'].' ('.GetMessage("AB_MG_BACK_TO_ALBUM_LIST").')'?>" ><?=$arResult['USER_NAME'].' ('.GetMessage("AB_MG_BACK_TO_ALBUM_LIST").')'?></a>
	<?endif;?>

	<!-- VIDEO ADD OR EDIT FORM BEGIN -->
	<div id="mediagallery_form_add" style="display: none;">
		<h3><?=GetMessage("AB_MG_TERMS")?></h3>
		<?=GetMessage("AB_MG_TERMS_HTML")?>
		<!--
            UPLOAD INFO
        -->
		<?if (count($arResult["ERRORS"])):?>
			<?=ShowError(implode("<br />", $arResult["ERRORS"]))?>
		<?endif?>
		<?if (strlen($arResult["MESSAGE"]) > 0):?>
			<?=ShowNote($arResult["MESSAGE"])?>
		<?endif?>


		<form action="<?=$AJAX_URL?>" id="mediagallery_edit_form" name="element_add" method="post" enctype="multipart/form-data"  onsubmit="return abyteMG.onVideoAddSubmit(this);">
			<?=bitrix_sessid_post()?>
			<!-- <input type="hidden" name="action" value="element_update" />-->
			<input type="hidden" name="mediagallery_ajax" value="Y" />
			<input id="ab_mg_element_id" type="hidden" name="ID" value="" />
			<input type="hidden" name="USER_ID"  value="<?=$USER_ID?>" />
			<input type="hidden" name="IBLOCK_ID" value="<?=$arParams['IBLOCK_ID']?>" />


            <!-- file -->
            <div class="ab_mg_file-block">                
                <input id="ab_mg_add_file" type="file" size="" name="" accept="<?
				if ($VIDEO_ALLOWED)
					echo'video/avi,video/quicktime,video/mpeg,video/mp4,video/quicktime,video/x-flv,video/3gpp,video/MP2T';
				if ($AUDIO_ALLOWED)
					echo ',audio/mp3';
				if ($IMAGE_ALLOWED)
					echo ',image/jpeg,image/png,image/gif,image/x-icon,image/bmp';
                ?>" />
                <input id="ab_mg_file_info" type="hidden" name="FILE_INFO" value="" />
                <input id="ab_mg_file" type="hidden" name="FILE" value=""> 
                
                <!-- file link -->
                <a href="#" class="ab_mg_file-block__add-link ab_mg_file-block__action ab_mg_btn ab_mg_btn--red" onclick="$('.ab_mg_file-block__cancel').text('<?=GetMessage('CANCEL')?>').add($('#ab_mg_file_link')).show(); $('.ab_mg_file-block__action').hide(); return false;"><?=GetMessage("AB_MG_ADD_VIDEO_LINK")?></a>
                <input id="ab_mg_file_link" class="ab_mg_file-block__link ab_mg_input" type="text" name="VIDEO_LINK" value=""  placeholder="<?=GetMessage("AB_MG_ADD_VIDEO_LINK")?>">
				<div class="ab_mg_link ab_mg_file-block__cancel ab_mg_btn ab_mg_btn--text" style="display: none;" ><?=GetMessage('CANCEL')?></div>
            </div>

			<div class="ab_mg_clear"></div>
			<!-- video name -->
			<p>
				<label for="ab_mg_file_name" class="ab_mg_btn ab_mg_btn--text"><?=GetMessage("AB_MG_ADD_NAME")?></label>
				<input id="ab_mg_file_name" class="ab_mg_file-info ab_mg_input" name="NAME" type="text" value="" size="25" />
			</p>

			<!-- Choose Section -->
			<?  if($arResult['SECTION_LIST'] != ""):?>
				<p><label for="ab_mg_section_id" class="ab_mg_btn ab_mg_btn--text"><?=GetMessage("AB_MG_ADD_ALBUM_NAME")?></label>
					<select id="ab_mg_section_id" class="ab_mg_input" name ="SECTION_ID" onchange="abyteMG.onAlbumSelectChange(this);" >
						<?foreach ($arResult['SECTION_LIST']['SECTIONS'] as $id => $name):?>
							<option value="<?=$id?>" ><?=$name?></option>
						<?endforeach?>
						<option value="NEW"><?=GetMessage("AB_MG_ADD_ALBUM_NEW")?></option>
					</select>
					<!-- If new section -->
				<p id="section_name_holder" style="display: none;">
					<label for="ab_mg_section_name" class="ab_mg_btn ab_mg_btn--text"><?=GetMessage("AB_MG_ADD_ALBUM_NEW_NAME")?></label>
					<input id="ab_mg_section_name" class="ab_mg_input" name="SECTION_NAME" type="text" value="" size="25" />
					<input type="hidden" name="PARENT_SECTION" value="<?=$arResult['SECTION_LIST']['PARENT_SECTION']?>" />
				</p>
				</p>
				<script type="text/javascript">
					$(document).ready( function () {
						abyteMG.onAlbumSelectChange (document.getElementById('ab_mg_section_id'));
					});
				</script>
			<?endif?>

			<!-- video description -->
			<p>
				<label for="ab_mg_video_detail_text" class="ab_mg_btn ab_mg_btn--text"><?=GetMessage("AB_MG_ADD_DESCRIPTION")?></label>
				<textarea id="ab_mg_video_detail_text" class="ab_mg_file-info ab_mg_input" cols="30" rows="5" name="DETAIL_TEXT"></textarea>
			</p>	
			
			<!-- Submit -->
            <p>
                <input type="submit" class="ab_mg_video_submit ab_mg_btn" value="<?=GetMessage("IBLOCK_FORM_SUBMIT")?>" />
                <a href="#" class="cancel ab_mg_link ab_mg_btn ab_mg_btn--text" style="margin-left: 20px;" onclick="$('#mediagallery_form_add').slideUp('slow'); return false;"><?=GetMessage('CANCEL')?></a>
            </p>		
		</form>

	</div>
	<!-- VIDEO ADD OR EDIT FORM END -->

	<!-- VIDEO LIST BEGIN -->
	<div id="mediagallery_list">
		<?if (count($arResult['ELEMENTS'])):?>
		<div class="ab_mg_item_list">
			<?foreach ($arResult['ELEMENTS'] as $Item):?>
			<? $myself = ($Item['USER_ID'] == $USER_ID);
			$this->AddEditAction($Item['ID'], $Item['EDIT_LINK'], $ElementEditTitle);
			$this->AddDeleteAction($Item['ID'], $Item['DELETE_LINK'], $ElementDeleteTitle);
			?>
			<a id="<?=$this->GetEditAreaId($Item['ID']); ?>" href="<?=$Item['DETAIL_PAGE_URL']?>" title="<?=$Item['NAME']?>" class="item<?=( !$myself?'':'')?>" onclick='return abyteMG.open_video("<?=$Item['DETAIL_PAGE_URL']?>", false, false, <?=$arResult['GALLERY_ROOT'] ? 'true' : 'false'?>);'>
				<div class="img" >
					<div class="control <?=$Item['CONTENT_TYPE'] == 'IMAGE' ? 'zoom': 'play' ?> "><span></span></div>
				<?if($myself):?>
					<div class="control delete" title="<?=GetMessage("AB_MG_LIST_DELETE")?>"  onclick="if (confirm('<?=GetMessage("AB_MG_LIST_CONFIRM")?>')) abyteMG.delete_video(<?=$Item['ID'].','.$Item['IBLOCK_SECTION_ID']?>); event.stopPropagation(); event.preventDefault();"><span></span></div>
					<div class="control edit" title="<?=GetMessage("AB_MG_LIST_EDIT")?>"  onclick='abyteMG.edit_video(<?=\Bitrix\Main\Web\Json::encode($Item)?>); event.stopPropagation(); event.preventDefault();' ><span></span></div>
				<?elseif($USER_ID > 0):?>
					<?
					$copy_info = $Item;
					unset($copy_info['ID']);
					unset($copy_info['SECTION_ID']);
					?>
					<div class="control copy" title="<?=GetMessage("AB_MG_LIST_COPY")?>"  data-id="<?=$Item['ID']?>" data-ajaxurl="<?=$AJAX_URL?>" ><span></span></div>
				<?endif;?>				
				<?if ($Item['PREVIEW_PICTURE']):?>
					<img width="240" height="135" src="<?=CFile::ResizeImageGet($Item['PREVIEW_PICTURE'], array('width'=>240, 'height'=>135), BX_RESIZE_IMAGE_EXACT)['src'] ?>" />
				<?else:?>
					<div class="no-image" <?=$Item['CONTENT_TYPE'] == 'AUDIO' ? 'style="padding-left: 5%;"': ''?>>
						<img src="<?="$templateFolder/images/".($Item['CONTENT_TYPE'] == 'AUDIO' ? 'audio-no-preview.svg' : 'video-no-preview.svg') ?>" />
					</div>
				<?endif;?>
					<div class="info">
						<h3 title="<?=$Item['NAME']?>"><?=$Item['NAME']?></h3>
						<?if(isset($Item['SECTION_PAGE_URL'])):?>
						<span class="section-name" href="<?=$Item['SECTION_PAGE_URL']?>" title="<?=$Item['SECTION_NAME']?>"><?=$Item['SECTION_NAME']?></span>
						<?endif;?>
						<?if(isset($Item['USER_LINK'])):?>
						<span class="user-link ab_mg_link" data-link="<?=$Item['USER_LINK']?>" title="<?=$Item['USER_NAME']?>" onclick="location=$(this).data('link'); event.stopPropagation(); event.preventDefault();"><?=$Item['USER_NAME']?></span>
						<?endif?>
						<? $viewCnt = $Item['VIEW_COUNT'] > 0 ? $Item['VIEW_COUNT'] : 1; ?>
						<span class="view-count"><?=$viewCnt.' '.getNumEnding($viewCnt, Array(GetMessage("AB_MG_VIEWS_1"),GetMessage("AB_MG_VIEWS_4"),GetMessage("AB_MG_VIEWS_5")))?></span>
					</div>
				</div>

			</a>
			<?endforeach;?>
		</div>
		<? /*
		<?elseif():?>
		<div class="ab_mg_item_list_empty"></div>
 		*/ ?>
		<?endif;?>
	</div>
<!-- VIDEO LIST END -->
<!-- PAGE NAVIGATION -->
<div><?=$arResult["NAV_STRING"]?></div>
</div>

<script type="text/javascript">
$(document).ready(function(){
    
    $("#ab_mg_add_file").pekeUpload({
        url:'<?=$component->getPath()?>/file_upload.php' ,
        theme:'custom',
        showFilename: false,
        multi:false,
        allowedExtensions:"<?=$VIDEO_ALLOWED ? 'avi|mp4|3gp|mpeg|mov|flv|wmv' : ''?><?=$IMAGE_ALLOWED ? '|jpg|jpeg|png|bmp|gif|ico' : ''?><?=$AUDIO_ALLOWED ? '|mp3' : ''?>",
        btnText: '<?=GetMessage("AB_MG_ADD_VIDEO_FILE")?>',
		onStartUpload: function () {
			$('.ab_mg_file-block__cancel').text('<?=GetMessage('CANCEL')?>').show();
			$('.ab_mg_file-block__action').hide();

		},
		onFileError: function () {
			$('.ab_mg_file-block__cancel').text('<?=GetMessage('AB_MG_UPLOAD_ANOTHER_FILE')?>')
		},
		onFileSuccess: function () {
			$('.ab_mg_file-block__cancel').text('<?=GetMessage('AB_MG_UPLOAD_ANOTHER_FILE')?>')
		},
		invalidExtError: '<?=GetMessage("AB_MG_INVALID_FILE_TYPE")?>',
		sizeError: '<?=GetMessage("AB_MG_SIZE_ERROR")?>'
    });

	$('#ab_mg_file_link').on('blur', function (e) {
		var elem = $(this),
			youTubeLinkParse = elem.val().match(/([\/|\?|&]vi?[\/|=]|youtu\.be\/|embed\/)(\w+)/),
			ytId = youTubeLinkParse && youTubeLinkParse[youTubeLinkParse.length -1];
		if (ytId && elem.data('ytId') != ytId) {
			$('#ab_mg_file_name, #ab_mg_video_detail_text').add(elem).attr('disabled', 'disabled');
			elem.addClass('ab_mg_input--loading');
			abyteMG.getYouTubeData(ytId, function (data) {
				if (data && data.items && data.items.length && data.items[0].snippet) {
					var snippet = data.items[0].snippet;
					$('#ab_mg_file_name').val(snippet.title);
					$('#ab_mg_video_detail_text').val(snippet.description);
				}
				$('#ab_mg_file_name, #ab_mg_video_detail_text').add(elem).removeAttr('disabled');
				elem.removeClass('ab_mg_input--loading');
			});
		}

	});

	$('.ab_mg_file-block__cancel').on('click', function (e) {
		$('.ab_mg_file-block__action').show();
		$('#ab_mg_file_link, .ab_mg_file-block__cancel').hide();
		$('.ab_mg_file-info').val('');
		$('.pekecontainer').html('');
	});

	$('#mediagallery_modal').on('click', '.ab_mg_nav-link', function (e) {
		var elem = $(this);
		abyteMG.open_video(elem.attr('href'), true, elem.hasClass('ab_mg_prev-link'), <?=$arResult['GALLERY_ROOT'] ? 'true' : 'false'?>);
		e.preventDefault();
		return false;
	});

	$('#mediagallery_list .control.copy').on('click', function (e) {
		e.stopPropagation();
		e.preventDefault();
		var elem = $(this);

		if (elem.hasClass('loading') || elem.hasClass('success'))
			return;

		elem.addClass('loading');

		abyteMG.copy_video(elem.data('id'), elem.data('ajaxurl'), function (data) {
			elem.removeClass('loading');
			if (!data.ERROR.length)
				elem.addClass('success');
			else
				alert (data.ERROR.join('/n'));
		});

		return false;
	});
    
    

});
</script>