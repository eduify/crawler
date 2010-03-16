<?php
include_once("library/simple_html_dom.php");

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
	
	$html = new simple_html_dom();
	if (($handle = fopen($csv_file , "r")) !== FALSE) {
		$row = 0;
		$output = fopen('c:\scrap\output.csv', 'w');
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		
			if($row == 0){
				// Header of CSV file
				
				$row_data = "Print ISBN,AUTHOR,EDITION,SHORT_TITLE,180 DAYS,360 DAYS,540 DAYS\n";
				fwrite($output, $row_data);
			}else {
				// Save Data from CSV and coursemart To Output.csv
				
				$isbn =  $data[1] ;  // Get ISBN from CSV file
				$html = file_get_dom("http://www.coursesmart.com/".$isbn);
				
				//$author =  $data[1];
				//$edition =  $data[2];
				$short_title =  $data[3];
				
				$subscription = $html->find('div[class=sub] span', 0)->plaintext;
				$price = $html->find('td[class=purchase] span',1)->plaintext;
				
				$c_180 = "";
				$c_360 = "";
				$c_540 = "";
				
				
				if($subscription == "(180 day subscription)" ){
					$c_180 = $price ;
				}else if($subscription == "(360 day subscription)" ){
					$c_360 = $price ;
				}else if($subscription == "(540 day subscription)"){
					$c_540 = $price ;
				}else{
					$c_180 = "NO LISTING";
					$c_360 = "NO LISTING";
					$c_540 = "NO LISTING";
				}
				
				$delay =  rand(3, 5);
				sleep($delay);
				
				$row_data = "$isbn,$author,$edition,$short_title,$c_180,$c_360,$c_540\n";
				fwrite($output, $row_data);
				echo "Row Number : $row \nCurrent Delay $delay Seconds\n";
				echo "$isbn - $author - $edition - $short_title - $c_180 - $c_360 - $c_540\n";
				echo "Memory Usage = ".memory_get_usage()/(1024*1024) . "MB \n\n\n";
				
				
				//--------------------------------------- Saving Data To file --- 
			} // End of else
		$row++;	
		// Clean Up variable
		unset($data);
		unset($html);
		unset($isbn);
		unset($author);
		unset($edition);
		unset($short_title);
		unset($c_180);
		unset($c_360);
		unset($c_540);
		
		unset($row_data);
		unset($subscription);
		unset($price);
		
		}// End of While
		fclose($handle);
		fclose($output);
	}	
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
