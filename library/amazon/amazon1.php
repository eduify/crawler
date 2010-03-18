<?php

Function getAmazonData($SearchPhrase){
    include("aws_signed_request.php");
   
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


        $am =  getAmazonData("Guide to Presentations,  Munter, 2nd");
       var_dump($am);



?>