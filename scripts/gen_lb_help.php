<?php

require("include.php");

define('DOC_TYPE', 'lb');

// you can define the debug tag here
define('DEBUG_TAG', 'NO');

define('FOR_WEB', 0);

$pathcommon = 'text/common';
$pathlb = 'text/' . DOC_TYPE;
global $static_dir;
$static_dir = $pathlb;

$texts = array("$pathcommon/HelpDoc.DB.txt",
			"$pathcommon/Rewrite_Help.txt",
			"$pathcommon/Context_Help.txt",
			"$pathcommon/Templates_Help.txt",
			"$pathcommon/RequestFilter_Help.txt",
			"$pathcommon/Listener_Help.txt",
			"$pathcommon/ExtApp_Help.txt",
			"$pathcommon/ServerStat_Help.txt",
                        "$pathcommon/Cache_Help.txt",
			"$pathlb/LBPages.txt",
			"$pathlb/LB_LoadBalancer_Help.txt",
			"$pathlb/LB_HA_Help.txt",
			"$pathlb/PageNavDef.txt",
                        "$pathlb/LB_Server_General.txt",
                        "$pathlb/LB_Tables.txt");

$base = new ItemBase($texts);

$lslb_pageNav = array('DOC_ROOT', 'DOC_NAV', 'LSLB_CONF_NAV', 'LSLB_CONTROL_NAV');

$ws_lb = array('{ws_lb}', '{Ws_Lb}', '{WS_LB}',
	'{ent_version}', '%LB_%');
$ws_lb_replace = array('load balancer', 'Load balancer', 'Load Balancer',
	'', '');

$h = new GenHelpDoc($texts);
$h->genPages( $lslb_pageNav, $base);

$tips_file = "../lb_tips.txt";
$tips = new GenHelpTips($h);
$tips->genTips($lslb_pageNav, $base, $tips_file);

// $webdocs = new GenWebDoc();
// $webdocs->GenerateWebDocs(new MapLSLB());
