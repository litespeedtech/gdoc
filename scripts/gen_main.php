<?php

require("include.php");

$DOC_TYPE = $GLOBALS['argv'][1];
if (!in_array($DOC_TYPE, array('ws','lb','ows'))) {
    die("missing required doc type [ws|lb|ows]");
}

$DEBUG = $GLOBALS['argv'][2];

define('DEBUG', $DEBUG);

Config::Init($DOC_TYPE, dirname(__FILE__));
HelpDB::GenerateDocs();

