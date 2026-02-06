<?php
namespace HydraBooking\Admin;

use HydraBooking\Admin\Controller\AdminMenu; 
use HydraBooking\Admin\Controller\Notification;
use HydraBooking\Admin\Controller\UpdateController;
use HydraBooking\Services\Integrations\Zoom\ZoomServices;
use HydraBooking\Migration\Migration;
use HydraBooking\Admin\Controller\NoticeController;
use HydraBooking\Admin\Controller\licenseController;
use HydraBooking\License\HydraBooking; 
// Load Migrator
use HydraBooking\DB\Migrator;

	// exit
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

class Admin {

	// constaract
	public function __construct() { 
		// run migrator
		new Migrator();
	

		// admin menu
		new AdminMenu();
 

		// update controller
		new UpdateController();

		// notice controller
		new NoticeController();

		// Notification controller
		// new Notification();

		// activation hooks
		register_activation_hook( TFHB_URL, array( $this, 'activate' ) );

		Migration::instance();

		// license controller
        new  HydraBooking();
		new licenseController();
		

		add_action( 'admin_init', array( $this, 'tfhb_hydra_activation_redirect' ) );

		// Update Existing User Role
		add_action( 'admin_init', array( $this, 'plugins_update_v_1_0_10' ) );

		// 
		// add dome in admin footer based one page template
		add_action( 'admin_footer', array( $this, 'add_admin_footer_content' ) );


		add_action('wp_ajax_tfhb_hydra_manage_plugin', array( $this, 'tfhb_hydra_manage_plugin' ) );
	}

	public function add_admin_footer_content() {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return;
		}

		$screen = get_current_screen();

		if ( empty( $screen ) || 'toplevel_page_hydra-booking' !== $screen->id ) {
			return;
		}

		 echo $this->tfhb_sidebar(); 
	}


	public function tfhb_hydra_manage_plugin() {
		check_ajax_referer('wp_rest', 'security');

		if (!current_user_can('install_plugins')) {
			wp_send_json_error('You do not have permission to perform this action.');
		}

		$plugin_slug = isset($_POST['plugin_slug']) ? sanitize_text_field($_POST['plugin_slug']) : '';
		$plugin_filename = isset($_POST['plugin_filename']) ? sanitize_text_field($_POST['plugin_filename']) : '';
		$plugin_action = isset($_POST['plugin_action']) ? sanitize_text_field($_POST['plugin_action']) : '';

		if (!$plugin_slug || !$plugin_action) {
			wp_send_json_error('Invalid request.');
		}

		include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		if ($plugin_action === 'install') {
			$api = plugins_api('plugin_information', ['slug' => $plugin_slug]);

			if (is_wp_error($api)) {
				wp_send_json_error($api->get_error_message());
			}

			$upgrader = new \Plugin_Upgrader(new \WP_Ajax_Upgrader_Skin());
			$install_result = $upgrader->install($api->download_link);

			if (is_wp_error($install_result)) {
				wp_send_json_error($install_result->get_error_message());
			}

			wp_send_json_success(['message' => 'Installed successfully.']);
		}

		if ($plugin_action === 'activate') {
			$plugin_path = WP_PLUGIN_DIR . '/' . $plugin_slug . '/' . $plugin_filename . '.php';

			if (!file_exists($plugin_path)) {
				wp_send_json_error('Plugin file not found.');
			}

			$activate_result = activate_plugin($plugin_path);

			if (is_wp_error($activate_result)) {
				wp_send_json_error($activate_result->get_error_message());
			}

			wp_send_json_success(['message' => 'Activated successfully.']);
		}

		wp_send_json_error('Invalid action.');
	}


	public function activate() {
		// $Migrator = new Migrator();
		new Migrator();
	}

	public function tfhb_hydra_activation_redirect() {
		if ( wp_doing_ajax() ) {
			return;
		}

		if ( ! get_option( 'tfhb_hydra_quick_setup' ) ) {

			update_option( 'tfhb_hydra_quick_setup', 1 );
			wp_redirect( admin_url( 'admin.php?page=hydra-booking#/setup-wizard' ) );

			// exit;
		}
	}

	public function plugins_update_v_1_0_10(){

 
		if( TFHB_VERSION == '1.0.10' && get_option( 'tfhb_update_status' ) != '1.0.10' ) {
			$role = get_role( 'tfhb_host' );
			// remove capabilities
			$role->remove_cap( 'edit_posts' );
			$role->remove_cap( 'edit_pages' );
			$role->remove_cap( 'edit_others_posts' );
			$role->remove_cap( 'create_posts' );
			$role->remove_cap( 'manage_categories' );
			$role->remove_cap( 'publish_posts' );
			$role->remove_cap( 'edit_themes' );
			$role->remove_cap( 'install_plugins' );
			$role->remove_cap( 'update_plugin' );
			$role->remove_cap( 'update_core' );
			$role->remove_cap( 'manage_options' );

			// Tfhb Update Status
			update_option( 'tfhb_update_status', '1.0.10' );
		} 
	}


	public function tfhb_sidebar() {
		?>

		<div class="tfhb-dashboard-sidebar-content" style="display: none;">
			<div class="tfhb-sidebar-wrap">
				<!-- promo banner  -->
				<?php echo do_action('tfhb_sidebar_promo_banner', ''); ?>
				 
				<div class="tfhb-sidebar-content">

					<div class="tfhb-plugin-lists">
						<h3>Power up your website</h3>
						<?php echo $this->tfhb_get_sidebar_plugin_list(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized ?>
					</div>

					<div class="tfhb-customization-quote">
						<div class="tfhb-quote-content">
							<h3><?php echo esc_html__('Need Help Tweaking Your WordPress Site?', 'hydra-booking');  ?></h3>
							<p><?php echo esc_html__('Want to make small changes, add features, or need customization? Our team can do it for you — just $29/hour, no hassle.', 'hydra-booking'); ?></p>
							<a href="<?php echo esc_url( tfhb_utm_generator( 'https://portal.themefic.com/hire-us/', array( 'utm_medium' => 'dashboard_free_quote' ) ) ); ?>" target="_blank" class="tfhb-admin-btn tfhb-btn-secondary">
							<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
							<g clip-path="url(#clip0_1066_1543)">
								<path d="M8.3334 7.49995L5.8334 9.99995L8.3334 12.4999M11.6667 12.4999L14.1667 9.99995L11.6667 7.49995M2.4934 13.6183C2.61593 13.9274 2.64321 14.2661 2.57173 14.5908L1.68423 17.3324C1.65564 17.4715 1.66303 17.6155 1.70571 17.7509C1.7484 17.8863 1.82495 18.0085 1.92812 18.106C2.03129 18.2035 2.15766 18.273 2.29523 18.308C2.43281 18.343 2.57704 18.3422 2.71423 18.3058L5.5584 17.4741C5.86483 17.4133 6.18218 17.4399 6.47423 17.5508C8.25372 18.3818 10.2695 18.5576 12.166 18.0472C14.0625 17.5368 15.7178 16.373 16.8398 14.7611C17.9618 13.1492 18.4785 11.1928 18.2986 9.23707C18.1188 7.28136 17.254 5.45201 15.8568 4.07178C14.4596 2.69155 12.6198 1.84915 10.6621 1.6932C8.70429 1.53724 6.75435 2.07777 5.15627 3.2194C3.55819 4.36103 2.41468 6.0304 1.92748 7.93298C1.44028 9.83556 1.64071 11.8491 2.4934 13.6183Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
							</g>
							<defs>
								<clipPath id="clip0_1066_1543">
								<rect width="20" height="20" fill="white"/>
								</clipPath>
							</defs>
							</svg>	
							<?php echo esc_html__('Get Free Quote', 'hydra-booking');  ?>
							</a>								
						</div>
					</div>

					<div class="tfhb-quick-access">
						<h3><?php echo esc_html__('Helpful Resources', 'hydra-booking');  ?></h3>
						<div class="tfhb-quick-access-wrapper">
							<div class="tfhb-access-item">
								<a href="<?php echo esc_url( tfhb_utm_generator( 'https://themefic.com/docs/hydrabooking/', array( 'utm_medium' => 'dashboard_doc_link' ) ) ); ?>" target="_blank">
									<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M12.0833 1.66663H4.99992C4.55789 1.66663 4.13397 1.84222 3.82141 2.15478C3.50885 2.46734 3.33325 2.89126 3.33325 3.33329V16.6666C3.33325 17.1087 3.50885 17.5326 3.82141 17.8451C4.13397 18.1577 4.55789 18.3333 4.99992 18.3333H14.9999C15.4419 18.3333 15.8659 18.1577 16.1784 17.8451C16.491 17.5326 16.6666 17.1087 16.6666 16.6666V6.24996L12.0833 1.66663Z" stroke="#5D5676" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
									<path d="M11.6667 1.66663V6.66663H16.6667" stroke="#5D5676" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
									<path d="M13.3334 10.8334H6.66675" stroke="#5D5676" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
									<path d="M13.3334 14.1666H6.66675" stroke="#5D5676" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
									<path d="M8.33341 7.5H6.66675" stroke="#5D5676" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
									<?php echo esc_html__( 'Documentation', 'hydra-booking' ); ?>
								</a>
							</div>
							<div class="tfhb-access-item">
								<a href="<?php echo esc_url( tfhb_utm_generator( 'https://portal.themefic.com/support/', array( 'utm_medium' => 'dashboard_support_link' ) ) ); ?>" target="_blank">
									<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M2.5 9.16662H5C5.44203 9.16662 5.86595 9.34222 6.17851 9.65478C6.49107 9.96734 6.66667 10.3913 6.66667 10.8333V13.3333C6.66667 13.7753 6.49107 14.1992 6.17851 14.5118C5.86595 14.8244 5.44203 15 5 15H4.16667C3.72464 15 3.30072 14.8244 2.98816 14.5118C2.67559 14.1992 2.5 13.7753 2.5 13.3333V9.16662ZM2.5 9.16662C2.5 8.18171 2.69399 7.20644 3.0709 6.2965C3.44781 5.38656 4.00026 4.55976 4.6967 3.86332C5.39314 3.16689 6.21993 2.61444 7.12987 2.23753C8.03982 1.86062 9.01509 1.66663 10 1.66663C10.9849 1.66663 11.9602 1.86062 12.8701 2.23753C13.7801 2.61444 14.6069 3.16689 15.3033 3.86332C15.9997 4.55976 16.5522 5.38656 16.9291 6.2965C17.306 7.20644 17.5 8.18171 17.5 9.16662M17.5 9.16662V13.3333M17.5 9.16662H15C14.558 9.16662 14.134 9.34222 13.8215 9.65478C13.5089 9.96734 13.3333 10.3913 13.3333 10.8333V13.3333C13.3333 13.7753 13.5089 14.1992 13.8215 14.5118C14.134 14.8244 14.558 15 15 15H15.8333C16.2754 15 16.6993 14.8244 17.0118 14.5118C17.3244 14.1992 17.5 13.7753 17.5 13.3333M17.5 13.3333V15C17.5 15.884 17.1488 16.7319 16.5237 17.357C15.8986 17.9821 15.0507 18.3333 14.1667 18.3333H10" stroke="#5D5676" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
									<?php echo esc_html__( 'Get Support', 'hydra-booking' ); ?>
								</a>
							</div>
							<div class="tfhb-access-item">
								<a href="https://www.facebook.com/groups/hydrabooking/" target="_blank">
									<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M13.3334 17.5V15.8333C13.3334 14.9493 12.9822 14.1014 12.3571 13.4763C11.732 12.8512 10.8841 12.5 10.0001 12.5H5.00008C4.11603 12.5 3.26818 12.8512 2.64306 13.4763C2.01794 14.1014 1.66675 14.9493 1.66675 15.8333V17.5M13.3334 2.60667C14.0482 2.79197 14.6812 3.20939 15.1331 3.79339C15.5851 4.37738 15.8302 5.09491 15.8302 5.83333C15.8302 6.57176 15.5851 7.28928 15.1331 7.87328C14.6812 8.45728 14.0482 8.87469 13.3334 9.06M18.3334 17.5V15.8333C18.3329 15.0948 18.087 14.3773 17.6345 13.7936C17.1821 13.2099 16.5485 12.793 15.8334 12.6083M10.8334 5.83333C10.8334 7.67428 9.34103 9.16667 7.50008 9.16667C5.65913 9.16667 4.16675 7.67428 4.16675 5.83333C4.16675 3.99238 5.65913 2.5 7.50008 2.5C9.34103 2.5 10.8334 3.99238 10.8334 5.83333Z" stroke="#5D5676" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
									<?php echo esc_html__( 'Join our Community', 'hydra-booking' ); ?>
								</a>
							</div>
							<div class="tfhb-access-item">
								<a href="https://app.loopedin.io/hydrabooking" target="_blank">
									<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M14.1667 11.6667V17.5M5.83341 11.6667V17.5M14.1667 2.5V5M5.83341 2.5V5M8.33341 11.6667L1.91675 5.25M11.6667 5L18.0834 11.4167M6.66675 5L13.3334 11.6667M2.50008 5H17.5001C17.9603 5 18.3334 5.3731 18.3334 5.83333V10.8333C18.3334 11.2936 17.9603 11.6667 17.5001 11.6667H2.50008C2.03984 11.6667 1.66675 11.2936 1.66675 10.8333V5.83333C1.66675 5.3731 2.03984 5 2.50008 5Z" stroke="#5D5676" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
									<?php echo esc_html__( 'See our Roadmap', 'hydra-booking' ); ?>
								</a>
							</div>
							<div class="tfhb-access-item">
								<a href="https://app.loopedin.io/hydrabooking#/ideas-board" target="_blank">
									<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M12.5 11.6666C12.6667 10.8333 13.0833 10.25 13.75 9.58329C14.5833 8.83329 15 7.74996 15 6.66663C15 5.34054 14.4732 4.06877 13.5355 3.13109C12.5979 2.19341 11.3261 1.66663 10 1.66663C8.67392 1.66663 7.40215 2.19341 6.46447 3.13109C5.52678 4.06877 5 5.34054 5 6.66663C5 7.49996 5.16667 8.49996 6.25 9.58329C6.83333 10.1666 7.33333 10.8333 7.5 11.6666M7.5 15H12.5M8.33333 18.3333H11.6667" stroke="#5D5676" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
									<?php echo esc_html__( 'Request a Feature', 'hydra-booking' ); ?>
								</a>
							</div>
						</div>
					</div>
					
				</div>
			</div>
		</div>
		<?php
	}

	public function tfhb_get_sidebar_plugin_list(){

		$plugins = [
			[
				'name'       => 'UACF7',
				'slug'       => 'ultimate-addons-for-contact-form-7',
				'file_name'  => 'ultimate-addons-for-contact-form-7',
				'subtitle'   => '40+ Essential Addons for Contact Form 7',
				'image'      => 'https://ps.w.org/ultimate-addons-for-contact-form-7/assets/icon-128x128.png',
				
			],
			[
				'name'       => 'BEAF',
				'slug'       => 'beaf-before-and-after-gallery',
				'file_name'  => 'before-and-after-gallery',
				'subtitle'   => 'Ultimate Before After Image Slider & Gallery',
				'image'      => 'https://ps.w.org/beaf-before-and-after-gallery/assets/icon-128x128.png',
				
			],
			[
				'name'       => 'Tourfic',
				'slug'       => 'tourfic',
				'file_name'  => 'tourfic',
				'subtitle'   => 'Travel, Hotel Booking & Car Rental WP Plugin',
				'image'      => 'https://ps.w.org/tourfic/assets/icon-128x128.gif',
				
			],
			[
				'name'       => 'Instantio',
				'slug'       => 'instantio',
				'file_name'  => 'instantio',
				'subtitle'   => 'WooCommerce Quick & Direct Checkout',
				'image'      => 'https://ps.w.org/instantio/assets/icon-128x128.png',
			
			]
		];

		?>

		<ul>
			<?php foreach ($plugins as $plugin): 
				$plugin_path = $plugin['slug'] . '/' . $plugin['file_name'] . '.php';
				$installed = file_exists(WP_PLUGIN_DIR . '/' . $plugin_path);
				$activated = $installed && is_plugin_active($plugin_path);

				$pro_installed = false;
				$pro_activated = false;
				
				if (!empty($plugin['pro'])) {
					$pro_path = $plugin['pro']['slug'] . '/' . $plugin['pro']['file_name'] . '.php';
					$pro_installed = file_exists(WP_PLUGIN_DIR . '/' . $pro_path);
					$pro_activated = $pro_installed && is_plugin_active($pro_path);
				}

				?>

				<li class="tfhb-plugin-item <?php echo esc_attr($plugin['slug'] == 'ultimate-addons-for-contact-form-7' ? 'featured' : ''); ?>" data-plugin-slug="<?php echo esc_attr($plugin['slug']); ?>">
					<div class="tfhb-plugin-info-wrapper">
						<div class="tfhb-plugin-content">
							<div class="tfhb-plugin-image">
								<img src="<?php echo esc_url($plugin['image']); ?>" alt="<?php echo esc_attr($plugin['name']); ?>" class="<?php echo esc_attr($plugin['name'] == 'BEAF' ? 'beaf-logo' : ''); ?>" width="40" height="40">
							</div>
							<div class="tfhb-plugin-title">
								<h4><?php echo esc_html($plugin['name']); ?>
								<span class="badge free">Free</span></h4>
								<p><?php echo esc_html($plugin['subtitle']); ?></p>
								<strong></strong>
							</div>

							<div class="tfhb-plugin-btn">
								<?php if (!$installed): ?>
									<button class="tfhb-plugin-button install" data-action="install" data-plugin="<?php echo esc_attr($plugin['slug']); ?>" data-plugin_filename="<?php echo esc_attr($plugin['file_name']); ?>">
										Install 
										<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M4.66675 4.66663H11.3334V11.3333" stroke="#382673" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
										<path d="M4.66675 11.3333L11.3334 4.66663" stroke="#382673" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
										</svg>
										<span class="loader"></span>
									</button>
								<?php elseif (!$activated): ?>
									<button class="tfhb-plugin-button activate" data-action="activate" data-plugin="<?php echo esc_attr($plugin['slug']); ?>" data-plugin_filename="<?php echo esc_attr($plugin['file_name']); ?>" >
										Activate <span class="loader"></span>
									</button>
								<?php else: ?>
									<span class="tfhb-plugin-button tfhb-plugin-status active">Activated</span>
								<?php endif; ?>

								<?php if (!empty($plugin['pro'])): ?>
									<?php if (!$pro_installed): ?>
										<a href="<?php echo esc_url($plugin['pro']['url']); ?>" class="tfhb-plugin-button pro" target="_blank">Get Pro</a>
									<?php elseif (!$pro_activated): ?>
										<button class="tfhb-plugin-button activate-pro" data-action="activate" data-plugin="<?php echo esc_attr($plugin['pro']['slug']); ?>" data-plugin_filename="<?php echo esc_attr($plugin['pro']['file_name']); ?>">
											Activate Pro <span class="loader"></span>
										</button>
									<?php else: ?>
										<span class="tfhb-plugin-button tfhb-plugin-status active-pro">Pro Activated</span>
									<?php endif; ?>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</li>

			<?php endforeach; ?>

		</ul>

		<?php 
	}
}
