<?php
/**
 * Gift Card Product Functions
 *
 * @package     Gift-Cards-for-Woocommerce
 * @copyright   Copyright (c) 2014, Ryan Pletcher
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



function wpr_change_add_to_cart_button ( $link ) {
	global $post;

	if ( preventAddToCart( $post->ID ) ) {
		$giftCard_button = get_option( "woocommerce_giftcard_button" );

		if( $giftCard_button <> '' ){
			$giftCardText = get_option( "woocommerce_giftcard_button" );
		} else {
			$giftCardText = 'Customize';
		}

		$link = '<a href="' . esc_url( get_permalink( $post->ID ) ) . '" rel="nofollow" data-product_id="' . esc_attr( $post->ID ) . '" data-product_sku="' . esc_attr( $post->ID ) . '" class="button product_type_' . esc_attr( $post->product_type ) . '">' . $giftCardText . '</a>';
	}

	return  apply_filters( 'wpr_change_add_to_cart_button', $link, $post);
}
add_filter( 'woocommerce_loop_add_to_cart_link', 'wpr_change_add_to_cart_button' );



function rpgc_cart_fields( ) {
	global $post;

	$is_giftcard = get_post_meta( $post->ID, '_giftcard', true );

	$is_required_field_giftcard = get_option( 'woocommerce_enable_giftcard_info_requirements' );

	if ( $is_giftcard == 'yes' ) {
		$is_reload		= get_post_meta( $post->ID, '_wpr_allow_reload', true );
		$is_physical	= get_post_meta( $post->ID, '_wpr_physical_card', true );

		do_action( 'rpgc_before_all_giftcard_fields', $post );
		
		$rpgc_to 			= ( isset( $_POST['rpgc_to'] ) ? sanitize_text_field( $_POST['rpgc_to'] ) : "" );
		$rpgc_to_email 		= ( isset( $_POST['rpgc_to_email'] ) ? sanitize_text_field( $_POST['rpgc_to_email'] ) : "" );
		$rpgc_note			= ( isset( $_POST['rpgc_note'] ) ? sanitize_text_field( $_POST['rpgc_note'] ) : ""  );
		$rpgc_address		= ( isset( $_POST['rpgc_address'] ) ? sanitize_text_field( $_POST['rpgc_address'] ) : ""  );
		$rpgc_reloading		= ( isset( $_POST['rpgc_reload_check'] ) ? sanitize_text_field( $_POST['rpgc_reload_check'] ) : ""  );
		$rpgc_reload_number	= ( isset( $_POST['rpgc_reload_card'] ) ? sanitize_text_field( $_POST['rpgc_reload_card'] ) : ""  );

		$rpw_to_check 		= ( get_option( 'woocommerce_giftcard_to' ) <> NULL ? get_option( 'woocommerce_giftcard_to' ) : __('To', 'rpgiftcards' ) );
		$rpw_toEmail_check 	= ( get_option( 'woocommerce_giftcard_toEmail' ) <> NULL ? get_option( 'woocommerce_giftcard_toEmail' ) : __('To Email', 'rpgiftcards' )  );
		$rpw_note_check		= ( get_option( 'woocommerce_giftcard_note' ) <> NULL ? get_option( 'woocommerce_giftcard_note' ) : __('Note', 'rpgiftcards' )  );
		$rpw_address_check	= ( get_option( 'woocommerce_giftcard_address' ) <> NULL ? get_option( 'woocommerce_giftcard_address' ) : __('Address', 'rpgiftcards' )  );
		//$wpr_physical_card 	= ( get_option( 'woocommerce_giftcard_to' ) <> NULL ? get_option( 'woocommerce_giftcard_to' ) : __('To', 'rpgiftcards' ) );
		?>

		<div>
			<?php if ( $is_required_field_giftcard == "yes" ) { ?>
				<div class="rpw_product_message hide-on-reload"><?php _e('All fields below are required', 'rpgiftcards' ); ?></div>
			<?php } else { ?>
				<div class="rpw_product_message hide-on-reload"><?php _e('All fields below are optional', 'rpgiftcards' ); ?></div>
			<?php } ?>
			
			<?php  do_action( 'rpgc_before_product_fields' ); ?>

			<input type="hidden" id="rpgc_description" name="rpgc_description" value="<?php _e('Generated from the website.', 'rpgiftcards' ); ?>" />
			<input type="text" name="rpgc_to" id="rpgc_to" class="input-text hide-on-reload" style="margin-bottom:5px;" placeholder="<?php echo $rpw_to_check; ?>" value="<?php echo $rpgc_to; ?>">
			
			<?php if ( $is_physical == 'yes' ) { ?>
				<textarea class="input-text hide-on-reload" id="rpgc_address" name="rpgc_address" rows="2" style="margin-bottom:5px;" placeholder="<?php echo $rpw_address_check; ?>"><?php echo $rpgc_address; ?></textarea>
			<?php } else { ?> 
				<input type="email" name="rpgc_to_email" id="rpgc_to_email" class="input-text hide-on-reload" placeholder="<?php echo $rpw_toEmail_check; ?>" style="margin-bottom:5px;" value="<?php echo $rpgc_to_email; ?>">
			<?php } ?>
			<textarea class="input-text hide-on-reload" id="rpgc_note" name="rpgc_note" rows="2" style="margin-bottom:5px;" placeholder="<?php echo $rpw_note_check; ?>"><?php echo $rpgc_note; ?></textarea>
			<?php if ( $is_reload == "yes" ) { ?>
				<input type="checkbox" name="rpgc_reload_check" id="rpgc_reload_check" <?php if ( $rpgc_reloading == "on") { echo "checked=checked"; } ?>> Reload existing Gift Card
				<input type="text" name="rpgc_reload_card" id="rpgc_reload_card" class="input-text show-on-reload" style="margin-bottom:5px; display:none;" placeholder="<?php _e('Enter Gift Card Number', 'rpgiftcards' ); ?>" value="<?php echo $rpgc_reload_number; ?>">
			<?php } ?>

			<?php  do_action( 'rpgc_after_product_fields', $post->ID ); ?>

		</div>
		<?php

		if ( get_option( "woocommerce_enable_multiples") != 'yes' ) {
			echo '
				<script>
					jQuery( document ).ready( function( $ ){ $( ".quantity" ).hide( ); });
					
					jQuery("#rpgc_reload_check").change( function( $ ) {
						jQuery(".hide-on-reload").toggle();
					});

				</script>';
		}
	}
}
add_action( 'woocommerce_before_add_to_cart_button', 'rpgc_cart_fields' );