<?php
/*

	filename:			amazon_search_config.php
	created:			7/17/2002, © 2002 php9.com Calin Uioreanu
	descripton:		configuration variables for Amazon Search Parser

*/

// replace this with your developement token obtained from Amazon
define('DEVELOPER_TOKEN','D37FFQXOC3MRYZ');

// if you have a valid Amazon associate Id, place it here to be rewarded for the traffic you generate
define('ASSOCIATE_ID', 'php9comweblot-20');

// after how many seconds the Amazon request should timeout
define('AWS_XMLFEED_TIMEOUT', 20);

// number of amazon.com websearch results to display
define('AWS_NUMBER_OF_RESULTS', 3);

// Amazon Web Services Search teaser table width
define('AWS_TABLE_WIDTH', 750);

// TD colors selection
global $arTeaser;
// teaser background selection
$arLightColors = array (
	'#FFFFDF',
	'#DDFFDD',
	'#D9FFFF',
	'#FFDDEE',
	'#FFDFDF',
	'#DDFFDD',
	'#FFDDDD',
	'#DFDFFF',
	'#EEEEEE',
	'#FFFFDF',
	'#FFDFFF'
);
srand ((float)microtime()*1000000);
shuffle ($arLightColors);
// teaser background random selection
$arTeaserColors = array_rand ($arLightColors, 3);
// populate colors Array
foreach ($arTeaserColors as $iKey) {
	$arTeaser[] = $arLightColors[$iKey];
}

//  Product page on Amazon.com
define('PRODUCT_DETAIL_URL','http://www.amazon.com/exec/obidos/ASIN/');

global $sSearchString;

if (!$sSearchString) {
	$sSearchString = $_GET['s'];
}

if (!$sCurrentMode = $_GET['Mode']) {
	// choose one mode from the ones above:
	$sCurrentMode = 'books';
}

$arModes = array (
	'baby' => 'baby (Baby)',
	'books' => 'books (Books)',
	'classical' => 'classical (Classical Music)',
	'dvd' => 'dvd (DVD)',
	'electronics' => 'electronics (Electronics)',
	'garden' => 'garden (Outdoor Living)',
	'kitchen' => 'kitchen (Kitchen & Housewares)',
	'magazines' => 'magazines (Magazines)',
	'music' => 'music (Popular Music)',
	'pc-hardware' => 'pc-hardware (Computers)',
	'photo' => 'photo (Camera & Photo)',
	'software' => 'software (Software)',
	'toys' => 'toys (Toys & Games)',
	'universal' => 'universal (Tools & Hardware)',
	'vhs' => 'vhs (Video)',
	'videogames' => 'videogames (Computer & Video Games)'
);

$sUrl  = 'http://xml.amazon.com/onca/xml?v=1.0';
$sUrl .= '&t='. ASSOCIATE_ID;
$sUrl .= '&dev-t='. DEVELOPER_TOKEN;
$sUrl .= '&mode=' . $sCurrentMode;
$sUrl .= '&type=lite&page=1';
$sUrl .= '&f=xml';
$sUrl .= '&KeywordSearch=';
$sUrl .= urlencode ($sSearchString);

?>