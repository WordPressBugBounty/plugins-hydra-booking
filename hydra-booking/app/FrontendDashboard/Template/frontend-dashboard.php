<?php 
// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Template: Hydra - Dashbaord
 *
 */ 

 
add_filter( 'show_admin_bar', '__return_false' );
wp_head();

// get current page and page template  
    
?>
<meta name="viewport" content="width=device-width, initial-scale=1">
<div id="tfhb-admin-app"></div>
<?php
wp_footer();  
