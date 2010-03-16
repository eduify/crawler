<?php
include_once("library/simple_html_dom.php");

function MainBookData($url){
    include_once("library/simple_html_dom.php");
    $Main_Data = "";
    $html = file_get_dom($url);
    $ul  = $html->find('div[id=material_results] ul');

   // CHeck whether Required Material Exists
    if($ul != null){
        $total_type_books = count($ul);               // Counting type of books
        for($j=0;$j < $total_type_books; $j++){

            $total_books = count($ul[$j]->children);  // count($ul[$j]->children) This will give us Amount of books
            for($i=0;$i<$total_books; $i++){
                $BookTitle = $ul[$j]->children[$i]->find('span[class=wrap]', 0)->plaintext ;

                $SisterUrl_Ancher = $ul[$j]->children[$i]->find('div[id=field] a', 0);
                if($SisterUrl_Ancher->plaintext != ""){                                   // Check if Sister URL is available
                    $SisterUrl = $SisterUrl_Ancher->getAttribute("href") ;
                } // if
                  echo "$BookTitle <br /> $SisterUrl <br /><br />";
                  $Main_Data['title'] = $BookTitle;
                  $Main_Data['detail_url'] = $SisterUrl;
                  // Clearing Space
                  unset($BookTitle);
                  unset($SisterUrl);


            }// for
        }

   } // if
}
//--------------------------------------------------------------------------------------------------------
function SisterSiteData($sister_url){
    include_once("library/simple_html_dom.php");
    $url = "http://www.cafescribe.com/index.php?option=com_virtuemart&page=shop.product_details&flypage=shop.flypage&isbn13=9780495114789&storeid=670 ";
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

    return "$sister_url,$Edition,$Publisher,$ISBN_10_Print,$ISBN_13_Print,$ISBN_10_Digital,$ISBN_13_Digital";
}


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
function ProcessDataDigging($csv_file){
	//xdebug_start_trace();

        $output = fopen('c:\scrap\output.csv', 'w');
        fwrite($output, $row_data);

        
        
        fclose($output);
		
	//xdebug_stop_trace();
}
//------------------------------------------------------------------------------------
function GetMap_fromHtmlSelect(&$category_map){
    /**
     * There are two options
     * We will get all the data and put it into array
     * Example $data['division']['Department']['course']
     */

    /*
     *--------------------- First We will get Programs
     */
    $url = "";
    $return

    /*
     * -----------------------------------------------------------------
     *
     */

    
    
}

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
 	
ProcessDataDigging("c:\scrap\scraper.csv");	
GetMap_fromHtmlSelect()

?>
