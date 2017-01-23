<?php
/**
 * Gift Card Checkout Forms
 *
 * @package     Gift-Cards-for-Woocommerce
 * @copyright   Copyright (c) 2014, Ryan Pletcher
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'rpgc_cart_form' ) ) {
	// Adds the Gift Card form to the checkout page so that customers can enter the gift card information
	function rpgc_cart_form() {
		
		if( get_option( 'woocommerce_enable_giftcard_cartpage' ) == "yes" ) {
			do_action( 'wpr_before_cart_form' );
			
			?>
			
			<div class="giftcard" style="float: left;">
				<label type="text" for="giftcard_code" style="display: none;"><?php _e( 'Giftcard', 'rpgiftcards' ); ?>:</label><input type="text" name="giftcard_code" class="input-text" id="giftcard_code" value="" placeholder="<?php _e( 'Gift Card', 'rpgiftcards' ); ?>" /><input type="submit" class="button" name="apply_giftcard" value="<?php _e( 'Apply Gift card', 'rpgiftcards' ); ?>" />
			</div>

			<?php
			do_action( 'wpr_after_cart_form' );
		}

	}
	add_action( 'woocommerce_cart_actions', 'rpgc_cart_form' );
}


if ( ! function_exists( 'rpgc_checkout_form' ) ) {
	/**
	 * Output the Giftcard form for the checkout.
	 * @access public
	 * @subpackage Checkout
	 * @return void
	 */
	function rpgc_checkout_form() {

		if( get_option( 'woocommerce_enable_giftcard_checkoutpage' ) == 'yes' ){

			do_action( 'wpr_before_checkout_form' );

			$info_message = apply_filters( 'woocommerce_checkout_giftcard_message', __( 'Have a giftcard?', 'rpgiftcards' ) . ' <a href="#" class="showgiftcard">' . __( 'Click here to enter your code', 'rpgiftcards' ) . '</a>' );
			wc_print_notice( $info_message, 'notice' );
			?>

			<form class="checkout_giftcard" method="post" style="display:none">
				<p class="form-row form-row-first"><input type="text" name="giftcard_code" class="input-text" placeholder="<?php _e( 'Gift card', 'rpgiftcards' ); ?>" id="giftcard_code" value="" /></p>
				<p class="form-row form-row-last"><input type="submit" class="button" name="apply_giftcard" value="<?php _e( 'Apply Gift card', 'rpgiftcards' ); ?>" /></p>
				<div class="clear"></div>
			</form>

			<?php do_action( 'wpr_after_checkout_form' ); ?>

		<?php
		}
	}
	add_action( 'woocommerce_before_checkout_form', 'rpgc_checkout_form', 10 );
}


function woocommerce_apply_giftcard($giftcard_code) {
	global $wpdb;

	if ( ! empty( $_POST['giftcard_code'] ) ) {
		$giftcard_number = sanitize_text_field( $_POST['giftcard_code'] );
		$giftcard_id = WPR_Giftcard::wpr_get_giftcard_by_code( $giftcard_number );

		if ( $giftcard_id ) {
			
			if ( ! WC()->session->giftcard_post ) {
				WC()->session->giftcard_post = array();
			}

			if ( ! in_array($giftcard_id, WC()->session->giftcard_post) ) {
				$current_date = date("Y-m-d");
				$cardExperation = wpr_get_giftcard_expiration( $giftcard_id );

				if ( ( strtotime($current_date) <= strtotime($cardExperation) ) || ( strtotime($cardExperation) == '' ) ) {
					if( wpr_get_giftcard_balance( $giftcard_id ) > 0 ) {
						
						if ( WC()->session->giftcard_post == NULL ) {
							WC()->session->giftcard_post = array( $giftcard_id );
							
						} else {
							$newCard = array( $giftcard_id );
							$currentCards = WC()->session->giftcard_post;

							WC()->session->giftcard_post = array_merge($newCard, $currentCards);
						}

						if ( get_option( "woocommerce_disable_coupons" ) == "yes" ) {
							WC()->cart->remove_coupons();
						}

						WC()->cart->calculate_totals();

						wc_add_notice(  __( 'Gift card applied successfully.', 'rpgiftcards' ), 'success' );

					} else {
						wc_add_notice( __( 'Gift Card does not have a balance!', 'rpgiftcards' ), 'error' );
					}
				} else {
					wc_add_notice( __( 'Gift Card has expired!', 'rpgiftcards' ), 'error' ); // Giftcard Entered has expired
				}
			} else {
				wc_add_notice( __( 'Gift Card already in the cart!', 'rpgiftcards' ), 'error' );  //  You already have a gift card in the cart		
			}
		} else {		
			wc_add_notice( __( 'Gift Card does not exist!', 'rpgiftcards' ), 'error' ); // Giftcard Entered does not exist
		}

		wc_print_notices();
		
		if ( defined('DOING_AJAX') && DOING_AJAX ) {
			die();
		}
	}
}
add_action( 'wp_ajax_woocommerce_apply_giftcard', 'woocommerce_apply_giftcard' );



function woocommerce_apply_giftcard_ajax($giftcard_code) {

	woocommerce_apply_giftcard( $giftcard_code );

	WC()->cart->calculate_totals();

}
add_action( 'wp_ajax_nopriv_woocommerce_apply_giftcard', 'woocommerce_apply_giftcard_ajax' );


function apply_cart_giftcard( ) {
	if ( isset( $_POST['giftcard_code'] ) ) 
		woocommerce_apply_giftcard( $_POST['giftcard_code'] );
	
	WC()->cart->calculate_totals();

}
add_action ( 'woocommerce_before_cart', 'apply_cart_giftcard' );
add_action ( 'wpr_before_checkout_form', 'apply_cart_giftcard' );