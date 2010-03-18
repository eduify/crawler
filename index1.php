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
           
            
            $all_li = $ul[$j]->find('li');
            $total_books =  count($all_li); //This will give us Amount of books

            for($i=0;$i<$total_books; $i++){
                $BookTitle = $all_li[$i]->find('span[class=wrap]', 0)->plaintext ;

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


                // var_dump($all_li[$i]->find('div[class=field]', 1)->find('span[class=emph]', 0));

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
                $Edition = rtrim($Edition);
                
                // --- Data Cleaning ENDz


                $SisterUrl_Ancher = $all_li[$i]->find('div[id=field] a', 0);
                if($SisterUrl_Ancher->plaintext != ""){                                   // Check if Sister URL is available
                    $SisterUrl = $SisterUrl_Ancher->getAttribute("href") ;
                } // if
                  echo "$i - $BookTitle - $Author - $Edition - $BK_UsedPrice - $BK_NewPrice - $BK_DigitalPrice - $ImageUrl <br /> $SisterUrl <br /><br />";
                  
                  // Clearing Space
                  unset($BookTitle);
                  unset($SisterUrl);
                  unset($Author);
                  unset($Edition);
                  unset($ImageUrl);
                  unset($BK_UsedPrice);
                  unset($BK_NewPrice);
                  unset($BK_DigitalPrice);
                  
                  
            }// for
        }
        
   } // if
}

    // Url Long books
    $url= "http://www.bkstr.com/webapp/wcs/stores/servlet/CourseMaterialsResultsView?catalogId=10001&categoryId=9604&storeId=10161&langId=-1&programId=562&termId=100014525&divisionDisplayName=Graduate%20School%20of%20Business&departmentDisplayName=BUS&courseDisplayName=GSB%20101&sectionDisplayName=01&demoKey=null&purpose=browse";
    // URL short Books
    $url = "http://www.bkstr.com/webapp/wcs/stores/servlet/CourseMaterialsResultsView?catalogId=10001&categoryId=9604&storeId=10161&langId=-1&programId=562&termId=100014525&divisionDisplayName=Stanford&departmentDisplayName=CHEM&courseDisplayName=130&sectionDisplayName=01&demoKey=d&purpose=browse";

$url = "http://www.bkstr.com/webapp/wcs/stores/servlet/CourseMaterialsResultsView?catalogId=10001&categoryId=9604&storeId=10161&langId=-1&programId=562&termId=100014525&divisionDisplayName=Graduate%20School%20of%20Business&departmentDisplayName=BUS&courseDisplayName=GSB%20101&sectionDisplayName=01&demoKey=d&purpose=browse";

    MainBookData($url);

?>


