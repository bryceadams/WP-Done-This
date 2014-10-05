<?php
/**
 * WP Done This - API Methods
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
 * WP_Done_This_API Class
 *
 * @package  WP Done This
 * @author   Bryce <bryce@bryce.se>
 * @since    1.0.0
 */

if ( ! class_exists( 'WP_Done_This_API' ) ) {

    class WP_Done_This_API {

        protected static $instance = null;

        public function __construct() {

        	// Do Stuff Later

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
         * Authentication test for API Key
         *
         * @package WP Done This
         * @author  Bryce <bryce@bryce.se>
         * @since   1.0.0
         * @return 	bool
         */

		public function authenticate( $api_key, $team_slug ) {

			$fields = array(
				'raw_text'	=> '',
				'team'		=> $team_slug,
			);

			$team = $team_slug;

			$url = 'https://idonethis.com/api/v0.1/noop/?team=' . $team;

			$response = wp_remote_post( $url, array(
				'method' => 'GET',
				'user-agent' => 'WP Done This / 1.0',
			    'headers'=> array(
			      'Content-Type' => 'application/json',
			      'Authorization' => 'Token ' . $api_key,
			    ),
				'body' => json_encode( $fields ),
			    )
			);

			if ( is_wp_error( $response ) ) {

			   	$error_message = $response->get_error_message();
			   	$return = false;

			} else {

			   	$code = $response['response']['code'];

			   	if ( $code == 200 ) {
			   		$return = true;
			   	} else {
			   		$return = false;
			   	}

			}

			return $return;

		}


		/**
         * Owner of API Key
         *
         * @package WP Done This
         * @author  Bryce <bryce@bryce.se>
         * @since   1.0.0
         * @return 	bool
         */

		public function owner( $api_key, $team_slug ) {

			$fields = array(
				'raw_text'	=> '',
				'team'		=> $team_slug,
			);

			$team = $team_slug;

			$url = 'https://idonethis.com/api/v0.1/noop/?team=' . $team;

			$response = wp_remote_post( $url, array(
				'method' => 'GET',
				'user-agent' => 'WP Done This / 1.0',
			    'headers'=> array(
			      'Content-Type' => 'application/json',
			      'Authorization' => 'Token ' . $api_key,
			    ),
				'body' => json_encode( $fields ),
			    )
			);

			if ( is_wp_error( $response ) ) {

			   	$error_message = $response->get_error_message();
			   	return false;

			} else {

			   	$content = json_decode( $response['body'] );

			   	$username = $content->user;

			   	return $username;

			}

		}


		/**
         * Send done to iDoneThis
         *
         * @package WP Done This
         * @author  Bryce <bryce@bryce.se>
         * @since   1.0.0
         */

		public function send( $api_key, $team_slug, $message ) {

			$fields = array(
				'raw_text'	=> $message,
				'team'		=> $team_slug,
			);

			$team = $team_slug;

			$url = 'https://idonethis.com/api/v0.1/dones/?team=' . $team;

			$response = wp_remote_post( $url, array(
				'method' => 'POST',
				'user-agent' => 'WP Done This / 1.0',
			    'headers'=> array(
			      'Content-Type' => 'application/json',
			      'Authorization' => 'Token ' . $api_key,
			    ),
				'body' => json_encode( $fields ),
			    )
			);

			if ( is_wp_error( $response ) ) {
			
			   $error_message = $response->get_error_message();
			   $return = false;
			
			} else {
			   	
			   	$result = $response['response']['message'];
			   	
			   	if ( $result == 'Created' ) {
			   		$return = true;
			   	} else {
			   		$return = false;
			   	}

			}

			return $return;
		}


		/**
         * Get dones from iDoneThis
         *
         * @package WP Done This
         * @author  Bryce <bryce@bryce.se>
         * @since   1.0.0
         */

		public function dones( $api_key, $team_slug, $date, $owner, $ajax = false ) {

			$fields = array(
				'raw_text'	=> '',
				'team'		=> $team_slug,
			);

			$team = $team_slug;

			$url = 'https://idonethis.com/api/v0.1/dones/?team=' . $team . '&done_date=' . $date . '&order_by=-created&owner=' . $owner;

			$response = wp_remote_post( $url, array(
				'method' => 'GET',
				'user-agent' => 'WP Done This / 1.0',
			    'headers'=> array(
			      'Content-Type' => 'application/json',
			      'Authorization' => 'Token ' . $api_key,
			    ),
				'body' => json_encode( $fields ),
			    )
			);

			if ( is_wp_error( $response ) ) {
			
			   $error_message = $response->get_error_message();
			   return false;
			
			} else {

			   	$content = json_decode( $response['body'] );

			   	$results = $content->results;

			   	if ( $results ) { ?>

			   		<ul <?php if ( $ajax == true ) { echo 'class="ajax"'; } ?>>
			   	
				   	<?php foreach ( $results as $result ) { ?>
					        <li>
					        	<?php echo $result->raw_text; ?> <a href="http://idonethis.com/done/<?php echo $result->id; ?>">#</a>
					        	<span class="date">
					        	 - <?php echo wpdonethis_timeago( $result->created ) . ' ' . __( 'ago', 'wpdonethis' ); ?>
					        	</span>
					        </li>
					<?php } ?>

					</ul>

			   	<?php }

			}

		}

	}

}