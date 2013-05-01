<?php
require("include.php");

define('DOC_TYPE', 'ows');

$pathcommon = 'text/common';
$pathws = 'text/' . DOC_TYPE;

$texts = array("$pathcommon/HelpDoc.DB.txt", 
			"$pathcommon/Listener_Help.txt", 
			"$pathcommon/WSPages.txt",
			"$pathcommon/Context_Help.txt",
			"$pathcommon/ExtApp_Help.txt",
			"$pathcommon/Rails_Help.txt",
			"$pathcommon/Rewrite_Help.txt",
		    "$pathcommon/Templates_Help.txt",
			"$pathcommon/ServerStat_Help.txt",
			"$pathws/Context_Tbl_Help.txt",
			"$pathws/ServGeneralPage.txt",
			"$pathws/VHSecurityPage.txt",
			"$pathws/PageNavDef.txt");

$base = new ItemBase($texts);

$lsws_pageNav = array('OLSWS_CONF_NAV', 'OLSWS_CONTROL_NAV');

$ws_lb = array('{ws_lb}', '{Ws_Lb}', '{WS_LB}', 
	'{ent_version}', 
	'%LB_%');
$ws_lb_replace = array('web server', 'Web server', 'Web Server', 
	'',
	'');

$h = new GenHelpDoc($texts);
$h->genPages( $lsws_pageNav, $base);

$tips_file = "../ows_tips.txt";
$tips = new GenHelpTips($h);
$tips->genTips($lsws_pageNav, $base, $tips_file);

