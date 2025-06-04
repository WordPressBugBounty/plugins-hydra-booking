<?php

if ( ! function_exists( 'tfhb_print_r' ) ) {
	function tfhb_print_r( $data ) {
		echo '<pre>';
		print_r( $data );
		echo '</pre>';
		// exit;
	}
}


function tfhb_character_limit_callback( $str, $limit, $dots = true ) {
	if ( strlen( $str ) > $limit ) {
		if ( $dots == true ) {
			return substr( $str, 0, $limit ) . '...';
		} else {
			return substr( $str, 0, $limit );
		}
	} else {
		return $str;
	}
}

/**
 * checked pro plugins is active or not
 *
 * @return string
 */

 function tfhb_is_pro_active() {
	if ( class_exists( 'TFHB_INIT_PRO' ) ) { 
		return true;
	} else {
		return false;
	}
} 

/*
 * Load Template
 * 
 * @param string $template_path
 * @param array $data
 * @return string
 */
 