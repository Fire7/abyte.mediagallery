<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$AJAX_URL = CAbyteMediaGallery::AjaxPath();
$USER_ID = $USER->GetID();
$OWNER = $arResult['USER_ID'] == $USER_ID;
$this->AddEditAction($arParams['ID'], $arResult['EDIT_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_EDIT"));
$this->AddDeleteAction($arParams['ID'], $arResult['DELETE_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_DELETE"));

$PLAYER_WIDTH = $arParams['PLAYER_WIDTH'] > 0 ?  $arParams['PLAYER_WIDTH'] : 640;
$PLAYER_HEIGHT = $arParams['PLAYER_HEIGHT'] > 0 ? $arParams['PLAYER_HEIGHT'] + 24 : 384;

$SHOW_USERS_MARKED_BLOCK = isset($arResult['FRIENDS']) || isset($arResult['USERS_MARKED_SELECT2']) ? TRUE : FALSE;

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



<div id="<?=$this->GetEditAreaId($arParams['ID']); ?>" class="mediagallery_detail <?=$SHOW_USERS_MARKED_BLOCK ? 'mediagallery_detail--social' : ''?>">
<h1 class="ab_mg_title"><?=$arResult['NAME']?></h1>
<?
$provider = '';

if ($arResult['FILE'] > 0) {        
    switch ($arResult['CONTENT_TYPE']) {
        case 'VIDEO':
            $provider = 'video';
            break;
        case 'IMAGE':
            $provider = 'image';
            break;
        case 'AUDIO':
            $provider = 'sound';
            break;             
        default:                
            break;
    }  
}
elseif ($arResultp['VIDEO_LINK'] != '') {
     $provider = 'youtube';
}
?>
<div class="mediagallery_content">
<?if ($arResult['CONTENT_TYPE'] == 'IMAGE'):?>
    <div class="mediagallery_image">
        <img alt="<?=$arResult["NAME"]?>" title="<?=$arResult["NAME"]?>" src="<?=$arResult['FILE_SRC']?>" onclick="window.open('<?=$arResult['FILE_SRC']?>');" />
    </div>
<?else:?>
    
    <div class="mediagallery_player" style="width: <?=$PLAYER_WIDTH?>px; height: <?=$PLAYER_HEIGHT?>px;">
        <?$APPLICATION->IncludeComponent(
	"bitrix:player", 
	".default", 
	array(
		"PLAYER_TYPE" => "auto",
		"PATH" => $arResult["FILE_SRC"],
		"PROVIDER" => $provider,
		"STREAMER" => "",
		"WIDTH" => $PLAYER_WIDTH,
		"HEIGHT" => $PLAYER_HEIGHT,
		"PREVIEW" => $arResult['CONTENT_TYPE'] == 'AUDIO' ? $templateFolder.'/images/music.jpg' : ($arResult['PREVIEW_PICTURE'] > 0 ? CFile::ResizeImageGet($arResult['PREVIEW_PICTURE'], array('width'=>640, 'height'=>360), BX_RESIZE_IMAGE_EXACT)['src'] : ''),
		"FILE_TITLE" => $arResult["NAME"],
		"FILE_AUTHOR" => $arResult["USER_NAME"],
		"FILE_DATE" => $arResult["DATE_CREATE"],
		"FILE_DESCRIPTION" => $arResult["DETAIL_TEXT"],
		"SKIN_PATH" => "/bitrix/components/bitrix/player/mediaplayer/skins",
		"SKIN" => "nacht.zip",
		"CONTROLBAR" => "bottom",
		"WMODE" => "transparent",
		"PLUGINS" => array(
			0 => "",
			1 => "",
		),
		"PLUGINS_TWEETIT-1" => "tweetit.link=",
		"PLUGINS_FBIT-1" => "fbit.link=",
		"ADDITIONAL_FLASHVARS" => "",
		"WMODE_WMV" => "window",
		"SHOW_CONTROLS" => "N",
		"PLAYLIST_TYPE" => "xspf",
		"PLAYLIST_PREVIEW_WIDTH" => "64",
		"PLAYLIST_PREVIEW_HEIGHT" => "48",
		"SHOW_DIGITS" => "Y",
		"CONTROLS_BGCOLOR" => "FFFFFF",
		"CONTROLS_COLOR" => "000000",
		"CONTROLS_OVER_COLOR" => "000000",
		"SCREEN_COLOR" => "000000",
		"REPEAT" => "list",
		"VOLUME" => "90",
		"MUTE" => "N",
		"HIGH_QUALITY" => "Y",
		"SHUFFLE" => "N",
		"START_ITEM" => "1",
		"ADVANCED_MODE_SETTINGS" => "Y",
		"PLAYER_ID" => "",
		"BUFFER_LENGTH" => "10",
		"ADDITIONAL_WMVVARS" => "",
		"ALLOW_SWF" => "Y",
		"PLAYLIST_DIALOG" => "",
		"LOGO" => "",
		"LOGO_LINK" => "",
		"LOGO_POSITION" => "none",
		"PLAYLIST" => "bottom",
		"PLAYLIST_SIZE" => "180",
		"AUTOSTART" => "N",
		"PLUGINS_VIRAL-2" => "viral.onpause=false
            viral.oncomplete=true
            viral.allowmenu=false
            viral.functions=all
            viral.link=
            viral.email_subject=text
            viral.email_footer=text
            viral.embed=
            ",
		"PLUGINS_FLOW-1" => "flow.coverheight=100
            flow.coverwidth=150
            ",
		"PLUGINS_DRELATED-1" => "",
		"PLUGINS_HD" => "file=
            fullscreen=true
            ",
		"PLUGINS_REVOLT-1" => "",
		"DOWNLOAD_LINK" => "",
		"DOWNLOAD_LINK_TARGET" => "_self",
		"FILE_DURATION" => "",
		"COMPONENT_TEMPLATE" => ".default",
		"USE_PLAYLIST" => "N"
	),
	false
);?>
        </div>
<?endif;?>
	<?if($SHOW_USERS_MARKED_BLOCK):?>
	<div class="mediagallery_marked">
		<h2><?=GetMessage('AB_MG_USERS_MARKED')?>:</h2>
		<form id="ft_md_detail_form" action="">
			<input type="hidden" id="ft_md_select2friends" style="width: 300px;" />
			<script type="text/javascript">
				$(document).ready(function () {
					var data = <?=CUtil::PhpToJSObject($arResult['FRIENDS'])?> || [];

					$("#ft_md_select2friends").select2({
						width: 'auto',
						multiple: true,
						data: data,
						placeholder: '<?=GetMessage("AB_MG_DETAIL_MARK_FRIEND")?>',
						formatNoMatches: '<?=GetMessage('AB_MG_DETAIL_NOT_FOUND')?>'
					});

					$("#ft_md_select2friends").on("change", function(e) {					
						$.post(
							'<?=$AJAX_URL?>',
							{
								action : 'user_marked',
								mediagallery_ajax : 'Y',
								sessid: BX.bitrix_sessid(),
								ID:'<?=$arResult['ID']?>',
								data: {val:e.val, added:e.added, removed:e.removed}
							},
							function (ans) {
								if (!data.length && !$("#ft_md_select2friends").select2('data').length)  $("#ft_md_detail_form").css('display','none');

							}
						);

					});

					<?if($arParams['USER_PATH']):?>

					$('#ft_md_detail_form').on('mousedown', '.select2-search-choice div', function (e) {
						window['ab_mg_sel2_elem'] = e.target;
					});

					$('#ft_md_detail_form').on('mouseup', '.select2-search-choice div', function (e) {
						if(window['ab_mg_sel2_elem'] === e.target)
						{
							window.location = '<?=$arParams['USER_PATH']?>'.replace('#USER_ID#', $(e.target.parentNode).data('select2Data').id);
						}

						window['ab_mg_sel2_elem'] = null;
					});

					<?endif;?>

					if (!data.length)
					{
						$("#ft_md_select2friends").on("select2-opening select2-focus select2-blur", function (e) {
							e.preventDefault();
							return false;
						});

						$("#ft_md_select2friends").addClass('ft_md_select2friends--disabled');
					}					

					$("#ft_md_select2friends").select2('data', <?=CUtil::PhpToJSObject($arResult['USERS_MARKED_SELECT2'])?>);

					if (!data.length && !$("#ft_md_select2friends").select2('data').length)  $("#ft_md_detail_form").css('display','none');

				});
			</script>

		</form>
	</div>
	<?endif;?>
</div>
	<div class="mediagallery_controls">
		    <a class="ab_mg_section-link" href="<?=$arResult['SECTION_PAGE_URL']?>" title="<?=$arResult['SECTION_NAME']?>"><?=GetMessage('AB_MG_ALBUM').': '.$arResult['SECTION_NAME']?></a>
			<?if (isset($arResult['USER_LINK'])):?>
			<a class="ab_mg_user-link" href="<?=$arResult['USER_LINK']?>" title="<?=$arResult['USER_NAME']?>" ><?=$arResult['USER_NAME']?></a>
			<?endif;?>

			<div class="ab_mg_viewcount"><?=$arResult['VIEW_COUNT'].' '.getNumEnding($arResult['VIEW_COUNT'], Array(GetMessage("AB_MG_VIEWS_1"),GetMessage("AB_MG_VIEWS_4"),GetMessage("AB_MG_VIEWS_5")))?></div>

            <div class="ab_mg_copy">
                <? if ($USER_ID != $arResult['USER_ID']):?>
                <a href="#" onclick='abyteMG.copy_video(<?=$arResult['ID'].',"'.$AJAX_URL.'"'?>); event.preventDefault(); event.stopPropagation();' ><?=GetMessage("FT_MD_DETAIL_COPY")?></a>
                <?endif;?>
            </div>
        </div>
		<? if($arResult['LEFT_ELEM_ID'] != NULL):?>
			<a class="ab_mg_nav-link ab_mg_prev-link ab_mg_btn ab_mg_btn--text ab_mg_link" data-elementId="<?=$arResult['LEFT_ELEM_ID']?>" href="<?=$arResult['LEFT_ELEM_DETAIL_PAGE_URL']?>"><?=GetMessage('AB_MG_PREV_ELEMENT')?></a>
		<?endif;?>
		<? if($arResult['RIGHT_ELEM_ID'] != NULL):?>
			<a class="ab_mg_nav-link ab_mg_next-link ab_mg_btn ab_mg_btn--text ab_mg_link" data-elementId="<?=$arResult['RIGHT_ELEM_ID']?>" href="<?=$arResult['RIGHT_ELEM_DETAIL_PAGE_URL']?>"><?=GetMessage('AB_MG_NEXT_ELEMENT')?></a>
		<?endif;?>
    
</div>


