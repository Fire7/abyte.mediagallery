<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
IncludeModuleLangFile(__FILE__);
if (move_uploaded_file($_FILES['file']['tmp_name'], $_FILES['file']['tmp_name'])) {
    echo 'OK';
    echo '<script>	
		$("#ab_mg_file_info").val(JSON.stringify('.\Bitrix\Main\Web\Json::encode($_FILES['file']).'));
        $("#ab_mg_external_file_link").css("display","none");
        </script>';
} else {
    echo 'Possibly Attack';
}
?>