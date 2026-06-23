<?php
require_once __ROOT__ . '/model/modLocales.php';

$objLoc = new locales($_MYSQLI_);
$objLoc->setLocaleId(str_replace('-', '_', $chrLocale));
$arrLocale = $objLoc->selectLocale()['data'][0];
$chrLang = $arrLocale['language_id'];
$chrLocale = $arrLocale['locale_id'];
