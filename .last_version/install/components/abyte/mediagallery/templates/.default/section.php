
<?  
$component->includeComponentTemplate('search');
?>

<?$APPLICATION->IncludeComponent(
    "abyte:mediagallery.element.list", 
    ".default", 
    array(
		"CONTENT_TYPE_ALLOWED" => $arParams['CONTENT_TYPE_ALLOWED'],

		"USER_PATH" => $arResult['USER_PATH'],
        "DETAIL_PATH" => $arResult ['DETAIL_PATH'],
		"SECTION_PATH" =>  $arResult['SECTION_PATH'],
        
        "USER_ID" => $arResult['VARIABLES']['USER_ID'],
        "SECTION_ID" => $arResult['VARIABLES']['SECTION_ID'],
        "SECTION_LIST" => "",
        "IBLOCK_TYPE" => $arParams['IBLOCK_TYPE'],
        "IBLOCK_ID" => $arParams['IBLOCK_ID'],
        
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
        
        
        "CACHE_TYPE" => $arParams['CACHE_TYPE'],
        "CACHE_TIME" => $arParams['CACHE_TIME'],
		"CACHE_GROUPS" =>  $arParams['CACHE_GROUPS'],
		"SET_STATUS_404" =>  $arParams['SET_STATUS_404']
	),
    $component
);?>