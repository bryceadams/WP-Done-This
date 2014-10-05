<?php
/**
 * WP Done This - Main Class
 *
 * @package   WP Done This
 * @author    Bryce <bryce@bryce.se>
 * @license   GPL-2.0+
 * @link      http://captaintheme.com
 * @copyright 2014 Bryce
 * @since     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * WP_Done_This_Init Class
 *
 * @package  WP Done This
 * @author   Bryce <bryce@bryce.se>
 * @since    1.0.0
 */

if ( ! class_exists( 'WP_Done_This_Init' ) ) {

    class WP_Done_This_Init {

        protected static $instance = null;

        public function __construct() {

        	// Add user profile fields
			add_action( 'show_user_profile', array( $this, 'show_api_field' ) );
			add_action( 'edit_user_profile', array( $this, 'show_api_field' ) );

			// Save user profile fields
			add_action( 'personal_options_update', array( $this, 'save_api_field' ) );
			add_action( 'edit_user_profile_update', array( $this, 'save_api_field' ) );

			// Enqueue Scripts
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			// Process Ajax
			add_action( 'wp_ajax_wpdonethis_get_results', array( $this, 'process_ajax' ) );

        }

        /**
         * Start the Class when called
         *
         * @package WP Done This
         * @author  Bryce <bryce@bryce.se>
         * @since   1.0.0
         */

        public static function get_instance() {

          // If the single instance hasn't been set, set it now.
          if ( null == self::$instance ) {
            self::$instance = new self;
          }

          return self::$instance;

        }


        /**
         * Enqueue Scripts etc. needed
         *
         * @package WP Done This
         * @author  Bryce <bryce@bryce.se>
         * @since   1.0.0
         */

        public function enqueue_scripts() {

        	// Enqueue jQuery
        	wp_enqueue_script( 'jquery' );

        	// Register Scripts
        	wp_register_script( 'wpdonethis-ajax', plugins_url( 'assets/js/ajax.js', dirname( __FILE__ ) ), array( 'jquery' ) );
        	wp_register_style( 'wpdonethis-css', plugins_url( 'assets/css/wpdonethis.css', dirname( __FILE__ ) ) );

        	// Only load the ajax when in front-end
        	if ( ! is_admin() ) {

        		// Ajax / JS Stuff
        		wp_enqueue_script('wpdonethis-ajax' );
				wp_localize_script('wpdonethis-ajax', 'wpdonethis_vars', array(
						'wpdonethis_nonce'	=> wp_create_nonce( 'wpdonethis-nonce' ),
					)
				);

				// Styles
				wp_enqueue_style( 'wpdonethis-css' );

			}

        }


        /**
         * Process AJAX Request
         *
         * @package WP Done This
         * @author  Bryce <bryce@bryce.se>
         * @since   1.0.0
         * @todo 	Should probably do a check for if the form was submitted, but this should still be pretty safe
         */


        public function process_ajax() {
	
			if( !isset( $_POST['wpdonethis_nonce'] ) || !wp_verify_nonce($_POST['wpdonethis_nonce'], 'wpdonethis-nonce') ) {
				die( 'Permissions check failed' );	
			}

			$wp_done_this_api 	= new WP_Done_This_API;

			$message			= sanitize_text_field( $_POST['wpdonethis_done'] );
        			
        	$current_user 		= get_current_user_id();

        	$api_key 			= get_the_author_meta( 'wpdonethis_api_key', $current_user );
        	$team_slug 			= get_the_author_meta( 'wpdonethis_team_slug', $current_user );

        	$date 				= 'today';
			$owner				= $wp_done_this_api->owner( $api_key, $team_slug );

        	$send_done = $wp_done_this_api->send( $api_key, $team_slug, $message );

        	if ( $send_done == 1 ) {
				// Can do something if done submitted was successful
			} else {
				_e( 'Something went wrong!', 'wpdonethis' );
			}

			// Delete dones transient that is first displayed in widget.php
			if ( get_transient( 'wpdonethis_dones' ) ) {
				delete_transient( 'wpdonethis_dones' );
			}

			echo $wp_done_this_api->dones( $api_key, $team_slug, $date, $owner, true );

			die();
		}


        /**
         * Add Field to User Profile Settings
         *
         * @package WP Done This
         * @author  Bryce <bryce@bryce.se>
         * @since   1.0.0
         */

		public function show_api_field( $user ) { ?>

			<h3><?php _e( 'WP Done This', 'wpdonethis' ); ?></h3>

			<table class="form-table">

				<tr>
					<th><label for="wpdonethis_api_key"><?php _e( 'iDoneThis API Key', 'wpdonethis' ); ?></label></th>

					<td>
						<input type="text" name="wpdonethis_api_key" id="wpdonethis_api_key" value="<?php echo esc_attr( get_the_author_meta( 'wpdonethis_api_key', $user->ID ) ); ?>" class="regular-text" />
						<?php
							$wp_done_this_api = new WP_Done_This_API;
							$current_user = get_current_user_id();

				        	$api_key = get_the_author_meta( 'wpdonethis_api_key', $current_user );
				        	$team_slug = get_the_author_meta( 'wpdonethis_team_slug', $current_user );

				        	// Check if transient for authenticate data exists, if not - set it!
							if ( false === ( $authenticate = get_transient( 'wpdonethis_authenticate' ) ) ) {

								$authenticate = $wp_done_this_api->authenticate( $api_key, $team_slug );
							    set_transient( 'wpdonethis_authenticate', $authenticate, 30 * DAY_IN_SECONDS ); // save for a month

							}

							// Transient for API owner
							if ( false === ( $owner = get_transient( 'wpdonethis_owner' ) ) ) {

								$owner	= $wp_done_this_api->owner( $api_key, $team_slug );
							    set_transient( 'wpdonethis_owner', $owner, 30 * DAY_IN_SECONDS ); // save for a month

							}
								
				        	if ( $authenticate == 1 ) {
				        		echo '<span style="color:green;font-weight:bold;">';
				        		_e( 'Valid for ', 'wpdonethis' );
				        		echo $owner;
				        		echo '</span>';
				        	} else {
				        		echo '<span style="color:red;font-weight:bold;">' . __( 'Invalid', 'wpdonethis' ) . '</span>';
				        	}
				        ?>
						<br />
						<span class="description">
							<?php echo sprintf( __( 'Please enter the API Key found in your %s account.', 'wpdonethis' ), '<a href="https://idonethis.com/api/get_token/" target="_blank">iDoneThis</a>' ); ?>
						</span>
					</td>
				</tr>

				<tr>
					<th><label for="wpdonethis_team_slug"><?php _e( 'iDoneThis Team Slug', 'wpdonethis' ); ?></label></th>

					<td>
						<input type="text" name="wpdonethis_team_slug" id="wpdonethis_team_slug" value="<?php echo esc_attr( get_the_author_meta( 'wpdonethis_team_slug', $user->ID ) ); ?>" class="regular-text" /><br />
						<span class="description">
							<?php _e( 'The slug of the team you\'d like to post \'dones\' for.', 'wpdonethis' ); ?>
						</span>
					</td>
				</tr>

			</table>
		<?php }


		/**
         * Save Field in User Profile Settings
         *
         * @package WP Done This
         * @author  Bryce <bryce@bryce.se>
         * @since   1.0.0
         */

		public function save_api_field( $user_id ) {

			if ( ! current_user_can( 'edit_user', $user_id ) ) {
				return false;
			}

			update_usermeta( $user_id, 'wpdonethis_api_key', sanitize_text_field( $_POST['wpdonethis_api_key'] ) );
			update_usermeta( $user_id, 'wpdonethis_team_slug', sanitize_text_field( $_POST['wpdonethis_team_slug'] ) );

			// Delete auth transient on settings save
			if ( get_transient( 'wpdonethis_authenticate' ) ) {
				delete_transient( 'wpdonethis_authenticate' );
			}

			// Delete owner transient on settings save
			if ( get_transient( 'wpdonethis_owner' ) ) {
				delete_transient( 'wpdonethis_owner' );
			}

		}


		/**
         * Plugin Textdomain / i18n Stuff
         *
         * @package WP Done This
         * @author  Bryce <bryce@bryce.se>
         * @since   1.0.0
         */

		public function load_plugin_textdomain() {

	  		$domain = 'wpdonethis';
	  		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	  		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
	  		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	  	}

	}

}