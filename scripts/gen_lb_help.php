<?php
require_once("GenHelpDoc.php");
require_once("GenQADoc.php");
require_once("ItemBase.php");
require_once("GenHelpTips.php");

$txt = array('HelpDoc.DB.txt', 'LBPages.txt',  'LB_LoadBalancer_Help.txt', 'LB_HA_Help.txt',
			'Templates_Help.txt', 'Listener_Help.txt', 'ServerStat_Help.txt');
$base = new ItemBase($txt);

$lslb_pageNav = array('LSLB_CONF_NAV');

$ws_lb = array('{ws_lb}', '{Ws_Lb}', '{WS_LB}', 
	'{ent_version}', '%LB_%');
$ws_lb_replace = array('load balancer', 'Load balancer', 'Load Balancer', 
	'', '');

$h = new GenHelpDoc($txt);
$h->genPages( $lslb_pageNav, $base);

//$q = new GenQADoc;
//$q->genAll('Faq_QA.txt', $base);
//$q->genAll('HowTo_QA.txt', $base);
//$q->genAll('Trouble_QA.txt', $base);

$tips_file = "../lb_tips.txt";
$tips = new GenHelpTips($h);
$tips->genTips($lslb_pageNav, $base, $tips_file);

?>
