<?php
define ('WP_USE_THEMES', FALSE);
include (dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/wp-load.php');

$ajax = new WP_CLS_Ajax ();
$ajax->fire ('list');
$ajax->page ('list');
$ajax->view ();
?>
