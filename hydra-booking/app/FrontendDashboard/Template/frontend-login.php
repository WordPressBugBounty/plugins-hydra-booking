<?php 
// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Template: Hydra - Login
 *
 */

get_header();
while ( have_posts() ) :
	the_post();  
    echo do_shortcode('[hydra_login_form]');
	the_content();
endwhile;
get_footer(); 
