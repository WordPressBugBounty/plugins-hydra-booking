<?php
namespace HydraBooking\Admin\Controller;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class NoticeController {
    private $api_base_url;

    // Constructor
    public function __construct() {
        $this->api_base_url = 'https://portal.themefic.com/wp-admin/admin-ajax.php';

        add_action('wp_ajax_tfhb_user_registration_license', [$this, 'tfhb_user_registration_license_callback']);
        add_action('wp_ajax_tfhb_cart_item_license', [$this, 'tfhb_cart_item_license_callback']);
    }

    // Register Callback
    public function tfhb_user_registration_license_callback() {
        check_ajax_referer('wp_rest', 'nonce');

        if (!isset($_POST['email']) || !is_email($_POST['email'])) {
            wp_send_json_error(['message' => 'Invalid email address.']);
        }

        $email = sanitize_email($_POST['email']);
        $api_url = $this->api_base_url . '?action=tfur_user_register';
        $response = wp_remote_post($api_url, [
            'timeout' => 10,
            'body'    => [
                'key' => $email,
                'url' => TFHB_URL
            ],
            'cookies' => $_COOKIE,
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error(['message' => 'API request failed: ' . $response->get_error_message()]);
        }

        $response_body = json_decode(wp_remote_retrieve_body($response), true);

        if ($response_body['data']['status']) {
            wp_send_json_success(['message' => 'Check your inbox and set a password for free licensing!']);
        } else {
            if(!empty($response_body['data']['exits'])){
                wp_send_json_error([
                    'message' => $response_body['data']['message'],
                    'exits'   => $response_body['data']['exits']
                ]);
            }else{
                wp_send_json_error(['message' => $response_body['data']['message'] ?? 'Something went wrong.']);
            }
        }

        wp_die();
    }

    // Add To Cart Callback
    public function tfhb_cart_item_license_callback() {
        check_ajax_referer('wp_rest', 'nonce');

        if (!isset($_POST['key'])) {
            wp_send_json_error(['message' => 'Invalid Key.']);
        }

        $key = sanitize_text_field($_POST['key']);
        $api_url = $this->api_base_url . '?action=tfur_user_add_to_cart';

        $response = wp_remote_post($api_url, [
            'timeout' => 10,
            'body'    => [
                'key' => $key,
                'url' => TFHB_URL
            ],
            'cookies' => $_COOKIE,
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error(['message' => 'API request failed: ' . $response->get_error_message()]);
        }

        $response_body = json_decode(wp_remote_retrieve_body($response), true);

        if (!empty($response_body['data']['message'])) {
            wp_send_json_success([
                'message' => 'Product added to remote cart successfully!',
                'url' => $response_body['data']['cart_url']
            ]);
        } else {
            wp_send_json_error(['message' => $response_body['data']['message'] ?? 'Something went wrong.']);
        }

        wp_die();
    }
}