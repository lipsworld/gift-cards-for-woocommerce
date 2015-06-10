<?php
/**
 * Helper Functions
 *
 * @package     WPR\PluginName\Functions
 * @since       1.0.0
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


function wpr_update_database() {

	$loop = new WP_Query( array( 'post_type' => 'rp_shop_giftcard', 'posts_per_page' => -1 ) );

	while ( $loop->have_posts() ) : $loop->the_post();

		$id = get_the_id();

		$to = get_post_meta( $id, 'rpgc_to', true );
	    if( isset( $to ) ) {
	        $giftInfo['to'] = $to;
	        delete_post_meta($id, 'rpgc_to' );
	    }

	    $toEmail = get_post_meta( $id, 'rpgc_email_to', true );
	    if( isset( $toEmail ) ) {
	        $giftInfo['toEmail'] = $toEmail;
	        delete_post_meta($id, 'rpgc_email_to' );
	    }

	    $from = get_post_meta( $id, 'rpgc_from', true );
	    if( isset( $to ) ) {
	        $giftInfo['from'] = $from;
	        delete_post_meta($id, 'rpgc_from' );
	    }

	    $fromEmail = get_post_meta( $id, 'rpgc_email_from', true );
	    if( isset( $to ) ) {
	        $giftInfo['fromEmail'] = $fromEmail;
	        delete_post_meta($id, 'rpgc_email_from' );
	    }

	    $amount = get_post_meta( $id, 'rpgc_amount', true );
	    if( isset( $amount ) ) {
	        $giftInfo['amount'] = (float) $amount;
	        delete_post_meta($id, 'rpgc_amount' );
	    }

	    $balance = get_post_meta( $id, 'rpgc_balance', true );
	    if( isset( $balance ) ) {
	        $giftInfo['balance'] = (float) $balance;
	        delete_post_meta($id, 'rpgc_balance' );
	    }

	    $note = get_post_meta( $id, 'rpgc_note', true );
	    if( isset( $note ) ) {
	        $giftInfo['note'] = $note;
	        delete_post_meta($id, 'rpgc_note' );
	    }

	    $expire = get_post_meta( $id, 'rpgc_expiry_date', true );
	    if( isset( $expire ) ) {
	        $giftInfo['expiry_date'] = $expire;
	        delete_post_meta($id, 'rpgc_expiry_date' );
	    }

	    $description = get_post_meta( $id, 'rpgc_description', true );
	    if( isset( $description ) ) {
	        $giftInfo['description'] = $description;
	        delete_post_meta($id, 'rpgc_description' );
	    }

		$sendTheEmail = get_post_meta( $id, 'rpgc_email_sent', true );
	    if( isset( $sendTheEmail ) ) {
	        $giftInfo['sendTheEmail'] = $sendTheEmail;
	        delete_post_meta($id, 'rpgc_email_sent' );
	    }	    

		update_post_meta( $id, '_wpr_giftcard', $giftInfo );

	    echo 'Gift card ' . wpr_get_giftcard_number( $id ) . ': Updated <br />';

	endwhile;

	wp_reset_query();

}
add_action( 'upgrader_process_complete', 'wpr_update_database', 10, 2 );



