<?php

require("include.php");

$doc_type = $GLOBALS['argv'][1];
if (!in_array($doc_type, array('ws','lb','ows'))) {
    die("missing required doc type [ws|lb|ows]");
}

$DEBUG = $GLOBALS['argv'][2];

define('DEBUG', $DEBUG);

Config::Init($doc_type, dirname(__FILE__));
HelpDB::GenerateDocs();

