<?php
require_once("GenHelpDoc.php");
require_once("GenQADoc.php");
require_once("ItemBase.php");

$txt = array('HelpDoc.DB.txt', 'Faq_QA.txt', 'HowTo_QA.txt', 'Trouble_QA.txt', 
			'Templates_Help.txt', 'Listener_Help.txt', 'ServerStat_Help.txt');
$base = new ItemBase($txt);


$h = new GenHelpDoc;
$h->genAll('HelpDoc.DB.txt', $base);
$h->genAll('Listener_Help.txt', $base );
$h->genAll('Templates_Help.txt', $base );
$h->genAll('ServerStat_Help.txt', $base );

$q = new GenQADoc;
$q->genAll('Faq_QA.txt', $base);
$q->genAll('HowTo_QA.txt', $base);
$q->genAll('Trouble_QA.txt', $base);

?>
