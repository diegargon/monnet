<?php

define('IN_WEB', true);

require('include/common.inc.php');
require('include/usermode.inc.php');

$web = new Web($cfg_db, $cfg);
