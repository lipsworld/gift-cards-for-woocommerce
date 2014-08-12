<?php
/**
 * Gift Card Short Codes
 *
 * @package     Woocommerce
 * @subpackage  Giftcards
 * @copyright   Copyright (c) 2014, Ryan Pletcher
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


function wpr_check_giftcard( $atts ) {
	global $wpdb, $woocommerce;

	if ( isset( $_POST['giftcard_code'] ) ) {

		var_dump($_POST['giftcard_code'] );

		echo '<h3>' . 'Balance is' . '</h3>';

		// Check for Giftcard
		$giftcard_found = $wpdb->get_var( $wpdb->prepare( "
			SELECT $wpdb->posts.ID
			FROM $wpdb->posts
			WHERE $wpdb->posts.post_type = 'rp_shop_giftcard'
			AND $wpdb->posts.post_status = 'publish'
			AND $wpdb->posts.post_title = '%s'
		", $giftCardNumber ) );

		if ( $_POST['giftcard_code'] ) {
			// Valid Gift Card Entered
			if ( ( strtotime($current_date) <= strtotime($cardExperation) ) || ( strtotime($cardExperation) == '' ) ) {

				$oldBalance = get_post_meta( $giftcard_found, 'rpgc_balance', true );

				if ( is_string( $oldBalance ) )  // Determin if the Value from $oldBalance is a String and convert it
					$GiftcardBlance = (float) $oldBalance;
			}
		}

		return  '<h3>Remaining Balance:</h3> ' . $$_POST['giftcard_code'] ;

	} else {



		$return = '';

		$return .= '<form class="check_giftcard_balance" method="post">';

			$return .= '<p class="form-row form-row-first">';
				$return .= '<input type="text" name="giftcard_code" class="input-text" placeholder="' . __( 'Gift card', RPWCGC_CORE_TEXT_DOMAIN ) . '" id="giftcard_code" value="" />';
			$return .= '</p>';

			$return .= '<p class="form-row form-row-last">';
				$return .= '<input type="submit" class="button" name="check_giftcard" value="' . __( 'Check Balance', RPWCGC_CORE_TEXT_DOMAIN ) . '" />';
			$return .= '</p>';

			$return .= '<div class="clear"></div>';
		$return .= '</form>';
		$return .= '<div id="theBalance"></div>';

	}

	return $return;

}
add_shortcode( 'giftcardbalance', 'wpr_check_giftcard' );
