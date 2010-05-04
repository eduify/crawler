<?php
include_once("library/simple_html_dom.php");
include_once("library/form_post/php_form_post.php");

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
                $Amazon['AmazonISBN10'] = $pxml->Items->Item->ItemAttributes->ISBN;
                $Amazon['AmazonISBN13'] = $pxml->Items->Item->ItemAttributes->EAN;
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
                $Amazon['AmazonISBN10'] = $pxml->Items->Item->ItemAttributes->ISBN;
                $Amazon['AmazonISBN13'] = $pxml->Items->Item->ItemAttributes->EAN;
                return $Amazon;

            } // Else
        }// Else
    }





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

    $output = fopen($file_name, 'w');
    $row_data = "University URL,Term,Department,Course,Section,BN Book Title,BN Author,BN Edition,BN Publisher,BK Used Price,BK New Price,Amazon List Price,Amazon Discount Price,Non Amazon New Price,Non Amazon Used Price,Amazon Detail Page URL,ISBN (10),ISBN (13)\n";
    fwrite($output, $row_data);



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
                    $data = "storeId=$storeID&langId=$langID&catalogId=$catalogID&savedListAdded=true&clearAll=&viewName=TBWizardView&removeSectionId=&mcEnabled=N&section_1=$newSection&numberOfCourseAlready=1&viewTextbooks.x=37&viewTextbooks.y=3&sectionList=newSectionNumber";
                    $finalHTML = do_post_request("$universityURL/webapp/wcs/stores/servlet/TBListView",$data);
                    $finalHTML = str_get_html($finalHTML);
                    $bookArray = $finalHTML->find("div[id=bookTbl]",0)->children();
                    for($i_books=0; $i_books < count($bookArray);$i_books++) {
                        $bookArray_1 = $bookArray[$i_books]->children();
                        $bookDataPart1 = $bookArray_1[1]->find('table',0)->children();
                        $bookDataPart2 = $bookArray_1[1]->find('table',1)->children();
                        //--------------------------------------------------------------------------
                        $bookTitle = $bookDataPart1[0]->find('a',0)->innertext;
                        $bookTitle = str_replace(" ", "", $bookTitle);
                        //--------------------------------------------------------------------------
                        $bookAuthor = $bookDataPart1[2]->find('span',0)->innertext;
                        $bookAuthor = str_replace(" ", "", $bookAuthor);
                        //--------------------------------------------------------------------------
                        $bookEditionPublisher = $bookDataPart1[4]->find('td',0)->innertext;
                        $bookEditionPublisher = split("<br />", $bookEditionPublisher);
                        $bookEdition = $bookEditionPublisher[0];
                        $bookEdition = str_replace("\n", "", $bookEdition );
                        $bookEdition = ltrim($bookEdition);
                        $bookEdition = rtrim($bookEdition);
                        $bookEdition = str_replace("Edition:", "", $bookEdition );
                        //--------------------------------------------------------------------------
                        $bookPublisher = $bookEditionPublisher[1];
                        $bookPublisher = str_replace("\n", "", $bookPublisher );
                        $bookPublisher = ltrim($bookPublisher);
                        $bookPublisher = rtrim($bookPublisher);
                        $bookPublisher = str_replace("Publisher:", "", $bookPublisher );
                        //--------------------------------------------------------------------------
                        $bookUsedPrice = $bookDataPart2[4]->children(2)->find('span',0)->innertext;
                        $bookUsedPrice = str_replace("\n", "", $bookUsedPrice );
                        $bookUsedPrice = ltrim($bookUsedPrice);
                        $bookUsedPrice = rtrim($bookUsedPrice);
                        //--------------------------------------------------------------------------
                        $bookNewPrice = $bookDataPart2[6]->children(2)->find('span',0)->innertext;
                        $bookNewPrice = str_replace("\n", "", $bookNewPrice );
                        $bookNewPrice = ltrim($bookNewPrice);
                        $bookNewPrice = rtrim($bookNewPrice);
                        //--------------------------------------------------------------------------
                        $amazon = getAmazonData("$bookTitle, $bookAuthor, $bookEdition","ItemSearch");
                        if($amazon) {
                            $AmazonListPrice = $amazon['AmazonListPrice'] ;
                            $AmazonDiscountPrice = $amazon['AmazonDiscountPrice'] ;
                            $NonAmazonNewPrice = $amazon['NonAmazonNewPrice'] ;
                            $NonAmazonUsedPrice = $amazon['NonAmazonUsedPrice'] ;
                            $AmazonDetailPageURL = $amazon['AmazonDetailPageURL'] ;
                            $AmazonISBN10 = $Amazon['AmazonISBN10'];
                            $AmazonISBN13 = $Amazon['AmazonISBN13'];

                        }
                        $row_data = "\"$universityURL\",\"$termName\",\"$deptName\",\"$courseName\",\"$sectionName\",\"$bookTitle\",\"$bookAuthor\",\"$bookEdition\",\"$bookPublisher\",\"$bookUsedPrice\",\"$bookNewPrice\",\"$AmazonListPrice\",\"$AmazonDiscountPrice\",\"$NonAmazonNewPrice\",\"$NonAmazonUsedPrice\",\"$AmazonDetailPageURL\",\"$AmazonISBN10\",\"$AmazonISBN13\"\n";
                        fwrite($output, $row_data);
                        unset($universityURL,$termName,$termArray,$termID,$deptName,$deptArray,$deptID,$courseName,$courseArray,$courseID,$sectionName,$sectionID,$sectionArray,$bookTitle,$bookAuthor,$bookEdition,$bookPublisher,$bookUsedPrice,$bookNewPrice,$AmazonListPrice,$AmazonDiscountPrice,$NonAmazonNewPrice,$NonAmazonUsedPrice,$AmazonDetailPageURL,$AmazonISBN10,$AmazonISBN13);
                      

                        echo "\n\n";
                        echo "Memory Usage  = ".memory_get_usage()/(1024*1024) . "MB  \n\n\n";
                        //--------------------------------------------------------------------------

                    } // main data
                    $finalHTML->__destruct();

                    //---------------------------------------

                }// Section
                $sectionHTML->__destruct();
            }// Courses
            $courseHTML->__destruct();
        }//dept
        $deptHTML->__destruct();
    }//HTML and term
    $html->__destruct();
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
            $universityType = getSchoolType($state);
            $universityURL = getUniversity($state,$universityType);
            $finalURL = getUniversityRedirectedURL($universityURL);

            $option = getOptions();
            break;
        case 2:
            if($state<> "" and $universityType<>"" and $universityURL) {
                echo "Processsing File here \n\n\n";
                //------------------- Start Processing File

                ProcessDataDigging_Generic($universityURL,$finalURL);
            }else {
                echo "\n\n University Not Found - Please Select State, School Type, University again \n\n";
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


?>
