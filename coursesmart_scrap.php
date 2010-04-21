<?php
include_once("library/simple_html_dom.php");

function getAmazonData($SearchPhrase,$RequestType) {
    include_once("library/amazon/aws_signed_request.php");

    $public_key = "AKIAIRPU52XIPOIZS5OA";
    $private_key = "MQUKYscxHYenmyPApVY9NmCi/9+KDC2FxiBeZmgn";

    if($RequestType=="ItemLookup") {
        $pxml = aws_signed_request("com", array("Operation"=>"ItemLookup","SearchIndex"=>"Books", "ItemId"=>"$SearchPhrase","IdType"=>"ISBN","ResponseGroup"=>"Large"), $public_key, $private_key);
        if ($pxml === False) {
            return false;
            // Problem in accessing AMAZON API

        }else {

            if($pxml->Items->Item->ASIN == "") {

                return false;

            }else {
                $Amazon['AmazonListPrice'] = $pxml->Items->Item->ItemAttributes->ListPrice->FormattedPrice;
                $Amazon['NonAmazonNewPrice'] = $pxml->Items->Item->OfferSummary->LowestNewPrice->FormattedPrice;
                $Amazon['NonAmazonUsedPrice'] = $pxml->Items->Item->OfferSummary->LowestUsedPrice->FormattedPrice;
                $Amazon['AmazonDiscountPrice'] = $pxml->Items->Item->Offers->Offer->OfferListing->Price->FormattedPrice;
                $Amazon['AmazonDetailPageURL'] = $pxml->Items->Item->DetailPageURL;
                return $Amazon;
            } // Else
        }// Else

    }else if($RequestType=="ItemSearch") {
        $pxml = aws_signed_request("com", array("Operation"=>"ItemSearch","SearchIndex"=>"Books","Keywords"=>"$SearchPhrase","ResponseGroup"=>"Large"), $public_key, $private_key);

        if ($pxml === False) {
            return false;
            // Problem in accessing AMAZON API

        }else {

            if($pxml->Items->Item->ItemAttributes->ListPrice->FormattedPrice == "") {

                return false;

            }else {
                $Amazon['AmazonListPrice'] = $pxml->Items->Item->ItemAttributes->ListPrice->FormattedPrice;
                $Amazon['NonAmazonNewPrice'] = $pxml->Items->Item->OfferSummary->LowestNewPrice->FormattedPrice;
                $Amazon['NonAmazonUsedPrice'] = $pxml->Items->Item->OfferSummary->LowestUsedPrice->FormattedPrice;
                $Amazon['AmazonDiscountPrice'] = $pxml->Items->Item->Offers->Offer->OfferListing->Price->FormattedPrice;
                $Amazon['AmazonDetailPageURL'] = $pxml->Items->Item->DetailPageURL;
                return $Amazon;
            } // Else
        }// Else
    }





}
//--------------------------------------------------------------------------------------------------------

function getTotalPages(&$html) {
    $totalPages  = $html->find('td[class=pagination]',1);
    $totalPages = $totalPages->outertext;
    $totalPages = split("\|", $totalPages);
    $totalPages = $totalPages[0];
    $totalPages = split("of", $totalPages);
    $totalPages = $totalPages[1];
    $totalPages = split(" ", $totalPages);
    $totalPages = $totalPages[1];
    $totalPages =  $totalPages/10 ;
    return (round($totalPages,0) == $totalPages)?$totalPages:(round($totalPages,0)+1);

}
//--------------------------------------------------------------------------------------------------------
function MainBookData(&$output) {
    include_once("library/simple_html_dom.php");

    $url = "http://www.coursesmart.com/_ajax_searchresultsajax_1_390380?__sugus=191036118&action=2&__version=1.1.1&searchmode=&__className=search&view=book&xmlid=&page=1";
    $html = file_get_dom($url);
    $html = split("F9.Gk.Hu", $html);
    $html = $html[1];
    $html = str_get_html($html);

    // After cleaning Data Get the Total Pages
    $totalPages = getTotalPages($html);
    //-------------------------------------
    // Start looping to get the Data On the Page
    $bookDataRows  = $html->find('div[id=tabcontents_sr]',0)->find('div[class=page_discipline_search]',0)->children();
    $bookDataRows  = $bookDataRows[2]->find('div[class=page_discipline_search]',0)->first_child()->children();
    $bookDataRows  = $bookDataRows[0]->children();

    //echo count($bookDataRows[]);
    for($i=0; $i< count($bookDataRows);$i++) {
        if($bookDataRows[$i]->find('div[class=search_booktitle]',0) <> "") {
            $title  = $bookDataRows[$i]->find('div[class=search_booktitle] a',0)->innertext;
            $author = $bookDataRows[$i]->find('div[class=info] div',0)->innertext;
            $author = split("</span>", $author);
            $author = utf8_decode($author[1]);

            $publisher = $bookDataRows[$i]->find('div[class=info] div',1)->innertext;
            $publisher = split("Publisher: ", $publisher);
            $publisher = $publisher[1];

            $copywriteYear = $bookDataRows[$i]->find('div[class=info] div',2)->plaintext;
            $copywriteYear = split("Copyright Year: ", $copywriteYear);
            $copywriteYear = $copywriteYear[1];

            $publishingData = $bookDataRows[$i]->find('div[class=info] div',3)->plaintext;
            $publishingData = split("Publishing Date: ", $publishingData);
            $publishingData = $publishingData[1];

            // If to check weather which ISBN is available
            if(count($bookDataRows[$i]->find('table[class=info] div'))== 4){
                
            }
            $ebookISBN_10 = utf8_decode($bookDataRows[$i]->find('table[class=info] div',0)->plaintext);
            echo ($ebookISBN_10)."<br />";

//            $printISBN_10 =
//            $ebookISBN_10 =
//
//            $printISBN_13 =
//            $ebookISBN_13 =
//            $numberOfPages =
//            $price =
//            $subscription =

        } // End of IF
    } // End of FOR

    $html->__destruct();



    unset($html);
    unset($ul);


}

//--------------------------------------------------------------------------------------------------------
function getOptions() {
    //echo "\n\nEnter 1: Print List of State \n";
    echo "\n\nEnter 2: Process CSV File: \n";
    echo "Enter 3: Exit: \n";
    $option = fgets(STDIN);
    return $option;
}
//------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------

function get_file_extension($file_name) {
    return substr(strrchr($file_name,'.'),1);
}
//------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------
function checkFile($file_name) {

    //if(file_exists($file_name)) {  	//  Check whether FIle Exists or Not
    if(get_file_extension($file_name) <> "csv") {
        echo "\n\n\n\n\n------------------------------------------------\n";
        echo "------------------------------------------------\n";
        echo "------------------------------------------------\n";
        echo "\nPlease Enter CSV file\n ";
        echo "------------------------------------------------\n";
        echo "------------------------------------------------\n";
        echo "------------------------------------------------\n";
    }else {
        echo "\n\n\n\n\n------------------------------------------------\n";
        echo "------------------------------------------------\n";
        echo "------------------------------------------------\n";
        echo "\nCSV File Exist, You can now Process File\n ";
        echo "------------------------------------------------\n";
        echo "------------------------------------------------\n";
        echo "------------------------------------------------\n";
    }
    return true;
//	}else{
//			var_dump($file_name);
//			echo "\n\n\n\n\n------------------------------------------------\n";
//			echo "------------------------------------------------\n";
//			echo "------------------------------------------------\n";
//			echo "File Does Not Exist, Please check Path or File Name \n";
//			echo "------------------------------------------------\n";
//			echo "------------------------------------------------\n";
//			echo "------------------------------------------------\n";
//			return false;
//	}
}
//------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------
function ProcessDataDigging_Generic() {
    if(PHP_OS == "WINNT") {
        $file_name = "c:\\coursesmart.csv";
    }else {
        $file_name = "\\coursesmart.csv";
    }

    $output = fopen($file_name, 'w');
    $row_data = "Program,Term,Department,Course,Section,Course URL,Book Title,BK Author,BK Edition,BK Image URL,BK Used Price,BK New Price,BK Digital Price,BK ISBN,Amazon List Price,Amazon Discount Price,Non Amazon New Price,Non Amazon Used Price,Amazon Detail Page URL,Detailed Link,Author(s),Edition,Publisher,ISBN (10),ISBN (13),ISBN (10) - Digi,ISBN (13) - Digi,List Price,You Pay Price\n";

    MainBookData($output);
    //fwrite($output, $row_data);


    //echo "\n";
    //echo "Memory Usage  = ".memory_get_usage()/(1024*1024) . "MB  \n\n\n";

    fclose($output);
}
//------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------




// ----------- Varibales Initilization --------------
$condition = true;
$option = "";
$file_name = "";



// --------------------------------------------------
//while($condition) {
//    switch($option) {
//        case "":
//            $option = getOptions();           // General Options
//            break;
//        case 1:
//
//            $option = getOptions();
//            break;
//        case 2:
//            if($Store<> "") {
//                echo "Processsing File here \n\n\n";
//                //------------------- Start Processing File
//
//               // ProcessDataDigging_Generic($Store,$University[1],$Campus[1]);
//            }
//            $option = getOptions(); 		// General Options
//            break;
//        case 3:
//
//
//            exit;
//            break;
//        default:
//            $option = getOptions();			// General Options
//            break;
//    }
//
//}

//ProcessDataDigging();
ProcessDataDigging_Generic();

?>
