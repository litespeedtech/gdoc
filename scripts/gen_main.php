<?php

require("include.php");

$DOC_TYPE = $GLOBALS['argv'][1];
if (!in_array($DOC_TYPE, array('ws','lb','ows'))) {
    die("missing required doc type [ws|lb|ows]");
}
define('DOC_TYPE', $DOC_TYPE);

$DEBUG = $GLOBALS['argv'][2];
define('DEBUG', $DEBUG);
$config = array();

// add your debug tag here
$config['DEBUG_TAG'] = array();

// common & default part

define('DEFAULT_LANG', 'en-US');

$outbasse = dirname(dirname(__FILE__)) . '/output/';
$config['outdir'] = array(
  'base' => $outbasse,
    'docs' => $outbasse . 'docs/',
    'tips' => $outbasse . 'tips/',
    'web' => $outbasse . 'forweb/',
    'docs_lang' => $outbasse . 'docs_lang/',
    'tips_lang' => $outbasse . 'tips/lang/',
    'web_lang' => $outbasse . 'forweb/lang/',
);

$config['base_dir'] = dirname(__FILE__);
$config['lang'] = array(DEFAULT_LANG);
$config['doc_nav'] = 'HTMLDOC';
$config['tip_nav'] = 'POPTIPS';
$config['ws_lb'] = array('{ent_version}');
$config['FOR_WEB'] = false;


if ($DOC_TYPE == 'lb') {
    $config['ws_lb_replace'] = array('');
}
elseif ($DOC_TYPE == 'ws') {
    $config['FOR_WEB'] = true;
    $config['ws_lb_replace'] = array('{tag}Enterprise Edition Only{/}');

}
elseif ($DOC_TYPE == 'ows') {
    $config['ws_lb_replace'] = array('');
    $config['lang'][] = 'zh-CN';

}

// main

    $db = new HelpDB();
    $db->buildDB();
    $db->generateDocs();


