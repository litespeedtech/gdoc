<?php
require("include.php");

// you can define the debug tag here
define('DEBUG_TAG', 'NO');

define('DOC_TYPE', 'ows');

define('FOR_WEB', 0);

// define globals


$lsws_pageNav = array('OLSWS_DOC_ROOT', 'OLSWS_DOC_NAV', 'OLSWS_CONF_NAV', 'OLSWS_CONTROL_NAV');
$lsws_pageNavTips = array_merge($lsws_pageNav, array('OLSWS_TOOLS_NAV'));

$ws_lb = array('{ws_lb}', '{Ws_Lb}', '{WS_LB}',	'{ent_version}','%LB_%');
$ws_lb_replace = array('web server', 'Web server', 'Web Server', '', '');



function generate($textpath, $genhelpdoc, $gentips, $tipfile)
{
	$pathcommon = $textpath . "/common";
	$pathows = $textpath . "/ows";
	global $static_dir, $lsws_pageNav, $lsws_pageNavTips;
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
			"$pathcommon/WebSocketProxy.txt",
			"$pathcommon/CompilePHP.txt",
			"$pathows/LSIAPIModule.txt",
			"$pathows/Context_Tbl_Help.txt",
			"$pathows/ServGeneralPage.txt",
			"$pathows/VHSecurityPage.txt",
			"$pathows/OWS_Help.txt",
			"$pathows/PageNavDef.txt");

	$base = new ItemBase($texts);

	$h = new GenHelpDoc($texts);
	if ($genhelpdoc)
		$h->genPages( $lsws_pageNav, $base);

	if ($gentips) {
		$tips = new GenPopupTips($h);
		$tips->genTips($lsws_pageNavTips, $base, $tipfile);
	}
}


$pathtext = 'text';
generate($pathtext, true, true,  "../en-US_tips.php");

//chinese tips
echo "output chinese tips \n";
$pathtext = 'text_lang/zh-CN';
generate($pathtext, false, true,  "../zh-CN_tips.php");

