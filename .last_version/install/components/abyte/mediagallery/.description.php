<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
        "NAME" => GetMessage("abyte.mediagallery_COMPONENT_NAME"),
        "DESCRIPTION" => GetMessage("abyte.mediagallery_COMPONENT_DESCRIPTION"),
        "ICON" => "/images/icon.gif",
        "CACHE_PATH" => "Y",
        "SORT" => 70,
        "PATH" => array(
                "ID" => "Abyte",
                "CHILD" => array(
                        "ID" => "abyte_mediagallery_module",
                        "NAME" => GetMessage("abyte.mediagallery"),
                        "SORT" => 30,
                        "CHILD" => array(
                                "ID" => "abyte_mediagallery",
                        ),
                ),
        ),
         "COMPLEX" => "Y"
);


?>