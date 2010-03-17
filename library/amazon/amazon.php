<?php
/*

	filename:			amazon.php
	created:			7/17/2002, © 2002 php9.com Calin Uioreanu
	descripton:		controller script Amazon API 
	requirements:	

		- PHP with XML support
		- a Developer's token from Amazon (http://www.amazon.com/webservices)

*/

// configuration variables 
require_once('amazon_config.php');

// webservice class definition 
require_once('amazon_class.php');

if (!$_GET['Search']) {
	$_GET['Search'] = 'php';
}

?>

<html>
<head>
 <title>Amazon API : <?= ($_GET['Search']) ?></title>
<style type="text/css">
<!-- 
td { font-family: arial,helvetica,sans-serif; font-size: smaller; }
p { font-family: arial,helvetica,sans-serif; font-size: smaller; }
-->
</head>
</style>
</head>
<!--
Script generated with the Amazon PHP API from php9.com
Try it here: http://www.php9.com/amazon.php
//-->
<p>
<form method="get">
Search for <input type="text" name="Search" value="<?= ($_GET['Search'])?>"> 
 in 
<select name="Mode">
<?php 
	foreach ($arModes as $sMode => $sDisplay) {
		echo "\n". '	<option value="'. $sMode .'"';
		if ($sCurrentMode == $sMode) {
			echo ' selected';
		}
		echo '>'. $sDisplay .'</option>';
	}
?>
</select>
 sorted by 
<select name="SortBy">
<?php 
	foreach ($arModeSortType[$sCurrentMode] as $sModeSortType => $sDisplay) {
		echo "\n". '	<option value="'. $sModeSortType .'"';
		if ($sCurrentModeSortType == $sModeSortType) {
			echo ' selected';
		}
		echo '>'. $sDisplay .'</option>';
	}
?>
</select>
<input type="submit" value="Go">
</form>
</p>
<p>
<font size="-2" color="brown">
<?php

$arCurrentShops = array (
	'baby',
	'books',
	'camera',
	'classical',
	'computer',
	'dvd',
	'electronics',
	'games',
	'garden',
	'kitchen',
	'magazines',
	'music',
	'software',
	'tools',
	'toys',
	'video',
);

foreach ($arCurrentShops as $sShop) {
	echo 'Were you looking for <a href="http://simplest-shop.com/'. $sShop .'/search/'. ($_GET['Search']) .'">'. ($_GET['Search']) .'</a> in our <a href="http://simplest-shop.com/'. $sShop .'">'. $sShop .' shop</a> ?<br />';
}
?>
</font>
</p>
<?php

flush();

$oAmazon = new Amazon_WebService();

//$oAmazon->fp = fopen ($sUrl, 'r');
if (!$oAmazon->setInputUrl($sUrl, 20)) {
	die ('cannot open input file. exiting..' . '<a href='. $sUrl .'>@</a>');
}

// pass the output display template
$oAmazon->sTemplate = 'amazon_layout.php';

if (!$oAmazon->parse()) {
	die ('XMLParse failed');
}

$iTotalResuls = (int) $oAmazon->arAtribute['TotalResults'];

echo '<p> Displayed '. (int) $oAmazon->iNumResults .' results out of ' . $iTotalResuls .'.</p>';

// debugging: XML source 
// echo '<a href='. $sUrl .'>@</a>';
?>
<p>
Here is a small article with the code behind this Amazon PHP API implementation:<br /> <a href="http://www.php9.com/index.php/section/articles/name/Amazon%20PHP%20API">http://www.php9.com/index.php/section/articles/name/Amazon PHP API</a>
</p>
<table border="0" cellpadding="0" cellspacing="0" width="750" bgcolor="white">
	<tr>
		<td valign="top" align="center">
Copyright © 2001-2002 Calin Uioreanu, <a href="http://www.php9.com/">php9.com Weblog</a>. Powered by <a href="http://www.php9.com/amazon.php">Amazon PHP API</a>. All rights reserved. <br /> In association with <a href="http://www.amazon.com">Amazon.com</a>. Visit the <a href="http://simplest-shop.com">simplest shop</a> and our top categories: 
<?php

foreach ($arCurrentShops as $sShop) {
	echo '<a href="http://simplest-shop.com/'. $sShop .'">'. $sShop .'</a>; ';
}

?>
</td>
	</tr>
</table>