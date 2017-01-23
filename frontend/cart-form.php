<?php
/**
 * Gift Card Cart Functions
 *
 * @package     Gift-Cards-for-Woocommerce
 * @copyright   Copyright (c) 2014, Ryan Pletcher
 *
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

//  Display the current gift card information on the cart
//  *Plan on adding ability to edit the infomration in the future
function wpr_display_giftcard_in_cart() {
	$cart = WC()->session->cart;
	$gift = 0;
	$card = array();

	foreach( $cart as $key => $product ) {

		if( WPR_Giftcard::wpr_is_giftcard($product['product_id'] ) )
				$card[] = $product;
	}

	if( ! empty( $card ) ) {
		echo '<h6>' . __( 'Gift Cards In Cart', 'rpgiftcards' ) . '</h6>';
		echo '<table width="100%" class="shop_table cart">';
		echo '<thead>';
		echo '<tr><td>' . __( 'Name', 'rpgiftcards' ) . '</td><td>' . __( 'Email', 'rpgiftcards' ) . '</td><td>' . __( 'Price', 'rpgiftcards' ) . '</td><td>' . __( 'Note', 'rpgiftcards' ) . '</td></tr>';
		echo '</thead>';
		foreach( $card as $key => $information ) {

			if( WPR_Giftcard::wpr_is_giftcard($information['product_id'] ) ){
				$gift += 1;

				$rpw_to_check 		= ( get_option( 'woocommerce_giftcard_to' ) <> NULL ? get_option( 'woocommerce_giftcard_to' ) : __('To', 'rpgiftcards' ) );
				$rpw_toEmail_check 	= ( get_option( 'woocommerce_giftcard_toEmail' ) <> NULL ? get_option( 'woocommerce_giftcard_toEmail' ) : __('To Email', 'rpgiftcards' )  );
				$rpw_note_check		= ( get_option( 'woocommerce_giftcard_note' ) <> NULL ? get_option( 'woocommerce_giftcard_note' ) : __('Note', 'rpgiftcards' )  );
				$rpw_reload_card	= ( get_option( 'woocommerce_giftcard_reload_card' ) <> NULL ? get_option( 'woocommerce_giftcard_reload' ) : __('Card Number', 'rpgiftcards' )  );
				
				for ( $i = 0; $i < $information["quantity"]; $i++ ) { 
					echo '<tr style="font-size: 0.8em">';
						if ( isset( $information["variation"][$rpw_reload_card] ) ) {
							echo '<td colspan="2">' . __( 'Reload card:', 'rpgiftcards') . ' ' . $information["variation"][$rpw_reload_card] . '</td>';
						} else {
							echo '<td>';
							if ( isset( $information["variation"][$rpw_to_check] ) ) {
								echo $information["variation"][$rpw_to_check];
							}
							echo '</td>';
							
							echo '<td>';
							if ( isset( $information["variation"][$rpw_toEmail_check] ) ) {
								echo $information["variation"][$rpw_toEmail_check];
							}
							echo '</td>';
						}
						
						// Add a check to see if the product is on sale and if it is output the full price
						echo '<td>' . woocommerce_price( $information["line_subtotal"] / $information["quantity"] ) . '</td>';
						
						echo '<td>';
						if ( isset( $information["variation"][$rpw_toEmail_check] ) ) {
							echo $information["variation"][$rpw_note_check]; 
						}
						echo '</td>';
					echo '</tr>';
				}
			}
		}
		echo '</table>';

	}
}
add_action( 'woocommerce_after_cart_table', 'wpr_display_giftcard_in_cart' );

