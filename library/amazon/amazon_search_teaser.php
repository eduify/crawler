<?php
/*

	filename:			amazon_search_teaser.php
	created:			7/17/2002, © 2002 php9.com Calin Uioreanu
	descripton:		controller script Amazon API search result add
	requirements:	
		- PHP with XML support and able to open remote files
		- a Developer's token from Amazon (http://www.amazon.com/webservices)

	optional:
		- an AssociateId from Amazon (http://associates.amazon.com/)

*/

// configuration variables 
require_once('amazon_search_config.php');

// webservice class definition 
require_once('amazon_class.php');

?>
<style type="text/css">
<!-- 
td { font-family: verdana,arial,helvetica,sans-serif; font-size: smaller; }
-->
</style>
<?php

$oAmazon = new Amazon_WebService();

if (!$oAmazon->setInputUrl($sUrl, AWS_XMLFEED_TIMEOUT)) {
	return; // silent 
}

// pass the output display template
$oAmazon->sTemplate = 'amazon_search_layout.php';

if (!$oAmazon->parse()) {
	return; // silent 
}

?>