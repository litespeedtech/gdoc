<?php

require("include.php");

define('DOC_TYPE', 'lb');

// you can define the debug tag here
define('DEBUG_TAG', 'NO');

define('FOR_WEB', 0);

/*

$lslb_pageNav = array('DOC_ROOT', 'DOC_NAV', 'LSLB_CONF_NAV', 'LSLB_CONTROL_NAV');

$h = new GenHelpDoc($texts);
$h->genPages( $lslb_pageNav, $base);
*/

$config = array();

$config['DOC_TYPE'] = 'lb';
$config['FOR_WEB'] = false;
$config['DEBUG_TAG'] = array();

$config['ws_lb'] = array('{ws_lb}', '{Ws_Lb}', '{WS_LB}',	'{ent_version}', '%LB_%');
$config['ws_lb_replace'] = array('load balancer', 'Load balancer', 'Load Balancer',	'', '');

$config['tips_dir'] = dirname(dirname(__FILE__));
$config['base_dir'] = dirname(__FILE__);
$config['lang'] = array('zh-CN');
$config['doc_nav'] = 'HTMLDOC';
$config['tip_nav'] = 'POPTIPS';

//$tips_file = dirname(dirname(__FILE__)) . "/lb_tipsnew.txt";
//$textpath = dirname(__FILE__) . '/text';
//
//global $lsws_pageNav;
//$lsws_pageNav = array('DOC_ROOT', 'DOC_NAV', 'LSLB_CONF_NAV', 'LSLB_CONTROL_NAV');

//GenTool::generate($textpath, true, true, $tips_file);

GenTool::generateGDoc($config);
