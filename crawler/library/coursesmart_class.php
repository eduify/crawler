<?php

include_once("simple_html_dom.php");

class coursemart {

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
        $totalPages = $html->find('td[class=pagination]', 1);
        $totalPages = $totalPages->outertext;
        $totalPages = split("\|", $totalPages);
        $totalPages = $totalPages[0];
        $totalPages = split("of", $totalPages);
        $totalPages = $totalPages[1];
        $totalPages = split(" ", $totalPages);
        $totalPages = $totalPages[1];
        $totalPages = $totalPages / 10;
        return (round($totalPages, 0) == $totalPages) ? $totalPages : (round($totalPages, 0) + 1);
    }

//--------------------------------------------------------------------------------------------------------
    private function MainBookData(&$output) {
        include_once("simple_html_dom.php");

        $url = "http://www.coursesmart.com/_ajax_searchresultsajax_1_390380?__sugus=191036118&action=2&__version=1.1.1&searchmode=&__className=search&view=book&xmlid=&page=0";
        $html = file_get_dom($url);
        $html = split("F9.Gk.Hu", $html);
        $html = $html[1];
        $html = str_get_html($html);

// After cleaning Data Get the Total Pages
        $totalPages = $this->getTotalPages($html);
        $html->__destruct();
//-------------------------------------
        for ($k = 0; $k < $totalPages; $k++) {

            $url = "http://www.coursesmart.com/_ajax_searchresultsajax_1_390380?__sugus=191036118&action=2&__version=1.1.1&searchmode=&__className=search&view=book&xmlid=&page=$k";
            $html = file_get_dom($url);
            $html = split("F9.Gk.Hu", $html);
            $html = $html[1];
            $html = str_get_html($html);
// Start looping to get the Data On the Page
            $bookDataRows = $html->find('div[id=tabcontents_sr]', 0)->find('div[class=page_discipline_search]', 0)->children();
            $bookDataRows = $bookDataRows[2]->find('div[class=page_discipline_search]', 0)->first_child()->children();
            $bookDataRows = $bookDataRows[0]->children();

//echo count($bookDataRows[]);
            for ($i = 0; $i < count($bookDataRows); $i++) {

                unset($title, $author, $publisher, $copywriteYear, $publishingData, $ISBN_obj, $ebookISBN_10, $ebookISBN_13, $printISBN_10, $printISBN_13);
                unset($numberOfPages, $price, $courses, $descriptionPageURL, $AmazonListPrice, $AmazonDiscountPrice, $AmazonDetailPageURL, $subscription, $subscription_180, $subscription_360, $subscription_540);




                if ($bookDataRows[$i]->find('div[class=search_booktitle]', 0) <> "") {
                    $title = $bookDataRows[$i]->find('div[class=search_booktitle] a', 0)->innertext;
                    $descriptionPageURL = "http://www.coursesmart.com" . $bookDataRows[$i]->find('div[class=search_booktitle] a', 0)->getAttribute("href");


                    $author = $bookDataRows[$i]->find('div[class=info] div', 0)->innertext;
                    $author = split("</span>", $author);
                    $author = utf8_decode($author[1]);

                    $publisher = $bookDataRows[$i]->find('div[class=info] div', 1)->innertext;
                    $publisher = split("Publisher: ", $publisher);
                    $publisher = $publisher[1];

                    $copywriteYear = $bookDataRows[$i]->find('div[class=info] div', 2)->plaintext;
                    $copywriteYear = split("Copyright Year: ", $copywriteYear);
                    $copywriteYear = $copywriteYear[1];

                    $publishingData = $bookDataRows[$i]->find('div[class=info] div', 3)->plaintext;
                    $publishingData = split("Publishing Date: ", $publishingData);
                    $publishingData = $publishingData[1];


                    $ISBN_obj = ($bookDataRows[$i]->find('table[class=info] div'));
                    for ($j = 0; $j < count($ISBN_obj); $j++) {
                        if ($ISBN_obj[$j]->plaintext <> "") {

                            if ($ISBN_obj[$j]->find('span', 0)->plaintext == "eText ISBN-10: ") {
                                $ebookISBN_10 = utf8_decode($ISBN_obj[$j]->find('span', 1)->plaintext);
                            }
                            if ($ISBN_obj[$j]->find('span', 0)->plaintext == "eText ISBN-13: ") {
                                $ebookISBN_13 = utf8_decode($ISBN_obj[$j]->find('span', 1)->plaintext);
                            }
                            if ($ISBN_obj[$j]->find('span', 0)->plaintext == "Print ISBN-10: ") {
                                $printISBN_10 = utf8_decode($ISBN_obj[$j]->find('span', 1)->plaintext);
                            }
                            if ($ISBN_obj[$j]->find('span', 0)->plaintext == "Print ISBN-13: ") {
                                $printISBN_13 = utf8_decode($ISBN_obj[$j]->find('span', 1)->plaintext);
                            }
                        } // this Will Block the Error IF Particular type (isbn 10, 13) Not available
                    } // Loop to Check number All ISB 10, 13 , print, digital
// ---- Amazon Data Fetching ---------
//$amazonISBN = $bookDataRows[$i]->find('div[class=search_booktitle] a',0)->getAttribute("href");
//$amazonISBN = str_replace("/","" , $amazonISBN);

                    $amazonISBN = str_replace("-", "", $printISBN_13);
                    $amazonISBN = substr($amazonISBN, 0, 13);

                    $amazon = $this->getAmazonData("$amazonISBN", "ItemLookup");
                    if ($amazon) {
                        $AmazonListPrice = $amazon['AmazonListPrice'];
                        $AmazonDiscountPrice = $amazon['AmazonDiscountPrice'];
                        $AmazonDetailPageURL = $amazon['AmazonDetailPageURL'];
                    }

// ---- Amazon Data Fetching Ends---------
                    $numberOfPages = $bookDataRows[$i]->find('div[class=info] div', 0)->parentNode()->last_child();
                    $numberOfPages = split("Pages: ", $numberOfPages);
                    $numberOfPages = strip_tags($numberOfPages[1]);

                    $courses = $bookDataRows[$i]->find('div[class=info] div', 0)->parentNode()->last_child()->prev_sibling()->plaintext;
                    $courses = str_replace("Course:\n", "", $courses);
                    $courses = utf8_decode($courses);

                    $price = $bookDataRows[$i]->find('td[class=formbox] div div', 0)->find('div[class=oec]', 0)->plaintext;
                    $price = str_replace("eTextbook ", "", $price);

                    $subscription = $bookDataRows[$i]->find('td[class=formbox] div div', 0)->find('div[class=sub]', 0)->plaintext;
                    $subscription = str_replace(" day subscription)", "", $subscription);
                    $subscription = str_replace("(", "", $subscription);
// Row Data
                    if ($subscription == "180") {
                        $subscription_180 = "180";
                    } else if ($subscription == "360") {
                        $subscription_360 = "360";
                    } else if ($subscription == "540") {
                        $subscription_540 = "540";
                    }
                    $rowData = "\"$title\",\"$author\",\"$publisher\",\"$copywriteYear\",\"$publishingData\",\"$ebookISBN_10\",\"$ebookISBN_13\",\"$printISBN_10\",\"$printISBN_13\",\"$numberOfPages\",\"$price\",\"$courses\",\"$descriptionPageURL\",\"$AmazonListPrice\",\"$AmazonDiscountPrice\",\"$AmazonDetailPageURL\",\"$subscription_180\",\"$subscription_360\",\"$subscription_540\"\n";
                    echo $rowData . "\n\n";
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
            $file_name = "c:\\coursesmart.csv";
        } else {
            $file_name = "\\coursesmart.csv";
        }

        $output = fopen($file_name, 'w');
        $row_data = "Title,Author,Publisher,Copyright Year,Publishing Date,Digital ISBN 10,Digital ISBN 13,Print ISBN 10,Print ISBN 13,Pages,Price,Courses,Description Page URL,Amazon List Price,Amazon Price,Amazon Description Page URL,180 Day Subscription,360 Day Subscription,540 Day Subscription\n";

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
