<?php
/**
 * Gift Card Metabox Functions
 *
 * @package     Gift-Cards-for-Woocommerce
 * @copyright   Copyright (c) 2014, Ryan Pletcher
 *
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Calls the class on the post edit screen.
 */
function call_WPR_Gift_Card_Order_Meta() {
    new WPR_Gift_Card_Order_Meta();
}

if ( is_admin()  ) {
    add_action( 'load-post.php', 'call_WPR_Gift_Card_Order_Meta' );
    add_action( 'load-post-new.php', 'call_WPR_Gift_Card_Order_Meta' );
}


/** 
 * The Class.
 */
class WPR_Gift_Card_Order_Meta {

	/**
	 * Hook into the appropriate actions when the class is constructed.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'rpgc_meta_boxes' ) );
		//add_action( 'save_post', array( $this, 'save' ) );
		
		if( isset( $_GET['post_type'] ) ) {
			if ( $_GET['post_type'] == 'rp_shop_giftcard' )
				add_action( 'post_submitbox_misc_actions', array( $this, 'wpr_giftcard_title' ) );
		}
	}


	/**	
	 * Sets up the new meta box for the creation of a gift card.
	 * Removes the other three Meta Boxes that are not needed.
	 *
	 */
	public function rpgc_meta_boxes() {
		global $post;

		$data = get_post_meta( $post->ID );

		if ( isset( $data['rpgc_id'] ) ) {
			if ( $data['rpgc_id'][0] <> '' ) {
				add_meta_box(
					'rpgc-order-data',
					__( 'Gift Card Information', 'rpgiftcards' ),
					array( $this, 'rpgc_info_meta_box'),
					'shop_order',
					'side',
					'default'
				);
			}
		}
	}


	public function rpgc_info_meta_box( $post ) {
		global $wpdb;
		
		$data = get_post_meta( $post->ID );

		$orderCardNumbers 	= wpr_get_order_card_numbers( $post->ID );
		$orderCardBalance 	= wpr_get_order_card_balance( $post->ID );
		$orderCardPayment 	= wpr_get_order_card_payment( $post->ID );
		$isAlreadyRefunded	= wpr_get_order_refund_status( $post->ID );

		foreach ($orderCardNumbers as $key => $orderCardNumber ) {
			echo '<div>';
			echo '    <div class="options_group">';
				echo '<ul>';
					if ( isset( $orderCardNumber ) )
						echo '<li>' . __( 'Gift Card #:', 'rpgiftcards' ) . ' ' . esc_attr( $orderCardNumber ) . '</li>';

					if ( isset( $orderCardPayment ) )
						echo '<li>' . __( 'Payment:', 'rpgiftcards' ) . ' ' . woocommerce_price( $orderCardPayment[ $key ] ) . '</li>';

					if ( isset( $orderCardBalance ) )
						echo '<li>' . __( 'Balance remaining:', 'rpgiftcards' ) . ' ' . woocommerce_price( $orderCardBalance[ $key ] ) . '</li>';

				echo '</ul>';

				$giftcard_found = wpr_get_giftcard_by_code( $orderCardNumber );

				if ( $giftcard_found ) {
					echo '<div>';
						$link = 'post.php?post=' . $giftcard_found . '&action=edit';
						echo '<a href="' . admin_url( $link ) . '">' . __('Access Gift Card', 'rpgiftcards') . '</a>';
						
						if( ! empty( $isAlreadyRefunded[ $key] ) )
							echo  '<br /><span style="color: #dd0000;">' . __( 'Gift card refunded ', 'rpgiftcards' ) . ' ' . woocommerce_price( $orderCardPayment[ $key ] ) . '</span>';
					echo '</div>';
				
				}

			echo '    </div>';
			echo '</div>';
		}
	}

}