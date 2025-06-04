<?php 

namespace HydraBooking\App\Content;

// exit
if ( ! defined( 'ABSPATH' ) ) { exit; };

/**
 * Archive
 *
 * Handles archive pages.
 */

class Archive  {
  
    /**
     * Constructor.
     */
    public function __construct() {
        // Add custom archive hooks.
      
        add_filter('template_include', array($this, 'tfhb_meeting_custom_archive_template'));
        // add_filter('template_include', array($this, 'tfhb_load_custom_taxonomy_template'));

        add_rewrite_rule(
            '^tfhb-host/([^/]+)/?', // Pattern to capture the "username" part
            'index.php?username=$matches[1]', // Redirect to index.php with a query var "username"
            'top'
        );
 
    }

  

    // public function custom_query_vars($query_vars) {
    //     $query_vars[] = 'tfhb_username'; // Add "username" as a query variable
    //     return $query_vars;
    // }
    /**
     * Custom single template for archive pages.
     *
     * @param string $content The current post content.
     * 
     * */
    public function tfhb_meeting_custom_archive_template($template) {
        if (is_post_type_archive('tfhb_meeting')) {
            return plugin_dir_path(__FILE__) . 'Archive/archive-tfhb_meeting.php';
        }

       
        if (is_tax('meeting_category')) {
            // Check the queried term and load a custom template
            $term = get_queried_object();
    
            // Dynamically check for specific term, like 'doctor'
            if ($term) { 

                // Default behavior (fall back to the default taxonomy template)
                return plugin_dir_path(__FILE__) . 'Archive/taxonomy-meeting_category.php';
            }
    
        }
        if (get_query_var('username')) {
            $username = get_query_var('username'); // Get the 'user_archive' query variable
            // Check if the user exists
            $user = get_user_by('login', $username);

           
            if ($user) {

                // Default behavior (fall back to the default taxonomy template)
                return plugin_dir_path(__FILE__) . 'Archive/archive-page-tfhb-host.php'; 
     
            }
        }
        
        return $template; 
    }
    
} 