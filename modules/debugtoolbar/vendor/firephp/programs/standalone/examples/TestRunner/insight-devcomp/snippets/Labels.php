<?php

// NOTE: You must have FirePHP Companion installed (http://www.christophdorn.com/Tools/)

// See FirePHP Companion or Firebug Console for result (depending on $_GET['target'])

define('INSIGHT_CONFIG_PATH', dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'package.json');
require_once('FirePHP/Init.php');

$console = FirePHP::to('page')->console();
if(isset($_GET['target'])) {    // set by the drop-down in the reference
    $console = FirePHP::to($_GET['target'])->console();
    if($_GET['target']=='request') {
        FirePHP::to('controller')->triggerInspect();
    }
}


$console->log('Plain message');
$console->label('Label')->log('Labelled message');
