<?php
require("include.php");

// you can define the debug tag here
define('DEBUG_TAG', 'NO');

define('DOC_TYPE', 'ows');

define('FOR_WEB', 0);

$pathcommon = 'text/common';
$pathows = 'text/' . DOC_TYPE;
global $static_dir;
$static_dir = $pathows;

$texts = array("$pathcommon/HelpDoc.DB.txt", 
			"$pathcommon/Listener_Help.txt", 
			"$pathcommon/WSPages.txt",
			"$pathcommon/Context_Help.txt",
			"$pathcommon/ExtApp_Help.txt",
			"$pathcommon/Rails_Help.txt",
			"$pathcommon/Rewrite_Help.txt",
		    "$pathcommon/Templates_Help.txt",
			"$pathcommon/ServerStat_Help.txt",
			"$pathows/Context_Tbl_Help.txt",
			"$pathows/ServGeneralPage.txt",
			"$pathows/VHSecurityPage.txt",
			"$pathows/OWS_Help.txt",
			"$pathows/WebSocketProxy.txt",
			"$pathows/PageNavDef.txt");

$base = new ItemBase($texts);

$lsws_pageNav = array('OLSWS_DOC_ROOT', 'OLSWS_DOC_NAV', 'OLSWS_CONF_NAV', 'OLSWS_CONTROL_NAV');

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


