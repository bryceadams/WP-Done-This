<?php
/**
 * WP Done This - Widget
 *
 * @package   WP Done This
 * @author    Bryce <bryce@bryce.se>
 * @license   GPL-2.0+
 * @link      http://captaintheme.com
 * @copyright 2014 Bryce
 * @since     1.0.0
 */

// Prevent direct file access
if ( ! defined ( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Done_This_Widget' ) ) {

	class WP_Done_This_Widget extends WP_Widget {

		protected $widget_slug = 'wpdonethis';

		public function __construct() {

			parent::__construct(
				$this->get_widget_slug(),
				__( 'WP Done This', $this->get_widget_slug() ),
				array(
					'classname'  => $this->get_widget_slug().'-widget',
					'description' => __( 'Adds form for users to submit dones to iDoneThis and shows their previous dones for the day!', $this->get_widget_slug() )
				)
			);

			// Refreshing the widget's cached output with each new post
			add_action( 'save_post',    array( $this, 'flush_widget_cache' ) );
			add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
			add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );

		} // end constructor

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
	     * Return the widget slug.
	     *
	     * @since    1.0.0
	     *
	     * @return    Plugin slug variable.
	     */
	    public function get_widget_slug() {

	        return $this->widget_slug;

	    }

		/*--------------------------------------------------*/
		/* Widget API Functions
		/*--------------------------------------------------*/

		/**
		 * Outputs the content of the widget.
		 *
		 * @param array args  The array of form elements
		 * @param array instance The current instance of the widget
		 */
		public function widget( $args, $instance ) {

			// Check if there is a cached output
			$cache = wp_cache_get( $this->get_widget_slug(), 'widget' );

			if ( !is_array( $cache ) ) {
				$cache = array();
			}

			if ( ! isset ( $args['widget_id'] ) ) {
				$args['widget_id'] = $this->id;
			}

			if ( isset ( $cache[ $args['widget_id'] ] ) ) {
				return print $cache[ $args['widget_id'] ];
			}
			
			extract( $args, EXTR_SKIP );

			$widget_string = $before_widget;

			ob_start();
			
			// Only show to logged in users
			if ( is_user_logged_in()  ) {

				// Vars
				$wp_done_this_api = new WP_Done_This_API;
				$current_user 	= get_current_user_id();
				$api_key 		= get_the_author_meta( 'wpdonethis_api_key', $current_user );
				$team_slug 		= get_the_author_meta( 'wpdonethis_team_slug', $current_user );

				// Check if transient for authenticate data exists, if not - set it!
				if ( false === ( $authenticate = get_transient( 'wpdonethis_authenticate' ) ) ) {

					$authenticate = $wp_done_this_api->authenticate( $api_key, $team_slug );
				    set_transient( 'wpdonethis_authenticate', $authenticate, 30 * DAY_IN_SECONDS ); // save for a month

				}

				// Only show to those with valid info
	        	if ( $authenticate == 1 ) {
					include( plugin_dir_path( __FILE__ ) . '../views/widget.php' );
				} else {
					$warning = sprintf( __( 'Add your iDoneThis API Key & Team Slug in your %s', 'wpdonethis' ), '<a href="' . admin_url( '/profile.php' ) . '">' . __( 'User Profile Settings', 'wpdonethis' ) . '</a>' );
					echo apply_filters( 'wpdonethis_user_invalid', $warning );
				}
			}

			$widget_string .= ob_get_clean();
			$widget_string .= $after_widget;

			$cache[ $args['widget_id'] ] = $widget_string;

			wp_cache_set( $this->get_widget_slug(), $cache, 'widget' );

			print $widget_string;

		}
		
		
		public function flush_widget_cache() {
	    	wp_cache_delete( $this->get_widget_slug(), 'widget' );
		}

		/**
		 * Processes the widget's options to be saved.
		 *
		 * @param array new_instance The new instance of values to be generated via the update.
		 * @param array old_instance The previous instance of values before the update.
		 */
		public function update( $new_instance, $old_instance ) {

			$instance = $old_instance;

			$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

			$instance['placeholder'] = ( ! empty( $new_instance['placeholder'] ) ) ? strip_tags( $new_instance['placeholder'] ) : '';

			return $instance;

		}

		/**
		 * Generates the administration form for the widget.
		 *
		 * @param array instance The array of keys and values for the widget.
		 */
		public function form( $instance ) {

			// Display the admin form
			include( plugin_dir_path(__FILE__) . '../views/admin.php' );

		}

	}

}

add_action( 'widgets_init', create_function( '', 'register_widget("WP_Done_This_Widget");' ) );