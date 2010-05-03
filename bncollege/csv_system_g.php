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
function SisterSiteData($sister_url) {
    include_once("library/simple_html_dom.php");
    $url = $sister_url;
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

    $html->__destruct();
    unset($html);
    return "\"$Author\",$Edition,\"$Publisher\",$ISBN_10_Print,$ISBN_13_Print,$ISBN_10_Digital,$ISBN_13_Digital,$ListPrice,$YouPayPrice";
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
function getState() {

    $html = file_get_dom("http://www.bncollege.com/college.aspx");
    $stateArray = $html->find('select[id=cboStateID] option') ;
    $totalState = count($stateArray);

    $state = array();
    for($i=1;$i<$totalState;$i++) {
        $state[$i] = $stateArray[$i]->getAttribute('value');
        echo "$i - ".$stateArray[$i]->innertext." \n" ;
    }
    echo "\n\nEnter #(state): Select State\n";
    $selecttion = fgets(STDIN);
    $selecttion = trim($selecttion); 		// Input from user and save it in a variable
    $selecttion = str_replace("\n", '', $selecttion);
    return $state[$selecttion];
}
//------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------
function getSchoolType() {

    $html = file_get_dom("http://www.bncollege.com/college.aspx");
    $schoolTypeArray = $html->find('select[id=cboCollegeType] option') ;
    $totalSchoolType = count($schoolTypeArray);

    $schoolType = array();
    for($i=1;$i<$totalSchoolType;$i++) {
        $schoolType[$i] = $schoolTypeArray[$i]->getAttribute('value');
        echo "$i - ".$schoolTypeArray[$i]->innertext." \n" ;
    }
    echo "\n\nEnter #(School Type): Select School Type\n";
    $selecttion = fgets(STDIN);
    $selecttion = trim($selecttion); 		// Input from user and save it in a variable
    $selecttion = str_replace("\n", '', $selecttion);
    return $schoolType[$selecttion];
}

//------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------
function getUniversity($state,$collegeType) {
    $data = "__VIEWSTATE=dDwtMTc1MzAwNTM2NTs7Pq%2BGnOF4YWQiopXH7fPjhYmOcboy&txtCollegeName=&cboStateID=$state&cboCollegeType=$collegeType&cmdSubmit=Search";
    $opts = array(
            'http'=>array(

                    'method'=>"POST",
                    'header'=>"User-Agent: Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.9) Gecko/20100315 Firefox/3.5.9\r\n".
                            "Content-length: " . strlen($data)."\r\n".
                            "Connection: keep-alive\r\n".
                            "Accept-Encoding: gzip,deflate\r\n",
                    'content' => $data));

    $context = stream_context_create($opts);
    $fp = fopen("http://www.bncollege.com/college.aspx", 'r',false,$context);
    $html = fread($fp,2000000);
    $html = str_get_html($html);


    if($html->find('table[class=grid]',0)->plaintext <> "") {

        $schoolTypeArray = $html->find('table[class=grid] tr');

        $totalSchoolType = count($schoolTypeArray);
        $schoolType = array();
        for($i=1;$i<$totalSchoolType;$i++) {
            $schoolType[$i] = str_replace("bkstore", "bncollege", $schoolTypeArray[$i]->find('td', 1)->find('a',0)->getAttribute('href'));
            echo "$i - ".$schoolTypeArray[$i]->find('td', 1)->find('a',0)->innertext." \n" ;
        }
        echo "\n\nEnter #(University): Select University\n";
        $selecttion = fgets(STDIN);
        $selecttion = trim($selecttion); 		// Input from user and save it in a variable
        $selecttion = str_replace("\n", '', $selecttion);
        $selecttion = str_replace(" ", '', $selecttion);
        return $schoolType[$selecttion];
    }else {
        return false;
    }




}
//------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------
function getUniversityRedirectedURL($url) {
    $html = file_get_dom($url);
    $fullURL = $html->find('meta',0)->getAttribute('content');
    $fullURL = str_replace("0;URL=", "", $fullURL);
    $fullURL = str_replace('"', "", $fullURL);
    return $url.$fullURL;

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
function ProcessDataDigging_Generic($universityURL,$finalURL) {
    if(PHP_OS == "WINNT") {
        $file_name = "c:\\$University_Name($Campus_Name).csv";
    }else {
        $file_name = "\\$University_Name($Campus_Name).csv";
    }

    //$output = fopen($file_name, 'w');
    $row_data = "University URL,Term,Department,Course,Section,Course URL,Book Title,BN Author,BN Edition,BN Publisher,BK Used Price,BK New Price,Amazon List Price,Amazon Discount Price,Non Amazon New Price,Non Amazon Used Price,Amazon Detail Page URL,ISBN (10),ISBN (13),ISBN (10) - Digi,ISBN (13) - Digi\n";
    //fwrite($output, $row_data);



    $finalURL = "http://shc.bncollege.com/webapp/wcs/stores/servlet/BNCBHomePage?storeId=44558&catalogId=10001&langId=-1";
    $universityURL = "http://shc.bncollege.com";

    $html = file_get_dom($finalURL);
    $campusID = $html->find('input[name=campusId]',0)->getAllAttributes();
    $campusID  = $campusID['value'];

    $storeID = $html->find('input[name=storeId]',0)->getAllAttributes();
    $storeID  = $storeID['value'];

    $catalogID = $html->find('input[name=catalogId]',0)->getAllAttributes();
    $catalogID  = $catalogID['value'];

    $langID = $html->find('input[name=langId]',0)->getAllAttributes();
    $langID  = $langID['value'];

    $campus1 = $html->find('input[name=campus1]',0)->getAllAttributes();
    $campus1  = $langID['value'];


    $termArray = $html->find('select[name=s2] option');
    for($i_term=1; $i_term < count($termArray);$i_term++) {
        $termName = $termArray[$i_term]->innertext;
        $termName = ltrim($termName);
        $termName = rtrim($termName);

        $termID = $termArray[$i_term]->getAttribute('value');
        $termID = ltrim($termID);
        $termID = rtrim($termID);

        $deptHTML = file_get_dom("$universityURL/webapp/wcs/stores/servlet/TextBookProcessDropdownsCmd?campusId=$campusID&termId=$termID&deptId=&courseId=&sectionId=&storeId=$storeID&catalogId=$catalogID&langId=-1&dojo.transport=xmlhttp&dojo.preventCache=".rand(1200000000000, 1272882419264));
        $deptArray = $deptHTML->find('select[name=s3] option');
        for($i_dept=1; $i_dept < count($deptArray);$i_dept++) {
            $deptName = $deptArray[$i_dept]->innertext;
            $deptName = ltrim($deptName);
            $deptName = rtrim($deptName);

            $deptID = $deptArray[$i_dept]->getAttribute('value');
            $deptID = ltrim($deptID);
            $deptID = rtrim($deptID);

            $courseHTML = file_get_dom("$universityURL/webapp/wcs/stores/servlet/TextBookProcessDropdownsCmd?campusId=$campusID&termId=$termID&deptId=$deptID&courseId=&sectionId=&storeId=$storeID&catalogId=$catalogID&langId=-1&dojo.transport=xmlhttp&dojo.preventCache=".rand(1200000000000, 1272882419264));
            $courseArray = $courseHTML->find('select[name=s4] option');
            for($i_course=1; $i_course < count($courseArray);$i_course++) {
                $courseName = $courseArray[$i_course]->innertext;
                $courseName = ltrim($courseName);
                $courseName = rtrim($courseName);

                $courseID = $courseArray[$i_course]->getAttribute('value');
                $courseID = ltrim($courseID);
                $courseID = rtrim($courseID);

                $sectionHTML = file_get_dom("$universityURL/webapp/wcs/stores/servlet/TextBookProcessDropdownsCmd?campusId=$campusID&termId=$termID&deptId=$deptID&courseId=$courseID&sectionId=&storeId=$storeID&catalogId=$catalogID&langId=-1&dojo.transport=xmlhttp&dojo.preventCache=".rand(1200000000000, 1272882419264));
                $sectionArray = $sectionHTML->find('select[name=s5] option');
                for($i_section=1; $i_section < count($sectionArray);$i_section++) {
                    $sectionName = $sectionArray[$i_section]->innertext;
                    $sectionName = ltrim($sectionName);
                    $sectionName = rtrim($sectionName);

                    $sectionID = $sectionArray[$i_section]->getAttribute('value');
                    $sectionID = ltrim($sectionID);
                    $sectionID = rtrim($sectionID);

                    $newSection = $sectionID;
                    $newSection  = str_replace("N_", "", $newSection);
                    
                    //---------------------------------------
                    $data = "storeId=$storeID&langId=$langID&catalogId=$catalogID&savedListAdded=true&clearAll=&viewName=TBWizardView&removeSectionId=&mcEnabled=N&section_1=$newSection&numberOfCoursesAlready=1&viewTextbooks.x=37&viewTextbooks.y=3&sectionList=newSectionNumber";
                    $opts = array(
                            'http'=>array(

                                    'method'=>"POST",
                                    'header'=>"User-Agent: Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.9) Gecko/20100315 Firefox/3.5.9\r\n".
                                            "Content-length: " . strlen($data)."\r\n".
                                            "Connection: keep-alive\r\n".
                                            "Accept-Encoding: gzip,deflate\r\n",
                                    'content' => $data));

                    $context = stream_context_create($opts);
                    echo $htmlFinal = file_get_contents("$universityURL/webapp/wcs/stores/servlet/TBListView0", false, $context);
                    echo "";
                    exit;

                    //---------------------------------------

                }
            }
        }
    }






    //fclose($output);
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
//while($condition) {
//    switch($option) {
//        case "":
//            $option = getOptions();           // General Options
//            break;
//        case 1:
//
//            unset($state);
//            unset($University);
//            unset($Campus);
//            unset($StoreUrl);
//
//            $state = getState();
//            $universityType = getSchoolType($state);
//            $universityURL = getUniversity($state,$universityType);
//            $finalURL = getUniversityRedirectedURL($universityURL);
//
//            $option = getOptions();
//            break;
//        case 2:
//            if($state<> "" and $universityType<>"" and $universityURL) {
//                echo "Processsing File here \n\n\n";
//                //------------------- Start Processing File
//
//                ProcessDataDigging_Generic($universityURL,$finalURL);
//            }else {
//                echo "\n\n University Not Found - Please Select State, School Type, University again \n\n";
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

ProcessDataDigging_Generic($universityURL,$finalURL);
?>
