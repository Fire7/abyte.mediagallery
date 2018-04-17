<?
$APPLICATION->IncludeComponent(
    "abyte:mediagallery.detail", 
    ".default", 
    array(
        'ID' => $arResult['VARIABLES']['ELEMENT_ID'],     

		'USER_MARKED_ALLOWED' => $arParams['USER_MARKED_ALLOWED'],
		'PLAYER_WIDTH' => $arParams['PLAYER_WIDTH'],
		'PLAYER_HEIGHT' => $arParams['PLAYER_HEIGHT'],       
		"SET_STATUS_404" =>  $arParams['SET_STATUS_404'],

		// for back urls
		"USER_PATH" => $arResult['USER_PATH'],
		"SECTION_PATH" => $arResult['SECTION_PATH'],
		"DETAIL_PATH" => $arResult ['DETAIL_PATH'],
		'USER_ID' => $arResult['VARIABLES']['USER_ID'],
		"SECTION_ID" => $arResult['VARIABLES']['SECTION_ID'],
    ),
    $component
);?>