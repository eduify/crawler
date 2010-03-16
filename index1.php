<?php
include_once("library/simple_html_dom.php");
    // Url Long books
    $url= "http://www.bkstr.com/webapp/wcs/stores/servlet/CourseMaterialsResultsView?catalogId=10001&categoryId=9604&storeId=10161&langId=-1&programId=562&termId=100014525&divisionDisplayName=Graduate%20School%20of%20Business&departmentDisplayName=BUS&courseDisplayName=GSB%20101&sectionDisplayName=01&demoKey=null&purpose=browse";
    // URL short Books
    //$url = "http://www.bkstr.com/webapp/wcs/stores/servlet/CourseMaterialsResultsView?catalogId=10001&categoryId=9604&storeId=10161&langId=-1&programId=562&termId=100014525&divisionDisplayName=Stanford&departmentDisplayName=CHEM&courseDisplayName=130&sectionDisplayName=01&demoKey=d&purpose=browse";

    $html = file_get_dom($url);
    
    $ul  = $html->find('div[id=material_results] ul');

   // CHeck whether Required Material Exists
   if($ul != null){
       $total_books = count($ul[0]->children);  // count($ul[0]->children) This will give us Amount of books
   
        for($i=0;$i<$total_books; $i++){
             $BookTitle = $ul[0]->children[$i]->find('span[class=wrap]', 0)->plaintext ;
             
             $SisterUrl_Ancher = $ul[0]->children[$i]->find('div[id=field] a', 0);
             if($SisterUrl_Ancher->innerhtml != ""){                                   // Check if Sister URL is available 
                 $SisterUrl = $SisterUrl_Ancher->getAttribute("href") ;
             }
              echo "$BookTitle <br /> $SisterUrl <br /><br />";
            //echo $BookTitle."<br />";
        }// for
   } // if


?>


