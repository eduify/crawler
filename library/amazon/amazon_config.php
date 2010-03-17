<?php
/*

    filename:            amazon_config.php
    created:            7/17/2002, © 2002 php9.com Calin Uioreanu
    descripton:        configuration variables for Amazon Parser

*/

//////////////////////
// Modifiable section

// if you have a valid Amazon associate Id, place it here to be rewarded for the traffic you generate to Amazon
define('ASSOCIATE_ID', 'php9comweblot-20');

// Amazon specific constants
define('IMAGEURLMEDIUM_HEIGHT',    140);
define('IMAGEURLMEDIUM_WIDTH',        107);

//////////////////////
// Read only section

// Do not change this constant
define('DEVELOPER_TOKEN','D37FFQXOC3MRYZ');

//  XSL live transform Amazon data to HTML
define('PRODUCT_DETAIL_URL','http://xml.amazon.com/onca/xml3?t='.ASSOCIATE_ID.'&dev-t='.DEVELOPER_TOKEN.'&type=heavy&f=http://www.php9.com/php9-data-to-htmls.xsl&AsinSearch=');

if (!$sCurrentMode = $_GET['Mode']) {
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


// sort by salesRank by default
if (!$sCurrentModeSortType = $_GET['SortBy']) {
    $sCurrentModeSortType = '+salesrank';
}

// Sort Types
$arModeSortType = array (
    'baby' => array(
        '+pmrank' => 'Featured Items',
        '+salesrank' => 'Bestselling',
        '+titlerank' => 'Alphabetical (A-Z)',
    ),
    'books' => array(
        '+pmrank' => 'Featured Items',
        '+salesrank' => 'Bestselling',
        '+reviewrank' => 'Average Customer Review',
        '+pricerank' => 'Price (Low to High)',
        '+inverse-pricerank' => 'Price (High to Low)',
        '+daterank' => 'Publication Date',
        '+titlerank' => 'Alphabetical (A-Z)',
        '-titlerank' => 'Alphabetical (Z-A)',
    ),
    'classical' => array(
        '+pmrank' => 'Featured Items',
        '+salesrank' => 'Bestselling',
        '+titlerank' => 'Alphabetical (A-Z)',
    ),
    'dvd' => array(
        '+salesrank' => 'Bestselling',
        '+titlerank' => 'Alphabetical',
    ),
    'electronics' => array(
        '+pmrank' => 'Featured Items',
        '+salesrank' => 'Bestselling',
        '+titlerank' => 'Alphabetical',
        '+reviewrank' => 'Review',
    ),
    'garden' => array(
        '+psrank' => 'Featured Items',
        '+salesrank' => 'Bestselling',
        '+titlerank' => 'Alphabetical (A-Z)',
        '-titlerank' => 'Alphabetical (Z-A)',
        '+manufactrank' => 'Manufacturer (A-Z)',
        '-manufactrank' => 'Manufacturer (Z-A)',
        '+price' => 'Price (Low to High)',
        '-price' => 'Price (High to Low)',
    ),
    'kitchen' => array(
        '+psrank' => 'Featured Items',
        '+salesrank' => 'Bestselling',
        '+titlerank' => 'Alphabetical (A-Z)',
        '-titlerank' => 'Alphabetical (Z-A)',
        '+manufactrank' => 'Manufacturer (A-Z)',
        '-manufactrank' => 'Manufacturer (Z-A)',
        '+price' => 'Price (Low to High)',
        '-price' => 'Price (High to Low)',
    ),
    'magazines' => array(
        '+pmrank' => 'Featured Items',
        '+salesrank' => 'Bestselling',
        '+titlerank' => 'Alphabetical (A-Z)',
    ),
    'music' => array(
        '+psrank' => 'Featured Items',
        '+salesrank' => 'Bestselling',
        '+artistrank' => 'Artist Name',
        '+orig-rel-date' => 'Original Release Date',
        '+titlerank' => 'Alphabetical',
    ),
    'pc-hardware' => array(
        '+psrank' => 'Featured Items',
        '+salesrank' => 'Bestselling',
        '+titlerank' => 'Alphabetical (A-Z)',
        '-titlerank' => 'Alphabetical (Z-A)',
    ),
    'photo' => array(
        '+pmrank' => 'Featured Items',
        '+salesrank' => 'Bestselling',
        '+titlerank' => 'Alphabetical (A-Z)',
        '-titlerank' => 'Alphabetical (Z-A)',
    ),
    'software' => array(
        '+pmrank' => 'Featured Items',
        '+salesrank' => 'Bestselling',
        '+titlerank' => 'Alphabetical',
        '+price' => 'Price (Low to High)',
        '+price' => 'Price (High to Low)',
    ),
    'toys' => array(
        '+pmrank' => 'Featured Items',
        '+salesrank' => 'Bestselling',
        '+titlerank' => 'Alphabetical (A-Z)',
    ),
    'universal' => array(
        '+psrank' => 'Featured Items',
        '+salesrank' => 'Bestselling',
        '+titlerank' => 'Alphabetical (A-Z)',
        '-titlerank' => 'Alphabetical (Z-A)',
        '+manufactrank' => 'Manufacturer (A-Z)',
        '-manufactrank' => 'Manufacturer (Z-A)',
        '+price' => 'Price (Low to High)',
        '-price' => 'Price (High to Low)',
    ),
    'vhs' => array(
        '+psrank' => 'Featured Items',
        '+salesrank' => 'Bestselling',
        '+titlerank' => 'Alphabetical',
    ),
    'videogames' => array(
        '+pmrank' => 'Featured Items',
        '+salesrank' => 'Bestselling',
        '+titlerank' => 'Alphabetical',
        '+price' => 'Price (Low to High)',
        '-price' => 'Price (High to Low)',
    ),
);


$sUrl  = 'http://xml.amazon.com/onca/xml3';
$sUrl .= '?t='. ASSOCIATE_ID;
$sUrl .= '&dev-t='. DEVELOPER_TOKEN;
$sUrl .= '&mode=' . $sCurrentMode;
$sUrl .= '&type=lite&page=1';
$sUrl .= '&f=xml';
$sUrl .= '&KeywordSearch=';

// search for PHP Books by default
if (@$_GET['Search']) {
    error_log ("\n " . $_GET['Search'] ." from $sCurrentMode at ". date("F j, Y, g:i a"), 3, "amazon_search.log");
    $sUrl .= urlencode ($_GET['Search']);    
} else {
    $sUrl .= 'php';    
}

$sUrl .= '&sort='. $sCurrentModeSortType;    

?>
<!--
Script generated with the Amazon PHP API from php9.com
Try it here: http://www.php9.com/amazon.php
//--> 