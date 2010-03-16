<?php
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

    // Url Long books
    $url= "http://www.bkstr.com/webapp/wcs/stores/servlet/CourseMaterialsResultsView?catalogId=10001&categoryId=9604&storeId=10161&langId=-1&programId=562&termId=100014525&divisionDisplayName=Graduate%20School%20of%20Business&departmentDisplayName=BUS&courseDisplayName=GSB%20101&sectionDisplayName=01&demoKey=null&purpose=browse";
    // URL short Books
    $url = "http://www.bkstr.com/webapp/wcs/stores/servlet/CourseMaterialsResultsView?catalogId=10001&categoryId=9604&storeId=10161&langId=-1&programId=562&termId=100014525&divisionDisplayName=Stanford&departmentDisplayName=CHEM&courseDisplayName=130&sectionDisplayName=01&demoKey=d&purpose=browse";
MainBookData($url);

?>


