<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class MailChimp_Subscriber_Count_Admin {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( $this, 'settings' ) );
		add_action( 'update_option_mailchimp_subscriber_count', array( $this, 'delete_transient' ), 10, 2 );
	}

	/**
	 * Delete transient when the admin settings are saved
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function delete_transient( $old_value, $new_value ) {
		delete_transient( 'mailchimp_subscriber_count' );
	}

	/**
	 * Register menu
	 *
	 * @since 1.0.0
	 */
	public function register_menu() {

		add_options_page(
			__( 'MailChimp Subscriber Count', 'mailchimp-subscriber-count' ), // page title
			__( 'MailChimp Subscriber Count', 'mailchimp-subscriber-count' ), // menu title
			'manage_options', // capability
			'mailchimp-subscriber-count', // menu slug
			array( $this, 'admin_page' ) // function
		);

	}

	/**
	 * Admin page
	 *
	 * @since 1.0.0
	 */
	public function admin_page() { ?>
    <div class="wrap">
    	 <?php screen_icon( 'plugins' ); ?>
        <h2><?php _e( 'MailChimp Subscriber Count', 'mailchimp-subscriber-count' ); ?></h2>

        <form action="options.php" method="POST">
            <?php
	            settings_fields( 'mailchimp-subscriber-count' ); // settings group name
	            do_settings_sections( 'mailchimp-subscriber-count' );
            ?>

            <?php submit_button(); ?>
        </form>

    </div>
	<?php }

	/**
	 * Default values
	 *
	 * @since  1.0.0
	 */
	public function default_options() {

		$defaults = array(
			'mc_api_key' => '',
			'mc_list'    => ''
		);

		return apply_filters( 'mailchimp_subscriber_count_default_options', $defaults );

	}

	/**
	 * Settings
	 *
	 * @since  1.0.0
	 */
	public function settings() {

		if ( false == get_option( 'mailchimp_subscriber_count' ) ) {
			add_option( 'mailchimp_subscriber_count', $this->default_options() );
		}

		add_settings_section(
			'mc_subscriber_count', // $id
			'', // Title of the section
			'', // Function that fills the section with the desired content
			'mailchimp-subscriber-count' // The menu page on which to display this section
		);

		add_settings_field(
			'mc_api_key',
			__( 'MailChimp API Key', 'mailchimp-subscriber-count' ),
			array( $this, 'callback_input' ),
			'mailchimp-subscriber-count',
			'mc_subscriber_count',
			array(
				'name'        => 'mc_subscriber_count_api_key',
				'id'          => 'mc-subscriber-count-api-key',
				'description' => __( 'Enter your MailChimp API Key. This can be found from the <strong>Extras &rarr; API keys</strong> of your account page.', 'mailchimp-subscriber-count' )
			)
		);

		add_settings_field(
			'mc_list', // ID
			__( 'MailChimp List ID', 'mailchimp-subscriber-count' ), // title
			array( $this, 'callback_input' ), // callback
			'mailchimp-subscriber-count', //page
			'mc_subscriber_count', // section
			array(
				'name'        => 'mc_subscriber_count_list_id',
				'id'          => 'mc-subscriber-count-list-id',
				'description' => __( 'Enter the MailChimp list ID. This can be found from <strong>Settings &rarr; List name and defaults</strong> while viewing a list.', 'mailchimp-subscriber-count' )
			)
		);


		register_setting(
			'mailchimp-subscriber-count', // settings group name. Must match the group name in settings_fields()
			'mailchimp_subscriber_count', // name of an option to sanitize and save
			array( $this, 'sanitize' ) // sanitize callback
		);

	}

	/**
	 * Input field callback
	 *
	 * @since  1.0.0
	 */
	public function callback_input( $args ) {

		$options = get_option( 'mailchimp_subscriber_count' );
		$value   = isset( $options[$args['name']] ) ? $options[$args['name']] : '';
	?>
		<input type="text" class="regular-text" id="<?php echo $args['id']; ?>" name="mailchimp_subscriber_count[<?php echo $args['name']; ?>]" value="<?php echo $value; ?>" />

		<?php if ( isset( $args['description'] ) ) : ?>
			<p class="description"><?php echo $args['description']; ?></p>
		<?php endif; ?>
		<?php

	}

	/**
	 * Sanitization callback
	 *
	 * @since  1.0.0
	 */
	public function sanitize( $input ) {

		// Create our array for storing the validated options
		$output = array();

		// Loop through each of the incoming options
		foreach ( $input as $key => $value ) {

			// Check to see if the current option has a value. If so, process it.
			if ( isset( $input[$key] ) ) {
				$output[$key] = $input[ $key ];
			}

		}

		// Return the array processing any additional functions filtered by this action
		return apply_filters( 'mailchimp_subscriber_count_sanitize', $output, $input );

	}

}

$mailchimp_subscriber_count_admin = new MailChimp_Subscriber_Count_Admin;
