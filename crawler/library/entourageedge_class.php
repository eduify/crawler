<?php

include_once("simple_html_dom.php");
ini_set('user_agent', 'Mozilla/4.0 (compatible; MSIE 6.0)');

class entourageedge {

    private function getAmazonData($SearchPhrase, $RequestType) {
        include_once("amazon/aws_signed_request.php");

        $public_key = "AKIAIRPU52XIPOIZS5OA";
        $private_key = "MQUKYscxHYenmyPApVY9NmCi/9+KDC2FxiBeZmgn";

        if ($RequestType == "ItemLookup") {
            $pxml = aws_signed_request("com", array("Operation" => "ItemLookup", "SearchIndex" => "Books", "ItemId" => "$SearchPhrase", "IdType" => "ISBN", "ResponseGroup" => "Large"), $public_key, $private_key);
            if ($pxml === False) {
                return false;
// Problem in accessing AMAZON API
            } else {

                if ($pxml->Items->Item->ASIN == "") {

                    return false;
                } else {
                    $Amazon['AmazonListPrice'] = $pxml->Items->Item->ItemAttributes->ListPrice->FormattedPrice;
                    $Amazon['NonAmazonNewPrice'] = $pxml->Items->Item->OfferSummary->LowestNewPrice->FormattedPrice;
                    $Amazon['NonAmazonUsedPrice'] = $pxml->Items->Item->OfferSummary->LowestUsedPrice->FormattedPrice;
                    $Amazon['AmazonDiscountPrice'] = $pxml->Items->Item->Offers->Offer->OfferListing->Price->FormattedPrice;
                    $Amazon['AmazonDetailPageURL'] = $pxml->Items->Item->DetailPageURL;
                    return $Amazon;
                } // Else
            }// Else
        } else if ($RequestType == "ItemSearch") {
            $pxml = aws_signed_request("com", array("Operation" => "ItemSearch", "SearchIndex" => "Books", "Keywords" => "$SearchPhrase", "ResponseGroup" => "Large"), $public_key, $private_key);

            if ($pxml === False) {
                return false;
// Problem in accessing AMAZON API
            } else {

                if ($pxml->Items->Item->ItemAttributes->ListPrice->FormattedPrice == "") {

                    return false;
                } else {
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

    private function getTotalPages(&$html) {
        $totalPages = $html->find('table[class=pager] td', 0);
        $totalPages = $totalPages->innertext;
        $totalPages = str_replace("Items 1 to 10 of", "", $totalPages);
        $totalPages = str_replace("total", "", $totalPages);
        $totalPages = trim($totalPages) / 100;
        return (round($totalPages, 0) == $totalPages) ? $totalPages : (round($totalPages, 0) + 0);
    }

//--------------------------------------------------------------------------------------------------------
    private function MainBookData(&$output) {
        include_once("simple_html_dom.php");

        $url = "http://www.entourageedge.com/e-textbooks.html";
        $html = file_get_dom($url);

// After cleaning Data Get the Total Pages
        $totalPages = $this->getTotalPages($html);
         $html->__destruct();

        //$start = 131;
       // $totalPages = 147;


       
//-------------------------------------
        for ($k = 1; $k <= $totalPages; $k++) {

            $url = "http://www.entourageedge.com/e-textbooks.html?limit=100&p=$k";
            $html = file_get_dom($url);

// Start looping to get the Data On the Page
            $bookDataRows = $html->find('div[class=listing-item]');

            for ($i = 0; $i < count($bookDataRows); $i++) {

                unset($title, $author, $publisher, $publishingData, $ISBN_13, $youPayprice, $listPrice, $descriptionPageURL);
                unset($AmazonListPrice, $AmazonDiscountPrice, $AmazonDetailPageURL);

                if ($bookDataRows[$i]->find('div[class=product-shop] h5', 0) <> "") {
                    $title = $bookDataRows[$i]->find('div[class=product-shop] h5 a', 0)->innertext;
                    $descriptionPageURL = $bookDataRows[$i]->find('div[class=product-shop] h5 a', 0)->getAttribute("href");

                    $author = $bookDataRows[$i]->find('div[class=listing-author] a', 0)->innertext;
                    $author = utf8_decode($author);

                    $publisher = $bookDataRows[$i]->find('div[class=bullets] li', 0)->innertext;
                    $publisher = split("Publisher : ", $publisher);
                    $publisher = $publisher[1];

                    $publishingData = $bookDataRows[$i]->find('div[class=bullets] li', 1)->plaintext;
                    $publishingData = split("Pub. Date : ", $publishingData);
                    $publishingData = $publishingData[1];

                    $ISBN_13 = $bookDataRows[$i]->find('div[class=bullets] li', 2)->plaintext;
                    $ISBN_13 = str_replace("ISBN-13 : ", "", $ISBN_13);

// ---- Amazon Data Fetching ---------
//$amazonISBN = $bookDataRows[$i]->find('div[class=search_booktitle] a',0)->getAttribute("href");
//$amazonISBN = str_replace("/","" , $amazonISBN);

                    $amazonISBN = $ISBN_13;

                    $amazon = $this->getAmazonData("$amazonISBN", "ItemLookup");
                    if ($amazon) {
                        $AmazonListPrice = $amazon['AmazonListPrice'];
                        $AmazonDiscountPrice = $amazon['AmazonDiscountPrice'];
                        $AmazonDetailPageURL = $amazon['AmazonDetailPageURL'];
                    }

// ---- Amazon Data Fetching Ends---------


                    $listPrice = $bookDataRows[$i]->find('p[class=old-price] span', 1)->plaintext;
                    $listPrice = str_replace(" ", "", $listPrice);

                    $youPayPrice = $bookDataRows[$i]->find('p[class=special-price] span', 1)->plaintext;
                    $youPayPrice = str_replace(" ", "", $youPayPrice);


                    $rowData = "\"$title\",\"$author\",\"$publisher\",\"$publishingData\",\"$ISBN_13\",\"$listPrice\",\"$youPayPrice\",\"$descriptionPageURL\",\"$AmazonListPrice\",\"$AmazonDiscountPrice\",\"$AmazonDetailPageURL\"\n";
                    echo $rowData . "\n\n";

                    echo "\n\n";
                    echo "Memory Usage in Loop [$start - $totalPages] = " . memory_get_usage() / (1024 * 1024) . "MB  \n\n\n";
                    echo "\n\n";
                    
                    fwrite($output, $rowData);
                } // End of IF
            } // End of FOR


            $html->__destruct();

            echo "\n\n";
            echo "Memory Usage  = " . memory_get_usage() / (1024 * 1024) . "MB  \n\n\n";
        }




        unset($html);
        unset($ul);
    }

//--------------------------------------------------------------------------------------------------------
    private static function getOptions() {
//echo "\n\nEnter 1: Print List of State \n";
        echo "\n\nEnter 2: Process CSV File: \n";
        echo "Enter 3: Exit: \n";
        $option = fgets(STDIN);
        return $option;
    }

//------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------

    private function get_file_extension($file_name) {
        return substr(strrchr($file_name, '.'), 1);
    }

//------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------
    private function checkFile($file_name) {

//if(file_exists($file_name)) {  	//  Check whether FIle Exists or Not
        if ($this->get_file_extension($file_name) <> "csv") {
            echo "\n\n\n\n\n------------------------------------------------\n";
            echo "------------------------------------------------\n";
            echo "------------------------------------------------\n";
            echo "\nPlease Enter CSV file\n ";
            echo "------------------------------------------------\n";
            echo "------------------------------------------------\n";
            echo "------------------------------------------------\n";
        } else {
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
    public function runScrapper() {
        if (PHP_OS == "WINNT") {
            $file_name = "c:\\entourageedge-1.csv";
        } else {
            $file_name = "\\entourageedge-1.csv";
        }

        $output = fopen($file_name, 'w');
        $row_data = "Title,Author,Publisher,Publishing Date,ISBN 13,List Price,You Pay Price,Description Page URL,Amazon List Price,Amazon Price,Amazon Description Page URL\n";

        fwrite($output, $row_data);
        $this->MainBookData($output);



        fclose($output);
    }

//------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------
}

// ----------- Varibales Initilization --------------
//$condition = true;
//$option = "";
//$file_name = "";
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
?>
