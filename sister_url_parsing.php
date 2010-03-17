<?php
function SisterSiteData($sister_url){
    include_once("library/simple_html_dom.php");
    $url = "http://www.cafescribe.com/index.php?option=com_virtuemart&page=shop.product_details&flypage=shop.flypage&isbn13=9780073527093&storeid=670&vmcchk=1";
    $html = file_get_dom($url);
    $ListPrice  = $html->find('div[id=bodycenter] table td',0)->children[2]->children[1]->plaintext;
    $ListPrice = trim($ListPrice);
    
    $YouPayPrice  = $html->find('div[id=bodycenter] table td',0)->children[2]->children[6]->plaintext;
    $YouPayPrice = trim($YouPayPrice);

    $Author  = $html->find('div[id=bodycenter] table td',0)->children[9]->find('tr',0)->children[1]->plaintext;
    $Author = trim($Author);

    $Edition = $html->find('div[id=bodycenter] table td',0)->children[9]->find('tr',4)->children[1]->plaintext;
    $Edition = trim($Edition);

    $Publisher = $html->find('div[id=bodycenter] table td',0)->children[9]->find('tr',3)->children[1]->plaintext;
    $Publisher = trim($Publisher);

    $ISBN_10_Print = $html->find('div[id=bodycenter] table td',0)->children[9]->find('tr',6)->children[1]->plaintext;
    $ISBN_10_Print = trim($ISBN_10_Print);

    $ISBN_13_Print = $html->find('div[id=bodycenter] table td',0)->children[9]->find('tr',7)->children[1]->plaintext;
    $ISBN_13_Print = trim($ISBN_13_Print);

    $ISBN_10_Digital = $html->find('div[id=bodycenter] table td',0)->children[9]->find('tr',8)->children[1]->plaintext;
    $ISBN_10_Digital = trim($ISBN_10_Digital);

    $ISBN_13_Digital = $html->find('div[id=bodycenter] table td',0)->children[9]->find('tr',9)->children[1]->plaintext;
    $ISBN_13_Digital = trim($ISBN_13_Digital);

    return "$Author,$Edition,$Publisher,$ISBN_10_Print,$ISBN_13_Print,$ISBN_10_Digital,$ISBN_13_Digital,$ListPrice,$YouPayPrice";
}
echo SisterSiteData("as");
?>
