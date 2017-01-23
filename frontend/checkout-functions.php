<?php
/**
 * Gift Card Checkout Functions
 *
 * @package     Gift-Cards-for-Woocommerce
 * @copyright   Copyright (c) 2014, Ryan Pletcher
 *
 */

/**
 * Updates the Gift Card and the order information when the order is processed
 *
 */
function rpgc_update_card( $order_id ) {
	global $woocommerce;

	$giftCards = WC()->session->giftcard_post;
	$giftcard = new WPR_Giftcard();
	$payment = $giftcard->wpr_get_payment_amount();

	if ( isset( $giftCards ) ) {
		foreach ($giftCards as $key => $giftCard_id ) {

			if ( $giftCard_id != '' ) {
				//Decrease Ballance of card
				
				$balance = wpr_get_giftcard_balance( $giftCard_id );

				$giftcard->wpr_decrease_balance( $giftCard_id );

				$giftCard_IDs = get_post_meta ( $giftCard_id, 'wpr_existingOrders_id', true );
				$giftCard_IDs[] = $order_id;

				$giftCardIDs = get_post_meta( $order_id, 'rpgc_id', true );
				$giftCardIDs[] = $giftCard_id;
				
				$giftCardPayments = get_post_meta( $order_id, 'rpgc_payment', true );
				$giftCardBalances = get_post_meta( $order_id, 'rpgc_balance', true );


				if ( $payment > $balance ){
					$giftCardPayments[] = $balance;
					$payment -= $balance;
					$newBalance = 0;
				} else {
					$giftCardPayments[] = $payment;
					$newBalance = $balance - $payment;
				}

				$giftCardBalances[] = $newBalance;

				//$newBalance = wpr_get_giftcard_balance( $giftCard_id );

				$giftCardInfo = get_post_meta( $giftCard_id, '_wpr_giftcard', true );

				$giftCardInfo['balance'] = $newBalance;

				update_post_meta( $giftCard_id, '_wpr_giftcard', $giftCardInfo ); // Update balance of Giftcard
				update_post_meta( $giftCard_id, 'rpgc_balance', $giftCardBalances );
				update_post_meta( $giftCard_id, 'wpr_existingOrders_id', $giftCard_IDs ); // Saves order id to gifctard post
				
				update_post_meta( $order_id, 'rpgc_id', $giftCardIDs );
				update_post_meta( $order_id, 'rpgc_payment', $giftCardPayments );
				update_post_meta( $order_id, 'rpgc_balance', $giftCardBalances );

				WC()->session->idForEmail = $order_id;
				
			}
		}
	
		unset( WC()->session->giftcard_payment, WC()->session->giftcard_post );
	}

	if ( isset ( WC()->session->giftcard_data ) ) {
		update_post_meta( $order_id, 'rpgc_data', WC()->session->giftcard_data );

		unset( WC()->session->giftcard_data );
	}

}
add_action( 'woocommerce_checkout_order_processed', 'rpgc_update_card' );


/**
 * Function to add the giftcard data to the cart display on both the card page and the checkout page WC()->session->giftcard_balance
 *
 */
function rpgc_order_giftcard( ) {
	global $woocommerce;

	if ( isset( $_GET['remove_giftcards'] ) ) {
		$newGiftCards = array();
		$usedGiftCards = WC()->session->giftcard_post;

		foreach ($usedGiftCards as $key => $giftcard) {
			if ( wpr_get_giftcard_number( $giftcard ) != $_GET['remove_giftcards'] ) {
				$newGiftCards[] = $giftcard;
			}
		}

		WC()->session->giftcard_post = $newGiftCards;
		WC()->cart->calculate_totals();
	}

	if ( isset( WC()->session->giftcard_post ) ) {
		if ( WC()->session->giftcard_post ){

			$giftCards = WC()->session->giftcard_post;



			$giftcard = new WPR_Giftcard();
			$price = $giftcard->wpr_get_payment_amount();

			if ( is_cart() ) {
				$gotoPage = WC()->cart->get_cart_url();
			} else {
				$gotoPage = WC()->cart->get_checkout_url();	
			}

			?>
			<tr class="giftcard">
				<th><?php _e( 'Gift Card Payment', 'rpgiftcards' ); ?> </th>
				<td style="font-size:0.85em;">
					<?php echo woocommerce_price( $price ); ?>
					<?php foreach ( $giftCards as $key => $giftCard) { 
						$cardNumber = wpr_get_giftcard_number( $giftCard );
						$cardValue  = wpr_get_giftcard_balance( $giftCard );

						?>

						<br /> <a href="<?php echo add_query_arg( 'remove_giftcards', $cardNumber, $gotoPage ) ?>"><small>[<?php _e( 'Remove', 'rpgiftcards' ); ?> <?php echo woocommerce_price( $cardValue); ?> <?php _e( 'Gift Card', 'rpgiftcards' ); ?>]</small></a>
					<?php } ?>
				</td>
			</tr>
			<?php

		}
	}
}
add_action( 'woocommerce_review_order_before_order_total', 'rpgc_order_giftcard' );
add_action( 'woocommerce_cart_totals_before_order_total', 'rpgc_order_giftcard' );
