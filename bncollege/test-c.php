<?php
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
            //var_dump($pxml);
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

 var_dump(getAmazonData("INTERMEDIATE ACCOUNTING,KIESO","ItemSearch"));

?>
