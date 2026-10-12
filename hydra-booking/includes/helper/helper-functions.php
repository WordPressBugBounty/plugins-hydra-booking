<?php
if (! defined('ABSPATH')) {
	exit;
}

if (! function_exists('tfhb_print_r')) {
	function tfhb_print_r($data)
	{
		echo '<pre>';
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		print_r($data);
		echo '</pre>';
		// exit;
	}
}


function tfhb_character_limit_callback($str, $limit, $dots = true)
{
	$str = wp_strip_all_tags((string) $str);
	if ( mb_strlen( $str ) > $limit ) {
		$sub        = mb_substr( $str, 0, $limit );
		$last_space = mb_strrpos( $sub, ' ' );
		if ( false !== $last_space && $last_space > ( $limit * 0.4 ) ) {
			$sub = mb_substr( $sub, 0, $last_space );
		}
		$sub = rtrim( $sub, " \t\n\r\0\x0B.,;:-" );
		if ( true === $dots ) {
			return $sub . '...';
		} else {
			return $sub;
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

function tfhb_is_hydra_booking_pro_active()
{
	if (class_exists('TFHB_INIT_PRO')) {
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
