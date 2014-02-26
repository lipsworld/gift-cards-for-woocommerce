<?php
/**
 * Product Functions
 *
 * @package     Woocommerce
 * @subpackage  Giftcards
 * @copyright   Copyright (c) 2014, Ryan Pletcher
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! function_exists( 'rpgc_checkout_form' ) ) {

	/**
	 * Output the Giftcard form for the checkout.
	 * @access public
	 * @subpackage Checkout
	 * @return void
	 */
	function rpgc_checkout_form() {
		global $woocommerce;

		$info_message = apply_filters( 'woocommerce_checkout_giftcaard_message', __( 'Have a giftcard?', RPWCGC_CORE_TEXT_DOMAIN ) );
		?>

		<p class="woocommerce-info"><?php echo $info_message; ?> <a href="#" class="showgiftcard"><?php _e( 'Click here to enter your giftcard', RPWCGC_CORE_TEXT_DOMAIN ); ?></a></p>

		<form class="checkout_giftcard" method="post" style="display:none">

			<p class="form-row form-row-first">
				<input type="text" name="giftcard_code" class="input-text" placeholder="<?php _e( 'Gift Card', RPWCGC_CORE_TEXT_DOMAIN ); ?>" id="giftcard_code" value="" />
			</p>

			<p class="form-row form-row-last">
				<input type="submit" class="button" name="apply_giftcard" value="<?php _e( 'Apply Giftcard', RPWCGC_CORE_TEXT_DOMAIN ); ?>" />
			</p>

			<div class="clear"></div>
		</form>

		<script>
			jQuery(document).ready(function($) {
				$('a.showgiftcard').click(function(){
					$('.checkout_giftcard').slideToggle();
					$('#giftcard_code').focus();
						return false;
					});

					/* AJAX Coupon Form Submission */
					$('form.checkout_giftcard').submit( function() {
						var $form = $(this);

						if ( $form.is('.processing') ) return false;

						$form.addClass('processing').block({message: null, overlayCSS: {background: '#fff url(' + woocommerce_params.ajax_loader_url + ') no-repeat center', backgroundSize: '16px 16px', opacity: 0.6}});

						var data = {
							action: 			'woocommerce_apply_giftcard',
							security: 			'apply-giftcard',
							giftcard_code:		$form.find('input[name=giftcard_code]').val()
						};

						$.ajax({
							type: 		'POST',
							url: 		woocommerce_params.ajax_url,
							data:		data,
							success: 	function( code ) {
								$('.woocommerce-error, .woocommerce-message').remove();
								$form.removeClass('processing').unblock();

								if ( code ) {
									$form.before( code );
									$form.slideUp();

									$('body').trigger('update_checkout');
								}
							},
							dataType: 	"html"
						});
						return false;
					});

			});

		</script>

		<?php
	}
}
add_action( 'woocommerce_before_checkout_form', 'rpgc_checkout_form', 10 );



function rpgc_add_card_data( $cart_item_key, $product_id, $quantity ) {
	global $woocommerce, $post;

	$is_giftcard = get_post_meta( $product_id, '_giftcard', true );

	if ( $is_giftcard ) {



		$rpgc_to = woocommerce_clean( $_POST['rpgc_to'] );
		$rpgc_to_email = woocommerce_clean( $_POST['rpgc_to_email'] );
		$rpgc_note = woocommerce_clean( $_POST['rpgc_note'] );


		if ( $rpgc_to == '' ) { $rpgc_to = 'NA'; }
		if ( $rpgc_to_email == '' ) { $rpgc_to_email = 'NA'; }
		if ( $rpgc_note == '' ) { $rpgc_note = 'NA'; }

		$giftcard_data = array(
			'To'    => $rpgc_to,
			'To Email'   => $rpgc_to_email,
			'Note'   => $rpgc_note,
		);

		$woocommerce->cart->cart_contents[$cart_item_key]["variation"] = $giftcard_data;

		return $woocommerce;
	}
}
add_action( 'woocommerce_add_to_cart', 'rpgc_add_card_data', 10, 3 );

function rpgc_add_giftcard_to_paypal( $paypal_args ) {
	global $woocommerce;

	$giftCardPayment = $woocommerce->session->giftcard_payment;

	if ( isset( $paypal_args['discount_amount_cart'] ) ) {
		$paypal_args['discount_amount_cart'] = $paypal_args['discount_amount_cart'] + $giftCardPayment;
	} else { 
		$paypal_args['discount_amount_cart'] = $giftCardPayment;
	}

	return $paypal_args;
}

add_filter( 'woocommerce_paypal_args', 'rpgc_add_giftcard_to_paypal');


/**
 * AJAX apply coupon on checkout page
 * @access public
 * @return void
 */
function woocommerce_ajax_apply_giftcard() {
	global $woocommerce, $wpdb;

	if ( ! empty( $_POST['giftcard_code'] ) ) {
		$giftCardNumber = sanitize_text_field( $_POST['giftcard_code'] );

		$woocommerce->cart->total = $woocommerce->session->giftcard_payment + $woocommerce->cart->total;

		unset( $woocommerce->session->giftcard_payment, $woocommerce->session->giftcard_id, $woocommerce->session->giftcard_post, $woocommerce->session->giftcard_balance );

		// Check for Giftcard
		$giftcard_found = $wpdb->get_var( $wpdb->prepare( "
			SELECT $wpdb->posts.ID
			FROM $wpdb->posts
			WHERE $wpdb->posts.post_type = 'rp_shop_giftcard'
			AND $wpdb->posts.post_status = 'publish'
			AND $wpdb->posts.post_title = '%s'
		", $giftCardNumber ) );

		$orderTotal = (float) $woocommerce->cart->total;

		if ( $giftcard_found ) {
			// Valid Gift Card Entered					

			$oldBalance = get_post_meta( $giftcard_found, 'rpgc_balance' );

			if ( is_string( $oldBalance[0] ) )  // Determin if the Value from $oldBalance is a String and convert it
				$oldGiftcardValue = (float) $oldBalance[0];

			if ( is_string( $orderTotal ) )   // Determin if the Value from $orderTotal is a String and convert it
				$orderTotalCost = (float) $orderTotal;

			$woocommerce->session->giftcard_post = $giftcard_found;
			$woocommerce->session->giftcard_id = $giftCardNumber;


			if ( $oldGiftcardValue == 0 ) {
				// Giftcard Entered does not have a balance
				$woocommerce->add_error( __( 'Gift Card does not have a balance!', RPWCGC_CORE_TEXT_DOMAIN ) );

			} elseif ( $oldGiftcardValue >= $orderTotal ) {
				//  Giftcard Balance is more than the order total.
				//  Subtract the order from the card
				$woocommerce->session->giftcard_payment = $orderTotal;
				$woocommerce->session->giftcard_balance = $oldGiftcardValue - $orderTotal;
				$msg = __( 'Gift card applied successfully.', RPWCGC_CORE_TEXT_DOMAIN );
				$woocommerce->add_message(  __( 'Gift card applied successfully.', RPWCGC_CORE_TEXT_DOMAIN ) );

			} elseif ( $oldGiftcardValue < $orderTotal ) {
				//  Giftcard Balance is less than the order total.
				//  Subtract the giftcard from the order total
				$woocommerce->session->giftcard_payment = $oldGiftcardValue;
				$woocommerce->session->giftcard_balance = 0;
				$woocommerce->add_message(  __( 'Gift card applied successfully.', RPWCGC_CORE_TEXT_DOMAIN ) );

			}
		} else {
			// Giftcard Entered does not exist
			$woocommerce->add_error( __( 'Gift Card does not exist!', RPWCGC_CORE_TEXT_DOMAIN ) );
		}

	}

	$woocommerce->show_messages();

	die();
}
add_action( 'wp_ajax_woocommerce_apply_giftcard', 'woocommerce_ajax_apply_giftcard' );
add_action( 'wp_ajax_nopriv_woocommerce_apply_giftcard', 'woocommerce_ajax_apply_giftcard' );

/**
 * Displays the giftcard data on the order thank you page
 *
 */
function rpgc_display_giftcard( $order ) {
	global $woocommerce;

	$theIDNum =  get_post_meta( $order->id, 'rpgc_id' );
	$theBalance = get_post_meta( $order->id, 'rpgc_balance' );

	if( empty( $theIDNum[0] ) ) {
		if ( $theIDNum[0] <> '' ) {
		?>
			<h4><?php _e( 'Remaining Gift Card Balance:', RPWCGC_CORE_TEXT_DOMAIN ); ?><?php echo ' ' . woocommerce_price( $theBalance[0] ); ?> </h4>
			<?php
		}
	}

	$theGiftCardData = get_post_meta( $order->id, 'rpgc_data' );
	if( isset( $theGiftCardData[0] ) ) {
		if ( $theGiftCardData[0] <> '' ) {
	?>
			<h4><?php _e( 'Gift Card Informaion:', RPWCGC_CORE_TEXT_DOMAIN ); ?></h4>
			<?php
			$i = 1;

			foreach ( $theGiftCardData[0] as $giftcard ) {
				if ( $i % 2 ) echo '<div style="margin-bottom: 10px;">';
				echo '<div style="float: left; width: 45%; margin-right: 2%;>';
				echo '<h6"><strong>Giftcard ' . $i . '</strong></h6>';
				echo '<ul style="font-size: 0.85em; list-style: none outside none;">';
				if ( $giftcard[rpgc_product_num] ) echo '<li>Card: ' . get_the_title( $giftcard[rpgc_product_num] ) . '</li>';
				if ( $giftcard[rpgc_to] ) echo  '<li>To: ' . $giftcard[rpgc_to] . '</li>';
				if ( $giftcard[rpgc_to_email] ) echo  '<li>Send To: ' . $giftcard[rpgc_to_email] . '</li>';
				if ( $giftcard[rpgc_balance] ) echo  '<li>Balance: ' . woocommerce_price( $giftcard[rpgc_balance] ) . '</li>';
				if ( $giftcard[rpgc_note] ) echo  '<li>Note: ' . $giftcard[rpgc_note] . '</li>';
				if ( $giftcard[rpgc_quantity] ) echo  '<li>Quantity: ' . $giftcard[rpgc_quantity] . '</li>';
				echo '</ul>';
				echo '</div>';
				if ( !( $i % 2 ) ) echo '</div>';
				$i++;
			}
			echo '<div class="clear"></div>';
		}
	}

}
add_action( 'woocommerce_order_details_after_order_table', 'rpgc_display_giftcard' );
add_action( 'woocommerce_email_after_order_table', 'rpgc_display_giftcard' );






