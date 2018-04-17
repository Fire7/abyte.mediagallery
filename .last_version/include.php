<?
$MODULE_ID = basename(dirname(__FILE__));
CJSCore::RegisterExt('ft_mediagallery', array(    
            'js' => '/bitrix/js/'.$MODULE_ID.'/mediagallery.js',
            'css' => '/bitrix/js/'.$MODULE_ID.'/css/mediagallery.css',            
            'lang' => '/bitrix/modules/'.$MODULE_ID.'/lang/'.LANGUAGE_ID.'/mediagallery_js.php',  
            'rel' => array('jquery','popup')
            ));
            
CJSCore::RegisterExt('select2', array(    
    'js' => '/bitrix/js/'.$MODULE_ID.'/select2.js',
    'css' =>  '/bitrix/js/'.$MODULE_ID.'/css/select2.css',            
    'lang' => '',  
    'rel' => array('jquery')
));

Class CAbyteMediaGallery
{      
    public static function AjaxPath() 
    {
        $MODULE_ID = basename(dirname(__FILE__));
        return  "/bitrix/modules/$MODULE_ID/ajax/ajax.php";
    }
    
	function OnBuildGlobalMenu(&$aGlobalMenu, &$aModuleMenu)
	{
		if($GLOBALS['APPLICATION']->GetGroupRight("main") < "R")
			return;

	
		$aMenu = array(
			//"parent_menu" => "global_menu_services",
			"parent_menu" => "global_menu_settings",
			"section" => $MODULE_ID,
			"sort" => 50,
			"text" => $MODULE_ID,
			"title" => '',
//			"url" => "partner_modules.php?module=".$MODULE_ID,
			"icon" => "",
			"page_icon" => "",
			"items_id" => $MODULE_ID."_items",
			"more_url" => array(),
			"items" => array()
		);

		if (file_exists($path = dirname(__FILE__).'/admin'))
		{
			if ($dir = opendir($path))
			{
				$arFiles = array();

				while(false !== $item = readdir($dir))
				{
					if (in_array($item,array('.','..','menu.php')))
						continue;

					if (!file_exists($file = $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.$MODULE_ID.'_'.$item))
						file_put_contents($file,'<'.'? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/'.$MODULE_ID.'/admin/'.$item.'");?'.'>');

					$arFiles[] = $item;
				}

				sort($arFiles);

				foreach($arFiles as $item)
					$aMenu['items'][] = array(
						'text' => $item,
						'url' => $MODULE_ID.'_'.$item,
						'module_id' => $MODULE_ID,
						"title" => "",
					);
			}
		}
		$aModuleMenu[] = $aMenu;
	}
}
?>
