<?php
	// Title
	if ( isset( $instance[ 'title' ] ) ) {
		$title = $instance[ 'title' ];
	} else {
		$title = __( 'What have you done today?', 'wpdonethis' );
	}

	// Placeholder Text
	if ( isset( $instance[ 'placeholder' ] ) ) {
		$placeholder = $instance[ 'placeholder' ];
	} else {
		$placeholder = __( 'Enter a done and press enter!', 'wpdonethis' );
	}
?>
<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
	<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
</p>

<p>
	<label for="<?php echo $this->get_field_id( 'placeholder' ); ?>"><?php _e( 'Placeholder Text:' ); ?></label> 
	<input class="widefat" id="<?php echo $this->get_field_id( 'placeholder' ); ?>" name="<?php echo $this->get_field_name( 'placeholder' ); ?>" type="text" value="<?php echo esc_attr( $placeholder ); ?>">
</p>