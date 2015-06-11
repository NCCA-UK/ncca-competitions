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
	
	echo $days_remaining . ' days, ' . $hours_remaining . ' hours';
	
	$output = ob_get_clean();
	return $output;
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
	
	$output = ob_get_clean();
	return $output;
}


/**
 * Add shortcode to display competition winner
 */
add_shortcode( 'winner', 'ncca_competition_winner' );
function ncca_competition_winner( $atts ) {
	ob_start();

	// Attributes
	extract( shortcode_atts(
		array(
			'form_id' => ''
		), $atts )
	);

	// Output
	global $frm_entry, $frm_entry_meta;
	$entries = $frm_entry->getAll( array( 'it.form_id' => $form_id ) );

	$entry_ids = array();
	foreach( $entries as $entry ) {
		$entry_ids[] = $entry->id;
	}
	$winner_id = array_rand( $entry_ids );
	
	$fb_name = FrmEntryMeta::get_entry_meta_by_field( $entry_ids[$winner_id], 668 );
	$full_name = FrmEntryMeta::get_entry_meta_by_field( $entry_ids[$winner_id], 643 );

	if( $fb_name != '' ) {
		$winner_name = $fb_name;
	} else {
		$winner_name = $full_name;
	}

	//echo 'Winner ID: ' . $entry_ids[$winner_id];
	echo $winner_name;

	$output = ob_get_clean();
	return $output;
}

?>
