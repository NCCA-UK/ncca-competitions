<?php
/*
Plugin Name: NCCA Competitions
Plugin URI: https://wptechcentre.com/
Description: Declares a plugin that adds support for competitions.
Version: 1.0
Author: Tom Frearson
Author URI: https://wptechcentre.com/
License: GPLv2
*/
/**
 * Add countdown timer shortcode
 */
add_shortcode( 'countdown_timer', 'ncca_countdown_timer_shortcode' );
function ncca_countdown_timer_shortcode( $atts ) {
	ob_start();

	// Attributes
	extract( shortcode_atts(
		array(
			'date' => 'December 31, 2015 12:00 PM'
		), $atts )
	);

	// Output
	$date = strtotime( $date );
	$remaining = $date - time();
	$days_remaining = floor( $remaining / 86400 );
	$hours_remaining = floor( ( $remaining % 86400) / 3600 );
	
	// if less than zero, set to zero
	$days_remaining = max(0, $days_remaining);
	$hours_remaining = max(0, $hours_remaining);

	echo '<style>.pagetitle{visibility:hidden;}</style>';
	echo $days_remaining . ' days, ' . $hours_remaining . ' hours';

	$time_remaining = ob_get_clean();

	return $time_remaining;
}


/**
 * Add how to enter shortcode
 */
add_shortcode( 'how_to_enter', 'ncca_how_to_enter_shortcode' );
function ncca_how_to_enter_shortcode() {
	$current_user = wp_get_current_user();
	
	ob_start();

	// Output
	if( is_user_logged_in() ) {
		echo '<p>Hi ' . $current_user->user_firstname . ', as you\'re logged in, you can submit your entry using the button below.</p>';
	} else {
		echo '<p>Submit your entry using the form below or login with Facebook.</p>';
	}
	
	$how_to_enter = ob_get_clean();
	return $how_to_enter;
}


/**
 * Competition closed - hide entry form and display message
 */
function ncca_competition_closed( $content ) {

	$time_remaining = do_shortcode( '[countdown_timer date="July 31, 2015 12:00 PM"]' ); // how to get this dynamically?

	if( is_page( 'prize-draw' ) && $time_remaining = '0 days, 0 hours' ) {
		$content .= '<style>.frm_forms{display:none}</style>';

		return $content;
	}
	
	elseif( is_page( 'prize-draw' ) && $time_remaining != '0 days, 0 hours' ) {
		$content .= '<style>.competition-closed{display:none}</style>';

		return $content;
	}
}
add_filter( 'the_content', 'ncca_competition_closed' );


/**
 * Add dashboard widget to select competition winner
 */
function ncca_competition_dashboard_widget() {
	if( current_user_can( 'ncca_manager' ) || current_user_can( 'manage_options' ) ) {
		wp_add_dashboard_widget(
			'ncca_competition_winner',
			'Competition Winner',
			'ncca_competition_winner'
		);
	}
}
add_action( 'wp_dashboard_setup', 'ncca_competition_dashboard_widget' );


/**
 * Select competition winner based on form ID
 */
function ncca_competition_winner() {
	global $frm_entry, $frm_entry_meta;
	if( isset( $_POST['form_id'] ) ) {
		$form_id = $_POST['form_id'];
	} else {
		$form_id = '';
	}
	$entries = $frm_entry->getAll( array( 'it.form_id' => $form_id ) );

	$entry_ids = array();
	foreach( $entries as $entry ) {
		$entry_ids[] = $entry->id;
	}
	$winner_id = array_rand( $entry_ids );
	
	/* Form specific data */
	// Prize Draw
	if( $form_id == 24 ) {
		$winner_name = FrmEntryMeta::get_entry_meta_by_field( $entry_ids[$winner_id], 643 );
	}

	if( empty( $winner_name ) ) {
		echo '<p>Please enter a valid competition form ID to pick a winner.</p>';
	}
?>
	<form action="" method="post">
		<p><label>Form ID: </label><input style="padding: 4px;" type="number" name="form_id" min="1" max="999" required><input type="submit" name="submit" value="Pick a winner"></p>
		<p><label>Winner: </label><input style="padding: 4px; margin-left: 6px;" type="text" name="winner" value="<?php echo $winner_name; ?>" disabled></p>
	</form>
<?php
}

?>
