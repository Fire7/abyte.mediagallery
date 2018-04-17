<?
$component->includeComponentTemplate('search');
?>

<div class="mediagallery_index">

<div class="mediagallery_user-list">
    <?if($USER->IsAuthorized()):?>
    <a class="ab_mg_btn" href="<?=$arResult['SELF_GALLERY_URL']?>" title="<?=GetMessage('AB_MG_MY_GALLERY')?>" style="color: white;" ><?=GetMessage('AB_MG_MY_GALLERY')?></a>
    <?endif;?>
    <h3><?=GetMessage('AB_MG_USER_GALLERIES')?></h3>
    <div class="list">
        <?foreach ($arResult['USERS'] as $key => $gallery):?>
            <a class="ab_mg_link ab_mg_btn ab_mg_btn--text" href="<?=$gallery['URL']?>" title="<?=$gallery['NAME'].' ('.$gallery['ELEMENT_CNT'].')'?>" ><?=$gallery['NAME'].' ('.$gallery['ELEMENT_CNT'].')'?></a>
        <?endforeach;?>
    </div>
</div>

<?$APPLICATION->IncludeComponent(
    "abyte:mediagallery.element.list",
    ".default",
    array(

        "CONTENT_TYPE_ALLOWED" => $arParams['CONTENT_TYPE_ALLOWED'],

        "SEF_FOLDER" =>$arParams ['SEF_FOLDER'],
        "SECTION_PATH" =>  $arResult['SECTION_PATH'],
        "DETAIL_PATH" => $arResult ['DETAIL_PATH'],
        "USER_PATH" => $arResult['USER_PATH'],
        "USER_ID" => "0",
        "SUBSECTION" => "",
        "SECTION_LIST" => $AlbumList,
        "IBLOCK_TYPE" => $arParams['IBLOCK_TYPE'],
        "IBLOCK_ID" => $arParams['IBLOCK_ID'],
        "PAGE_SIZE" => $arParams['PAGE_SIZE'],
        "CACHE_TYPE" => $arParams['CACHE_TYPE'],
        "CACHE_TIME" => $arParams['CACHE_TIME'],
        "CACHE_GROUPS" =>  $arParams['CACHE_GROUPS'],
        "SET_STATUS_404" =>  $arParams['SET_STATUS_404'],

        // Nav settings
        "PAGE_SIZE" => $arParams['PAGE_SIZE'],
        "DISPLAY_TOP_PAGER" => $arParams["DISPLAY_TOP_PAGER"],
        "DISPLAY_BOTTOM_PAGER" => $arParams["DISPLAY_BOTTOM_PAGER"],
        "PAGER_TITLE" => $arParams["PAGER_TITLE"],
        "PAGER_SHOW_ALWAYS" => $arParams["PAGER_SHOW_ALWAYS"],
        "PAGER_TEMPLATE" => $arParams["PAGER_TEMPLATE"],
        "PAGER_DESC_NUMBERING" => $arParams["PAGER_DESC_NUMBERING"],
        "PAGER_DESC_NUMBERING_CACHE_TIME" => $arParams["PAGER_DESC_NUMBERING_CACHE_TIME"],
        "PAGER_SHOW_ALL" => $arParams["PAGER_SHOW_ALL"],
    ),
    $component
);?>

</div>