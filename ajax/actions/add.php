<?php
define ('WP_USE_THEMES', FALSE);
include (dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/wp-load.php');
echo '<div class="wp-classifieds-newad"></div><div class="wp-classifieds-upload"><div class="wp-classifieds-upload-button">Upload</div><div class="wp-classifieds-upload-cancel">Cancel</div><div class="wp-classifieds-upload-status"></div><div class="wp-classifieds-upload-queue"></div></div>';
?>
