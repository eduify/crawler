<?php
include("library/curl_form_post/curl.post.class.php");
$form =  new CRL();

$form->setURL("http://shc.bncollege.com/webapp/wcs/stores/servlet/TBListView");

$form->setField('storeId','44558');
$form->setField('langId','-1');
$form->setField('catalogId','10001');
$form->setField('savedListAdded','true');
$form->setField('clearAll','');
$form->setField('viewName','TBWizardView');
$form->setField('removeSectionId','');
$form->setField('mcEnabled','N');
$form->setField('section_1','41720354');
$form->setField('numberOfCoursesAlready','1');
$form->setField('viewTextbooks.x','66');
$form->setField('viewTextbooks.y','15');
$form->setField('sectionList','newSectionNumber');


//setting returned success words
$form->successWord = 'thank you';
if($form->init()){
	echo "Success";
}else{
	echo "error";
}

?>
