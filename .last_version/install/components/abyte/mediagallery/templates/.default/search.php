 <?
 global $USER;
 if(!CModule::IncludeModule("abyte.mediagallery")){
    $this->AbortResultCache();
    ShowError(GetMessage("mediagallery_MODULE_NOT_INSTALLED"));
    return;
  }

 $IsAuthorized = $USER->IsAuthorized();
?>

<!-- SEARCH BLOCK -->    
<div class="mediagallery_search <?=$IsAuthorized?'':'mediagallery_search--not-authorized'?>">
		<form method="get" action="" onsubmit="return abyteMG.submit_search_form(this);" >
		    <?if($IsAuthorized):?><div class="add_new ab_mg_btn ab_mg_btn--red" onclick="abyteMG.edit_video(); return false;"><?=GetMessage("AB_MG_LIST_NEW")?></div><?endif;?>
		    <input class="ab_mg_btn" type="submit" style="width: 120px;" value="<?=GetMessage("AB_MG_SEARCH_SUBMIT")?>">
			<div class="mediagallery_search_value-holder">
			    <input id="mediagallery_search_value" class="ab_mg_input" placeholder="<?=GetMessage("AB_MG_SEARCH_TITLE")?>" type="text" name="mg_search" value="<?=htmlspecialcharsbx($_REQUEST['mg_search'])?>" />			
            </div>
		</form>		
</div>

<!-- album edit block -->
<div id="mediagallery_album_edit" class="mg_modal popup-window" style="display: none;">
		<div class="mg_title ab_mg_title"><?=GetMessage("AB_MG_SECTION_LIST_EDIT")?></div>
		<form id="mediagallery_album_edit_form" method="post" action="<?=CAbyteMediaGallery::AjaxPath()?>" onsubmit="abyteMG.submit_edit_form(this); return false;" >
		<!-- service info -->
			<?=bitrix_sessid_post()?>	
			<input type="hidden" name="mediagallery_ajax" value="Y" />

			<input type="hidden" name="SECTION_ID" value="" />
			<input type="hidden" name="USER_ID" value="<?=$arResult['VARIABLES']['USER_ID']?>" />
			<input id="mediagallery_album_edit_id" type="hidden" name="ID" value="<?=$arResult['VARIABLES']['SECTION_ID']?>" />
			<p><label for="mediagallery_album_edit_name" class="ab_mg_btn ab_mg_btn--text"><?=GetMessage("AB_MG_SECTION_LIST_NAME")?></label><input id="mediagallery_album_edit_name" class="ab_mg_input" type="text" name="NAME" value="" /></p>
			<input type="submit" class="ab_mg_btn submit" value="<?=GetMessage("AB_MG_SECTION_LIST_SUBMIT")?>">
		</form>
		<a class="mg_modal-close-icon" href="#" onclick="abyteMG.CloseModalWindow(); return false;"></a>
</div>
