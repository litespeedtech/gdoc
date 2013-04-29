<?php
require_once("GenHelpTips.php");
require_once("ItemBase.php");

$txt = array('HelpDoc.DB.txt', 'PageNavDef.txt',
		    'Templates_Help.txt', 'Listener_Help.txt', 'ServerStat_Help.txt');
$base = new ItemBase($txt);


$ws_lb = array('{ws_lb}', '{Ws_Lb}', '{WS_LB}');
$ws_lb_replace = array('web server', 'Web server', 'Web Server');

$h = new GenHelpTips($txt);
$h->genTips( $base);


?>
