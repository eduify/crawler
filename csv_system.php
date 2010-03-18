<?php
include_once("library/simple_html_dom.php");

Function getAmazonData($SearchPhrase){
    include_once("library/amazon/aws_signed_request.php");

    $public_key = "AKIAIRPU52XIPOIZS5OA";
    $private_key = "MQUKYscxHYenmyPApVY9NmCi/9+KDC2FxiBeZmgn";
    $pxml = aws_signed_request("com", array("Operation"=>"ItemSearch","SearchIndex"=>"Books","Keywords"=>"$SearchPhrase","ResponseGroup"=>"Large"), $public_key, $private_key);


    if ($pxml === False)
    {
        return false;
        // Problem in accessing AMAZON API

    }
   else
   {
    if($pxml->Items->Item->ItemAttributes->ListPrice->FormattedPrice == ""){

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

function MainBookData($url,$initial_csv_row_data,&$output){
    include_once("library/simple_html_dom.php");
    $Main_Data = "";
    $html = file_get_dom($url);
    
    $ul  = $html->find('div[id=material_results] ul');
    
    // Header for csv
    

   // CHeck whether Material Exists
    if($ul != null){
        $total_type_books = count($ul);               // Counting type of books
        for($j=0;$j < $total_type_books; $j++){

            $all_li = $ul[$j]->find('li');
            $total_books =  count($all_li); //This will give us Amount of books

            for($i=0;$i<$total_books; $i++){
                $BookTitle = $all_li[$i]->find('span[class=wrap]', 0)->plaintext ;
                $BookTitle = htmlspecialchars_decode($BookTitle);
                
                $ImageUrl = $all_li[$i]->find('img', 0)->getAttribute("src");

                if($all_li[$i]->find('div[class=field]', 1)->plaintext != ""){
                    $BK_UsedPrice = $all_li[$i]->find('div[class=field]', 1)->find('span[class=emph]', 0)->plaintext;
                }

                if($all_li[$i]->find('div[class=field]', 2)->plaintext != ""){
                    $BK_NewPrice = $all_li[$i]->find('div[class=field]', 2)->find('span[class=emph]', 0)->plaintext;
                }

                if($all_li[$i]->find('div[id=field]', 0)->plaintext != ""){
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
                if($SisterUrl_Ancher->plaintext != ""){                                   // Check if Sister URL is available
                    $SisterUrl = $SisterUrl_Ancher->getAttribute("href") ;
                    $sister_site_data = SisterSiteData($SisterUrl);
                } else{
                    $sister_site_data = ",,,,,,";
                }
                if($ImageUrl <> "http://images.efollett.com/books/noBookImage.gif"){  // ONly Access Amazon Api if you image FOund
                    $amazon = getAmazonData("$BookTitle, $Author, $Edition");
                    if($amazon){
                        $AmazonListPrice = $amazon['AmazonListPrice'] ;
                        $AmazonDiscountPrice = $amazon['AmazonDiscountPrice'] ;
                        $NonAmazonNewPrice = $amazon['NonAmazonNewPrice'] ;
                        $NonAmazonUsedPrice = $amazon['NonAmazonUsedPrice'] ;
                        $AmazonDetailPageURL = $amazon['AmazonDetailPageURL'] ;

                     }
                }
                  echo $row_data = "$initial_csv_row_data,\"$BookTitle\",\"$Author\",\"$Edition\",$ImageUrl,$BK_UsedPrice,$BK_NewPrice,$BK_DigitalPrice,$AmazonListPrice,$AmazonDiscountPrice,$NonAmazonNewPrice,$NonAmazonUsedPrice,$AmazonDetailPageURL,$SisterUrl,$sister_site_data\n";
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
                  unset($row_data);

                  unset($amazon);
                  unset($AmazonListPrice) ;
                  unset($NonAmazonNewPrice) ;
                  unset($NonAmazonUsedPrice)  ;
                  unset($AmazonDiscountPrice) ;
                  unset($AmazonDetailPageURL) ;


            }// for
        }

   }else{
       // If no book is found still add the record
       echo $row_data = "$initial_csv_row_data,,,,,,,,\n";
       fwrite($output, $row_data);
   }
   $html->__destruct();

   unset($html);
   unset($ul);
   
   
}
//--------------------------------------------------------------------------------------------------------
function SisterSiteData($sister_url){
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
function getOptions(){
	echo "\n\nEnter 1: CSV File full path with name: \n";
	echo "Enter 2: Process CSV File: \n";
	echo "Enter 3: Exit: \n";
	$option = fgets(STDIN);
	return $option;
}
//------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------
function get_file_extension($file_name)
{
	return substr(strrchr($file_name,'.'),1);
}
//------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------
function checkFile($file_name){
	
	if(file_exists($file_name)) {  	//  Check whether FIle Exists or Not
		if(get_file_extension($file_name) <> "csv"){
			echo "\n\n\n\n\n------------------------------------------------\n";
			echo "------------------------------------------------\n";
			echo "------------------------------------------------\n";
			echo "\nPlease Enter CSV file\n "; 
			echo "------------------------------------------------\n";
			echo "------------------------------------------------\n";
			echo "------------------------------------------------\n";
		}else{
			echo "\n\n\n\n\n------------------------------------------------\n";
			echo "------------------------------------------------\n";
			echo "------------------------------------------------\n";
			echo "\nCSV File Exist, You can now Process File\n ";
			echo "------------------------------------------------\n";
			echo "------------------------------------------------\n";
			echo "------------------------------------------------\n";
		}
		return true;					
	}else{
			var_dump($file_name);
			echo "\n\n\n\n\n------------------------------------------------\n";
			echo "------------------------------------------------\n";
			echo "------------------------------------------------\n";
			echo "File Does Not Exist, Please check Path or File Name \n";
			echo "------------------------------------------------\n";
			echo "------------------------------------------------\n";
			echo "------------------------------------------------\n";
			return false;
	}	
}
//------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------
function ProcessDataDigging(){
	//xdebug_start_trace();

    $ProgramID = "562";
    $TermID = "100014525";
   
    $output = fopen('c:\scrap\book_data.csv', 'w');
    $row_data = "Program,Term,Division ,Department,Course,Section,Course URL,Book Title,BK Author,BK Edition,BK Image URL,BK Used Price,BK New Price,BK Digital Price,Amazon List Price,Amazon Discount Price,Non Amazon New Price,Non Amazon Used Price,Amazon Detail Page URL,Detailed Link,Author(s),Edition,Publisher,ISBN (10),ISBN (13),ISBN (10) - Digi,ISBN (13) - Digi,List Price,You Pay Price\n";
    fwrite($output, $row_data);
	
    $Division_arr = file_get_contents("http://www.bkstr.com/webapp/wcs/stores/servlet/LocateCourseMaterialsServlet?requestType=DIVISIONS&storeId=10161&programId=$ProgramID&termId=$TermID&_=");
    $Division_arr = str_replace("<script>parent.doneLoaded('", "", $Division_arr);
    $Division_arr = str_replace("')</script>", "", $Division_arr);

    $Division_arr = json_decode($Division_arr,true);
    $Division_arr = $Division_arr['data'][0];

    foreach($Division_arr as $Division_Name => $Division_Value)
    {
        $Division_Name_url = str_replace(" ", "%20", $Division_Name);   // Corrects The URL Data, removes spaces
        
        $Department_arr = file_get_contents("http://www.bkstr.com/webapp/wcs/stores/servlet/LocateCourseMaterialsServlet?requestType=DEPARTMENTS&storeId=10161&programId=$ProgramID&termId=$TermID&divisionName=$Division_Name_url&_=");
        $Department_arr = str_replace("<script>parent.doneLoaded('", "", $Department_arr);
        $Department_arr = str_replace("')</script>", "", $Department_arr);

        $Department_arr = json_decode($Department_arr,true);
        $Department_arr = $Department_arr['data'][0];
        foreach($Department_arr as $Department_Name => $Department_Value)
        {
            $Department_Name_url = str_replace(" ", "%20", $Department_Name);   // Corrects The URL Data, removes spaces

            $Course_arr = file_get_contents("http://www.bkstr.com/webapp/wcs/stores/servlet/LocateCourseMaterialsServlet?requestType=COURSES&storeId=10161&programId=$ProgramID&termId=$TermID&divisionName=$Division_Name_url&departmentName=$Department_Name_url&_=");
            $Course_arr = str_replace("<script>parent.doneLoaded('", "", $Course_arr);
            $Course_arr = str_replace("')</script>", "", $Course_arr);

            $Course_arr = json_decode($Course_arr,true);
            $Course_arr = $Course_arr['data'][0];
			
            foreach($Course_arr as $Course_Name => $Course_Value)
            {
                $Course_Name_url = str_replace(" ", "%20", $Course_Name);   // Corrects The URL Data, removes spaces

                $Section_arr = file_get_contents("http://www.bkstr.com/webapp/wcs/stores/servlet/LocateCourseMaterialsServlet?requestType=SECTIONS&storeId=10161&programId=$ProgramID&termId=$TermID&divisionName=$Division_Name_url&departmentName=$Department_Name_url&courseName=$Course_Name_url&_=");
                $Section_arr = str_replace("<script>parent.doneLoaded('", "", $Section_arr);
                $Section_arr = str_replace("')</script>", "", $Section_arr);

                $Section_arr = json_decode($Section_arr,true);
                $Section_arr = $Section_arr['data'][0];
                foreach($Section_arr as $Section_Name => $Section_Value)
                {
                    $Section_Name_url = str_replace(" ", "%20", $Section_Name);
                   // $delay =  rand(3, 5);
    //                sleep($delay);

                    $FinalUrl = "http://www.bkstr.com/webapp/wcs/stores/servlet/CourseMaterialsResultsView?catalogId=10001&categoryId=9604&storeId=10161&langId=-1&programId=$ProgramID&termId=$TermID&divisionDisplayName=$Division_Name_url&departmentDisplayName=$Department_Name_url&courseDisplayName=$Course_Name_url&sectionDisplayName=$Section_Name_url&demoKey=null&purpose=browse";
                    $initial_csv_row_data = "Stanford University,Winter 2009-2010,$Division_Name,$Department_Name,$Course_Name,$Section_Name,$FinalUrl";
                    MainBookData($FinalUrl,$initial_csv_row_data,$output);
                    
                    

                    echo "\n";
                    echo "Memory Usage  = ".memory_get_usage()/(1024*1024) . "MB  \n\n\n";


                } // Section

            } // Course
            
        } // Department
       
    } // Divisions

    
	fclose($output);		
	//xdebug_stop_trace();
}
//------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------


	// ----------- Varibales Initilization --------------
	$condition = true;
	$option = "";
	$file_name = "";
        $category_map = array();
	// --------------------------------------------------
	/* while($condition){
		switch($option){
			case "":
				$option = getOptions();           // General Options 
			break;
			case 1:
				echo "Enter CSV File full path with name HERE: ";
				$file_name = trim(fgets(STDIN)); 		// Input from user and save it in a variable
				$file_name = str_replace("\n", '', $file_name);   // Remove extra Line Entery
				checkFile($file_name);		  	//  Check whether FIle Exists or Not
				$option = getOptions();
			break;
			case 2:
				if(checkFile($file_name)){
					echo "Processsing File here \n\n\n";
					//------------------- Start Processing File
					ProcessDataDigging($file_name);
				}else{
					
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
			
}*/
 	
ProcessDataDigging();	


?>
