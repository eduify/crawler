<?php
include_once("library/simple_html_dom.php");

Function getAmazonData($SearchPhrase,$RequestType) {
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

function MainBookData($url,$initial_csv_row_data,&$output) {
    include_once("library/simple_html_dom.php");
    $Main_Data = "";
    $html = file_get_dom($url);

    $ul  = $html->find('div[id=material_results] ul');

    // Header for csv


    // CHeck whether Material Exists
    if($ul != null) {
        $total_type_books = count($ul);               // Counting type of books
        for($j=0;$j < $total_type_books; $j++) {

            $all_li = $ul[$j]->find('li');
            $total_books =  count($all_li); //This will give us Amount of books

            for($i=0;$i<$total_books; $i++) {
                $BookTitle = $all_li[$i]->find('span[class=wrap]', 0)->plaintext ;
                $BookTitle = htmlspecialchars_decode($BookTitle);

                $ImageUrl = $all_li[$i]->find('img', 0)->getAttribute("src");



                if($all_li[$i]->find('div[class=field]', 1)->plaintext != "") {
                    $BK_UsedPrice = $all_li[$i]->find('div[class=field]', 1)->find('span[class=emph]', 0)->plaintext;
                }

                if($all_li[$i]->find('div[class=field]', 2)->plaintext != "") {
                    $BK_NewPrice = $all_li[$i]->find('div[class=field]', 2)->find('span[class=emph]', 0)->plaintext;
                }

                if($all_li[$i]->find('div[id=field]', 0)->plaintext != "") {
                    $BK_DigitalPrice = $all_li[$i]->find('div[id=field]', 0)->find('span[class=emph]', 0)->plaintext;
                }


                $AuthorEdition = $all_li[$i]->find('div[class=detail]', 0)->plaintext ;
                $AuthorEdition = split("Edition", $AuthorEdition);

                // Data Cleaning for Author and Edition
                $Author = $AuthorEdition[0];
                $Edition = $AuthorEdition[1];
                $Author = str_replace("Author:", "", $Author);
                $Edition = str_replace(":", "", $Edition);

                $Author = str_replace("\n", "", $Author);
                $Edition = str_replace("\n", "", $Edition);

                $Author = ltrim($Author);
                $Edition = ltrim($Edition);

                $Author = rtrim($Author);
                $Author = htmlspecialchars_decode($Author);
                $Edition = rtrim($Edition);

                // --- Data Cleaning ENDz
                $SisterUrl_Ancher = $all_li[$i]->find('div[id=field] a', 0);
                if($SisterUrl_Ancher->plaintext != "") {                                   // Check if Sister URL is available
                    $SisterUrl = $SisterUrl_Ancher->getAttribute("href") ;
                    $sister_site_data = SisterSiteData($SisterUrl);
                } else {
                    $sister_site_data = ",,,,,,";
                }
                if($ImageUrl <> "http://images.efollett.com/books/noBookImage.gif") {  // ONly Access Amazon Api if you image FOund
                    $Bk_ISBN = split("/", $ImageUrl);
                    $Bk_ISBN_count = count($Bk_ISBN) -1;
                    $Bk_ISBN = $Bk_ISBN[$Bk_ISBN_count];
                    $Bk_ISBN = explode('.', $Bk_ISBN);
                    $Bk_ISBN = $Bk_ISBN[0];
                    $amazon = getAmazonData("$Bk_ISBN","ItemLookup");
                    if($amazon) {
                        $AmazonListPrice = $amazon['AmazonListPrice'] ;
                        $AmazonDiscountPrice = $amazon['AmazonDiscountPrice'] ;
                        $NonAmazonNewPrice = $amazon['NonAmazonNewPrice'] ;
                        $NonAmazonUsedPrice = $amazon['NonAmazonUsedPrice'] ;
                        $AmazonDetailPageURL = $amazon['AmazonDetailPageURL'] ;

                    }else {
                        $amazon = getAmazonData("$BookTitle, $Author, $Edition","ItemSearch");
                        if($amazon) {
                            $AmazonListPrice = $amazon['AmazonListPrice'] ;
                            $AmazonDiscountPrice = $amazon['AmazonDiscountPrice'] ;
                            $NonAmazonNewPrice = $amazon['NonAmazonNewPrice'] ;
                            $NonAmazonUsedPrice = $amazon['NonAmazonUsedPrice'] ;
                            $AmazonDetailPageURL = $amazon['AmazonDetailPageURL'] ;

                        }
                    }

                }
                echo $row_data = "$initial_csv_row_data,\"$BookTitle\",\"$Author\",\"$Edition\",$ImageUrl,$BK_UsedPrice,$BK_NewPrice,$BK_DigitalPrice,$Bk_ISBN,$AmazonListPrice,$AmazonDiscountPrice,$NonAmazonNewPrice,$NonAmazonUsedPrice,$AmazonDetailPageURL,$SisterUrl,$sister_site_data\n";
                echo "\n";

                fwrite($output, $row_data);

                // Clearing Space
                unset($BookTitle);
                unset($SisterUrl);
                unset($Author);
                unset($Edition);
                unset($ImageUrl);
                unset($BK_UsedPrice);
                unset($BK_NewPrice);
                unset($BK_DigitalPrice);
                unset($Bk_ISBN);
                unset($row_data);

                unset($amazon);
                unset($AmazonListPrice) ;
                unset($NonAmazonNewPrice) ;
                unset($NonAmazonUsedPrice)  ;
                unset($AmazonDiscountPrice) ;
                unset($AmazonDetailPageURL) ;


            }// for
        }

    }else {
        // If no book is found still add the record
        echo $row_data = "$initial_csv_row_data,,,,,,,,\n";
        fwrite($output, $row_data);
    }
    $html->__destruct();

    unset($html);
    unset($ul);


}

//--------------------------------------------------------------------------------------------------------
function getOptions() {
    echo "\n\nEnter 1: Print List of State \n";
    echo "Enter 2: Process CSV File: \n";
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
function ProcessDataDigging_Generic($Store, $University_Name,$Campus_Name) {
    if(PHP_OS == "WINNT") {
        $file_name = "c:\\$University_Name($Campus_Name).csv";
    }else {
        $file_name = "\\$University_Name($Campus_Name).csv";
    }

    $output = fopen($file_name, 'w');
    $row_data = "Program,Term,Department,Course,Section,Course URL,Book Title,BK Author,BK Edition,BK Image URL,BK Used Price,BK New Price,BK Digital Price,BK ISBN,Amazon List Price,Amazon Discount Price,Non Amazon New Price,Non Amazon Used Price,Amazon Detail Page URL,Detailed Link,Author(s),Edition,Publisher,ISBN (10),ISBN (13),ISBN (10) - Digi,ISBN (13) - Digi,List Price,You Pay Price\n";
    fwrite($output, $row_data);


    $Program_arr = file_get_contents("http://www.bkstr.com/webapp/wcs/stores/servlet/LocateCourseMaterialsServlet?requestType=INITIAL&storeId=$Store&demoKey=d&_=");
    $Program_arr = str_replace("<script>parent.doneLoaded('", "", $Program_arr);
    $Program_arr = str_replace("')</script>", "", $Program_arr);

    $Program_arr = json_decode($Program_arr,true);
    $Program_arr = $Program_arr['data'][0];
    foreach($Program_arr as $Program_Name => $Program_Value) {
        $Program_Name_url = str_replace(" ", "%20", $Program_Name);
        $term_arr = file_get_contents("http://www.bkstr.com/webapp/wcs/stores/servlet/LocateCourseMaterialsServlet?requestType=TERMS&storeId=$Store&demoKey=d&programId=$Program_Value&_=");
        $term_arr = str_replace("<script>parent.doneLoaded('", "", $term_arr);
        $term_arr = str_replace("')</script>", "", $term_arr);

        $term_arr = json_decode($term_arr,true);
        $term_arr = $term_arr['data'][0];
        foreach($term_arr as $term_Name => $term_Value) {
            $term_arr = str_replace(" ", "%20", $term_Name);
            $Division_arr = file_get_contents("http://www.bkstr.com/webapp/wcs/stores/servlet/LocateCourseMaterialsServlet?requestType=DIVISIONS&storeId=$Store&demoKey=d&programId=$Program_Value&termId=$term_Value&_=");
            $Division_arr = str_replace("<script>parent.doneLoaded('", "", $Division_arr);
            $Division_arr = str_replace("')</script>", "", $Division_arr);

            $Division_arr = json_decode($Division_arr,true);
            $Division_arr = $Division_arr['data'][0];

            if(!empty($Division_arr)) {
                foreach($Division_arr as $Division_Name => $Division_Value) {
                    $Division_Name_url = str_replace(" ", "%20", $Division_Name);   // Corrects The URL Data, removes spaces

                    $Department_arr = file_get_contents("http://www.bkstr.com/webapp/wcs/stores/servlet/LocateCourseMaterialsServlet?requestType=DEPARTMENTS&storeId=$Store&programId=$Program_Value&termId=$term_Value&divisionName=$Division_Name_url&_=");
                    $Department_arr = str_replace("<script>parent.doneLoaded('", "", $Department_arr);
                    $Department_arr = str_replace("')</script>", "", $Department_arr);

                    $Department_arr = json_decode($Department_arr,true);
                    $Department_arr = $Department_arr['data'][0];
                    foreach($Department_arr as $Department_Name => $Department_Value) {
                        $Department_Name_url = str_replace(" ", "%20", $Department_Name);   // Corrects The URL Data, removes spaces

                        $Course_arr = file_get_contents("http://www.bkstr.com/webapp/wcs/stores/servlet/LocateCourseMaterialsServlet?requestType=COURSES&storeId=$Store&programId=$Program_Value&termId=$term_Value&divisionName=$Division_Name_url&departmentName=$Department_Name_url&_=");
                        $Course_arr = str_replace("<script>parent.doneLoaded('", "", $Course_arr);
                        $Course_arr = str_replace("')</script>", "", $Course_arr);

                        $Course_arr = json_decode($Course_arr,true);
                        $Course_arr = $Course_arr['data'][0];

                        foreach($Course_arr as $Course_Name => $Course_Value) {
                            $Course_Name_url = str_replace(" ", "%20", $Course_Name);   // Corrects The URL Data, removes spaces

                            $Section_arr = file_get_contents("http://www.bkstr.com/webapp/wcs/stores/servlet/LocateCourseMaterialsServlet?requestType=SECTIONS&storeId=$Store&programId=$Program_Value&termId=$term_Value&divisionName=$Division_Name_url&departmentName=$Department_Name_url&courseName=$Course_Name_url&_=");
                            $Section_arr = str_replace("<script>parent.doneLoaded('", "", $Section_arr);
                            $Section_arr = str_replace("')</script>", "", $Section_arr);

                            $Section_arr = json_decode($Section_arr,true);
                            $Section_arr = $Section_arr['data'][0];
                            foreach($Section_arr as $Section_Name => $Section_Value) {
                                $Section_Name_url = str_replace(" ", "%20", $Section_Name);
                                // $delay =  rand(3, 5);
                                //                sleep($delay);

                                $FinalUrl = "http://www.bkstr.com/webapp/wcs/stores/servlet/CourseMaterialsResultsView?catalogId=10001&categoryId=9604&storeId=$Store&langId=-1&programId=$Program_Value&termId=$term_Value&divisionDisplayName=$Division_Name_url&departmentDisplayName=$Department_Name_url&courseDisplayName=$Course_Name_url&sectionDisplayName=$Section_Name_url&demoKey=null&purpose=browse";
                                $initial_csv_row_data = "$Program_Name,$term_Name,$Division_Name,$Department_Name,$Course_Name,$Section_Name,$FinalUrl";
                                MainBookData($FinalUrl,$initial_csv_row_data,$output);

                                echo "\n";
                                echo "Memory Usage  = ".memory_get_usage()/(1024*1024) . "MB  \n\n\n";


                            } // Section

                        } // Course

                    } // Department
                }
            }else {
                $Division_Name = " ";
                $Division_Name_url = str_replace(" ", "%20", $Division_Name);   // Corrects The URL Data, removes spaces

                $Department_arr = file_get_contents("http://www.bkstr.com/webapp/wcs/stores/servlet/LocateCourseMaterialsServlet?requestType=DEPARTMENTS&storeId=$Store&programId=$Program_Value&termId=$term_Value&divisionName=$Division_Name_url&_=");
                $Department_arr = str_replace("<script>parent.doneLoaded('", "", $Department_arr);
                $Department_arr = str_replace("')</script>", "", $Department_arr);

                $Department_arr = json_decode($Department_arr,true);
                $Department_arr = $Department_arr['data'][0];
                foreach($Department_arr as $Department_Name => $Department_Value) {
                    $Department_Name_url = str_replace(" ", "%20", $Department_Name);   // Corrects The URL Data, removes spaces

                    $Course_arr = file_get_contents("http://www.bkstr.com/webapp/wcs/stores/servlet/LocateCourseMaterialsServlet?requestType=COURSES&storeId=$Store&programId=$Program_Value&termId=$term_Value&divisionName=$Division_Name_url&departmentName=$Department_Name_url&_=");
                    $Course_arr = str_replace("<script>parent.doneLoaded('", "", $Course_arr);
                    $Course_arr = str_replace("')</script>", "", $Course_arr);

                    $Course_arr = json_decode($Course_arr,true);
                    $Course_arr = $Course_arr['data'][0];

                    foreach($Course_arr as $Course_Name => $Course_Value) {
                        $Course_Name_url = str_replace(" ", "%20", $Course_Name);   // Corrects The URL Data, removes spaces

                        $Section_arr = file_get_contents("http://www.bkstr.com/webapp/wcs/stores/servlet/LocateCourseMaterialsServlet?requestType=SECTIONS&storeId=$Store&programId=$Program_Value&termId=$term_Value&divisionName=$Division_Name_url&departmentName=$Department_Name_url&courseName=$Course_Name_url&_=");
                        $Section_arr = str_replace("<script>parent.doneLoaded('", "", $Section_arr);
                        $Section_arr = str_replace("')</script>", "", $Section_arr);

                        $Section_arr = json_decode($Section_arr,true);
                        $Section_arr = $Section_arr['data'][0];
                        foreach($Section_arr as $Section_Name => $Section_Value) {
                            $Section_Name_url = str_replace(" ", "%20", $Section_Name);
                            // $delay =  rand(3, 5);
                            //                sleep($delay);

                            $FinalUrl = "http://www.bkstr.com/webapp/wcs/stores/servlet/CourseMaterialsResultsView?catalogId=10001&categoryId=9604&storeId=$Store&langId=-1&programId=$Program_Value&termId=$term_Value&divisionDisplayName=$Division_Name_url&departmentDisplayName=$Department_Name_url&courseDisplayName=$Course_Name_url&sectionDisplayName=$Section_Name_url&demoKey=null&purpose=browse";
                            $initial_csv_row_data = "$Program_Name,$term_Name,Spring 2010,$Department_Name,$Course_Name,$Section_Name,$FinalUrl";
                            MainBookData($FinalUrl,$initial_csv_row_data,$output);

                            echo "\n";
                            echo "Memory Usage  = ".memory_get_usage()/(1024*1024) . "MB  \n\n\n";


                        } // Section

                    } // Course

                } // Department
            } // ELSE to check If Division is Zero
        }

    }

    fclose($output);
}
//------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------




// ----------- Varibales Initilization --------------
$condition = true;
$option = "";
$file_name = "";
$state = "";
$University = "";
$Campus = "";
$Store = "";


// --------------------------------------------------
while($condition) {
    switch($option) {
        case "":
            $option = getOptions();           // General Options
            break;
        case 1:

            unset($state);
            unset($University);
            unset($Campus);
            unset($StoreUrl);

            $state = getState();
            $University = getStateUniversity($state);
            $Campus = getCampusUniversity($University[0]);
            $Store = getBK_BOOKS_URL($Campus[0]);

            $option = getOptions();
            break;
        case 2:
            if($Store<> "") {
                echo "Processsing File here \n\n\n";
                //------------------- Start Processing File
                //ProcessDataDigging($StoreUrl);
                ProcessDataDigging_Generic($Store,$University[1],$Campus[1]);
            }
            $option = getOptions(); 		// General Options
            break;
        case 3:


            exit;
            break;
        default:
            $option = getOptions();			// General Options
            break;
    }

}

//ProcessDataDigging();


?>
