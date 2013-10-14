<?php

ini_set('include_path', '.:classes/');


require_once('GenTool.php');
require_once('DocItem.php');
require_once('DocTable.php');
require_once('DocPage.php');

require_once('Item.php');
require_once("ItemBase.php");
require_once('NavChain.php');

require_once("GenHelpDoc.php");
require_once("GenWebDoc.php");
require_once("GenHelpTips.php");


define('DOC_TYPE', 'ws');

// you can define the debug tag here
define('DEBUG_TAG', 'NO');

define('FOR_WEB', 1);

$pathcommon = 'text/common';
$pathws = 'text/' . DOC_TYPE;
global $static_dir;
$static_dir = $pathws;

$texts = array("$pathcommon/HelpDoc.DB.txt", 
			"$pathcommon/Listener_Help.txt", 
			"$pathcommon/WSPages.txt",
			"$pathcommon/Context_Help.txt",
			"$pathcommon/ExtApp_Help.txt",
			"$pathcommon/Rails_Help.txt",
			"$pathcommon/Rewrite_Help.txt",
		    "$pathcommon/Templates_Help.txt",
			"$pathcommon/RequestFilter_Help.txt",
			"$pathcommon/ServerStat_Help.txt",
			"$pathws/Cache_Help.txt",
			"$pathws/Addons_Help.txt",
			"$pathws/Context_Tbl_Help.txt",
			"$pathws/ServGeneralPage.txt",
			"$pathws/VHSecurityPage.txt",
			"$pathws/WS_Help.txt",
			"$pathws/PageNavDef.txt");

$base = new ItemBase($texts);

$lsws_pageNav = array('DOC_ROOT', 'DOC_NAV', 'LSWS_CONF_NAV', 'LSWS_CONTROL_NAV');

$ws_lb = array('{ws_lb}', '{Ws_Lb}', '{WS_LB}', 
	'{ent_version}', 
	'%LB_%');
$ws_lb_replace = array('web server', 'Web server', 'Web Server', 
	'{tag}Enterprise Edition Only{/}',
	'');

$h = new GenHelpDoc($texts);
$h->genPages( $lsws_pageNav, $base);

$tips_file = "../ws_tips.txt";
$tips = new GenHelpTips($h);
$tips->genTips($lsws_pageNav, $base, $tips_file);

$webdocs = new GenWebDoc();
$webdocs->GenerateWebDocs(new MapLSWS());


