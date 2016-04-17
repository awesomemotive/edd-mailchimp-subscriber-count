<?php
/**
 * Plugin Name: MailChimp Subscriber Count
 * Plugin URI: https://easydigitaldownloads.com
 * Description: Display your MailChimp subscriber count
 * Author: Easy Digital Downloads
 * Author URI: https://easydigitaldownloads.com
 * Version: 1.0.0
 * Text Domain: mailchimp-subscriber-count
 * Domain Path: languages
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'MailChimp_Subscriber_Count' ) ) {

	final class MailChimp_Subscriber_Count {

		/**
		 * Holds the instance
		 *
		 * Ensures that only one instance of MailChimp_Subscriber_Count exists in memory at any one
		 * time and it also prevents needing to define globals all over the place.
		 *
		 * TL;DR This is a static property property that holds the singleton instance.
		 *
		 * @var object
		 * @static
		 * @since 1.0.0
		 */
		private static $instance;

		/**
		 * The version number
		 *
		 * @since 1.0.0
		 */
		private $version = '1.0.0';

		/**
		 * Main MailChimp_Subscriber_Count Instance
		 *
		 * Insures that only one instance of MailChimp_Subscriber_Count exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @since 1.0.0
		 * @static
		 * @static var array $instance
		 * @return The one true MailChimp_Subscriber_Count
		 */
		public static function instance() {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof MailChimp_Subscriber_Count ) ) {
				self::$instance = new MailChimp_Subscriber_Count;
				self::$instance->setup_constants();
				self::$instance->includes();
				self::$instance->hooks();
			}

			return self::$instance;
		}

		/**
		 * Setup plugin constants
		 *
		 * @access private
		 * @since 1.0.0
		 * @return void
		 */
		private function setup_constants() {

			// Plugin Folder Path
			if ( ! defined( 'MC_SC_PLUGIN_DIR' ) ) {
				define( 'MC_SC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			}

		}

		/**
		 * API key
		 */
		public function api_key() {

			$options = get_option( 'mailchimp_subscriber_count' );
			$api_key = $options['mc_subscriber_count_api_key'];

			if ( $api_key ) {
				return $api_key;
			}

			return false;

		}

		/**
		 * List ID
		 */
		public function list_id() {

			$options = get_option( 'mailchimp_subscriber_count' );
			$list_id = $options['mc_subscriber_count_list_id'];

			if ( $list_id ) {
				return $list_id;
			}

			return false;

		}

		/**
		 * MailChimp API request
		 */
		private function mailchimp_request() {

			$api_key = $this->api_key();

			if ( ! $api_key ) {
				return;
			}

			$data_center = explode( '-', $api_key );
			$data_center = $data_center[1];

			$request = 'https://' . $data_center . '.api.mailchimp.com/3.0/lists/' . $this->list_id();

			// set arguments
			$args = array(
					'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( 'x' . ':' . $api_key )
				)
			);

			$request = wp_remote_get( $request, $args );

			if ( is_wp_error( $request ) ) {
				return false; // Bail early
			}

			$body = wp_remote_retrieve_body( $request );
			$data = json_decode( $body );

			if ( false === ( get_transient( 'mailchimp_subscriber_count' ) ) ) {
				set_transient( 'mailchimp_subscriber_count', (int) $data->stats->member_count, 60*60*24*3 ); // 3 days
			}

			return get_transient( 'mailchimp_subscriber_count' );
		}

		/**
		 * Get the subscriber count
		 *
		 * @since 1.0.0
		 * @return int subscriber count
		 */
		public function subscriber_count() {
			return number_format( $this->mailchimp_request() );
		}

		/**
		 * Include necessary files
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
		 */
		private function includes() {

			if ( is_admin() ) {
				require_once MC_SC_PLUGIN_DIR . 'includes/admin.php';
			}

		}

		/**
		 * Hooks
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function hooks() {
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'settings_link' ), 10, 2 );
		}

		/**
		 * Plugin settings link
		 *
		 * @since 1.0.0
		*/
		public function settings_link( $links ) {
			$plugin_links = array(
				'<a href="' . admin_url( 'options-general.php?page=mailchimp-subscriber-count' ) . '">' . __( 'Settings', 'mailchimp-subscriber-count' ) . '</a>',
			);

			return array_merge( $plugin_links, $links );
		}

	}

	/**
	 * The main function responsible for returning the one true MailChimp_Subscriber_Count
	 * Instance to functions everywhere.
	 *
	 * Use this function like you would a global variable, except without needing
	 * to declare the global.
	 *
	 * Example: <?php $mc_subscriber_count = mailchimp_subscriber_count(); ?>
	 *
	 * @since 1.0.0
	 * @return object The one true MailChimp_Subscriber_Count Instance
	 */
	function mailchimp_subscriber_count() {
	    return MailChimp_Subscriber_Count::instance();
	}
	add_action( 'plugins_loaded', 'mailchimp_subscriber_count', 100 );

}
