<?php
/*

	filename:			amazon_search_layout.php
	created:			7/17/2002, © 2002 php9.com Calin Uioreanu
	descripton:		display template for Amazon Search API 

*/

// number of Amazon search teaser results to display
if ($this->iNumResults >= AWS_NUMBER_OF_RESULTS) {
	return; // silent
}

$sProductUrl = $sBookUrl;
$sProductUrl = $sProductUrl .'/'. ASSOCIATE_ID;

global $arTeaser;

$sTdColor = $arTeaser[(int) $this->iNumResults];

// if Amazon returned an empty Image (1x1), do not display the HTML code
$arSize = getimagesize ($IMAGEURLSMALL);
if ((int) $arSize[1] == 1 ) {
	$sImageUrl = '';	
} else {
	$sImageUrl = '<a href="http://shop.php9.com/books.php/Mode/product/AsinSearch/'. $ASIN .'/name/'. rawurlencode($PRODUCTNAME) .'"><img src="'. $IMAGEURLSMALL .'" alt="'. $PRODUCTNAME .'" border="0" /></a>';
}

$iOurPrice = (int) str_replace('$', '', $OURPRICE);
$iListPrice = (int) str_replace('$', '', $LISTPRICE);
if ($iOurPrice && $iListPrice) {
	$iDiscount = ($iListPrice - $iOurPrice) / $iListPrice;
	$iDiscount = (int) ($iDiscount*100);
	if ($iDiscount > 0) {
		$sDiscount = ', this means <font color="red">' . $iDiscount .'%</font> off!';
	}
}

if ($AUTHORS) {
	$sAuthors = ' by ' . $AUTHORS;
}

$OURPRICE = '$' . number_format((float) str_replace('$', '', $OURPRICE), 2);
$LISTPRICE = '$' . number_format((float) str_replace('$', '', $LISTPRICE), 2);
$USEDPRICE = '$' . number_format((float) str_replace('$', '', $USEDPRICE), 2);

echo
	'<table border="0" cellpadding="2" cellspacing="0" width="'.AWS_TABLE_WIDTH.'">',
	'<tr><td bgcolor="'.$sTdColor.'" width="85%">',
	'<a href="http://shop.php9.com/books.php/Mode/product/AsinSearch/'. $ASIN .'/name/'. rawurlencode($PRODUCTNAME) .'"><b>'. $PRODUCTNAME .'</b></a>',
	'<font size="-2">', $sAuthors,
	'<br />List Price: <b><font color="red">'. $LISTPRICE .'</font></b>',
	'&nbsp; &nbsp; Amazon Price: <b><font color="red">'. $OURPRICE .'</font></b>', $sDiscount,
	'&nbsp; &nbsp; Used Price: <b><font color="red">'. $USEDPRICE .'</font></b>',
	'&nbsp; &nbsp; Publisher: ', 	$MANUFACTURER, ' ('. $RELEASEDATE .')',
	'<br />Buy from <a href="'. $sProductUrl .'"><b><font color="red">Amazon.com!</font></b></a>',
	'&nbsp;<a href="http://shop.php9.com/books.php/Mode/product/AsinSearch/'. $ASIN .'/name/'. rawurlencode($PRODUCTNAME) .'"><b><font color="red">our Top Books shop!</font></b></a>',
	'&nbsp;<a href="http://shop.php9.com/books.php/Mode/product/AsinSearch/'. $ASIN .'/name/'. rawurlencode($PRODUCTNAME) .'"><b><font color="red">our Tech Books shop!</font></b></a>',
	'</td><td bgcolor="'.$sTdColor.'" width="15%">',
	$sImageUrl,
	'</td></tr>',
	'</table>'
;

?>
<!--
Script generated with the Amazon PHP API from php9.com
Try it here: http://www.php9.com/amazon.php
//-->