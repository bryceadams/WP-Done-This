<?php

// Before Widget
echo $args['before_widget'];
	
	// Output Widget Title
	if ( ! empty( $instance['title'] ) ) {
		echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
	}

	// Placeholder Text
	if ( ! empty( $instance['placeholder'] ) ) {
		$placeholder = apply_filters( 'wpdonethis_placeholder', $instance['placeholder'] );
	} else {
		$placeholder = __( 'Enter a done and press enter!', 'wpdonethis' );
	}

	?>

	<form id="wpdonethis-form" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>" method="POST">
	    <p>
	    	<input type="text" name="wpdonethis_done" id="wpdonethis-done" size="40" placeholder="<?php echo $placeholder ?>" />
	  	</p>
	    <p>
	    	<input type="submit" name="wpdonethis_done_submitted" id="wpdonethis-submit" value="<?php _e( 'Send', 'wpdonethis' ); ?>" style="display:none;">
	    	<img src="<?php echo admin_url( '/images/wpspin_light.gif' ); ?>" class="waiting" id="wpdonethis-loading" style="display:none;"/>
	    </p>
    </form>

    <div id="wpdonethis-results">
    	<?php

    		$wp_done_this_api 	= new WP_Done_This_API;

			$current_user 	= get_current_user_id();
			$api_key 		= get_the_author_meta( 'wpdonethis_api_key', $current_user );
			$team_slug 		= get_the_author_meta( 'wpdonethis_team_slug', $current_user );

			$date 	= apply_filters( 'wpdonethis_date', 'today' );

			// Get owner data from the owner transient
			if ( get_transient( 'wpdonethis_owner' ) ) {
				$owner = get_transient( 'wpdonethis_owner' );
			} else {
				$owner = '';
			}

			// Check if transient for dones data exists, if not - set it!
			if ( get_transient( 'wpdonethis_dones' ) ) {
				$dones = get_transient( 'wpdonethis_dones' );
			} else {
				$dones = $wp_done_this_api->dones( $api_key, $team_slug, $date, $owner );
			    set_transient( 'wpdonethis_dones', $dones, 12 * HOUR_IN_SECONDS ); // save for half a day
			}

			echo $dones;

		?>
    </div>

<?php	

// After Widget
echo $args['after_widget'];

?>