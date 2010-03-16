<?php

/*$json = '{"meta":[{"request":"PROGRAMS","skip":"false","campusActive":"true","progActive":"true","termActive":"true","size":"1"}],"data":[{"Stanford University":"562"}]}';

$arr = json_decode($json,true);

//var_dump($arr);

var_dump($arr[data][0]);

*/

//$json  = file_get_contents('http://www.bkstr.com/webapp/wcs/stores/servlet/LocateCourseMaterialsServlet?requestType=INITIAL&storeId=10161&_=');


$Division_arr = file_get_contents("http://www.bkstr.com/webapp/wcs/stores/servlet/LocateCourseMaterialsServlet?requestType=DIVISIONS&storeId=10161&programId=562&termId=100014525&_=");
$Division_arr = str_replace("<script>parent.doneLoaded('", "", $Division_arr);
$Division_arr = str_replace("')</script>", "", $Division_arr);

$Division_arr = json_decode($Division_arr,true);
$Division_arr = $Division_arr['data'][0];

foreach($Division_arr as $Division_Name => $Division_Value)
{
    $Division_Name_url = str_replace(" ", "%20", $Division_Name);   // Corrects The URL Data, removes spaces

    $Department_arr = file_get_contents("http://www.bkstr.com/webapp/wcs/stores/servlet/LocateCourseMaterialsServlet?requestType=DEPARTMENTS&storeId=10161&programId=562&termId=100014525&divisionName=$Division_Name_url&_=");
    $Department_arr = str_replace("<script>parent.doneLoaded('", "", $Department_arr);
    $Department_arr = str_replace("')</script>", "", $Department_arr);

    $Department_arr = json_decode($Department_arr,true);
    $Department_arr = $Department_arr['data'][0];
    foreach($Department_arr as $Department_Name => $Department_Value)
    {
        $Department_Name_url = str_replace(" ", "%20", $Department_Name);   // Corrects The URL Data, removes spaces

        $Course_arr = file_get_contents("http://www.bkstr.com/webapp/wcs/stores/servlet/LocateCourseMaterialsServlet?requestType=COURSES&storeId=10161&programId=562&termId=100014525&divisionName=$Division_Name_url&departmentName=$Department_Name_url&_=");
        $Course_arr = str_replace("<script>parent.doneLoaded('", "", $Course_arr);
        $Course_arr = str_replace("')</script>", "", $Course_arr);

        $Course_arr = json_decode($Course_arr,true);
        $Course_arr = $Course_arr['data'][0];
        foreach($Course_arr as $Course_Name => $Course_Value)
        {
            $Course_Name_url = str_replace(" ", "%20", $Course_Name);   // Corrects The URL Data, removes spaces

            $Section_arr = file_get_contents("http://www.bkstr.com/webapp/wcs/stores/servlet/LocateCourseMaterialsServlet?requestType=SECTIONS&storeId=10161&programId=562&termId=100014525&divisionName=$Division_Name_url&departmentName=$Department_Name_url&courseName=$Course_Name_url&_=");
            $Section_arr = str_replace("<script>parent.doneLoaded('", "", $Section_arr);
            $Section_arr = str_replace("')</script>", "", $Section_arr);

            $Section_arr = json_decode($Section_arr,true);
            $Section_arr = $Section_arr['data'][0];
            foreach($Section_arr as $Section_Name => $Section_Value)
            {
                
               // $delay =  rand(3, 5);
//                sleep($delay);

                echo "Memory Usage = ".memory_get_usage()/(1024*1024) . "MB \n\n\n";
               
                    $FinalUrl = "http://www.bkstr.com/webapp/wcs/stores/servlet/CourseMaterialsResultsView?catalogId=10001&categoryId=9604&storeId=10161&langId=-1&programId=562&termId=100014525&divisionDisplayName=$Division_Name_url&departmentDisplayName=$Department_Name_url&courseDisplayName=$Course_Name_url&sectionDisplayName=$Section_Name&demoKey=null&purpose=browse";
                    //header("location: $FinalUrl");
                   
            } // Section

        } // Course
        echo("\n");
    } // Department 
    echo("\n");

} // Divisions


  ?>


