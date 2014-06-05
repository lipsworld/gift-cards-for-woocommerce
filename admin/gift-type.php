<?php
/**
 * Admin functions for the rp_shop_giftcard post type.
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WC_Admin_CPT' ) )
	include( WC()->plugin_path() . '/includes/admin/post-types/class-wc-admin-cpt.php' );

if ( ! class_exists( 'WPR_Giftcard_Admin_CPT_Shop_Giftcard' ) ) :

/**
 * WC_Admin_CPT_Shop_Giftcard Class
 */
class WPR_Giftcard_Admin_CPT_Shop_Giftcard extends WC_Admin_CPT {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->type = 'rp_shop_giftcard';

		// Post title fields
		add_filter( 'enter_title_here', array( $this, 'enter_title_here' ), 1, 2 );
		//add_action( 'edit_form_after_title', array( $this, 'giftcard_description_field' ) );

		// Admin Columns
		add_filter( 'manage_edit-rp_shop_giftcard_columns', array( $this, 'edit_columns' ) );
		add_action( 'manage_rp_shop_giftcard_posts_custom_column', array( $this, 'custom_columns' ), 2 );
		add_filter( 'request', array( $this, 'giftcards_by_type_query' ) );

		// Call WC_Admin_CPT constructor
		parent::__construct();
	}

	/**
	 * Change title boxes in admin.
	 * @param  string $text
	 * @param  object $post
	 * @return string
	 */
	public function enter_title_here( $text, $post ) {
		if ( $post->post_type == 'rp_shop_giftcard' )
			return __( 'Giftcard code', 'woocommerce' );

		return $text;
	}

	/**
	 * Change the columns shown in admin.
	 */
	public function edit_columns( $columns ) {
		$columns = array();

		$columns["cb"] 			= "<input type=\"checkbox\" />";
		$columns["title"] 		= __( 'Giftcard Number', RPWCGC_CORE_TEXT_DOMAIN );
		$columns["amount"] 		= __( 'Giftcard Amount', RPWCGC_CORE_TEXT_DOMAIN );
		$columns["balance"]		= __( 'Remaining Balance', RPWCGC_CORE_TEXT_DOMAIN );
		$columns["buyer"]		= __( 'Buyer', RPWCGC_CORE_TEXT_DOMAIN );
		$columns["recipient"]	= __( 'Recipient', RPWCGC_CORE_TEXT_DOMAIN );
		$columns["expiry_date"] = __( 'Expiry date', RPWCGC_CORE_TEXT_DOMAIN );
		$columns['date']		= __( 'Creation Date', RPWCGC_CORE_TEXT_DOMAIN );

		return $columns;
	}

	/**
	 * Define our custom columns shown in admin.
	 * @param  string $column
	 */
	public function custom_columns( $column ) {
		global $post, $woocommerce;

		switch ( $column ) {
			case "title" :
				$edit_link = get_edit_post_link( $post->ID );
				$title = _draft_or_post_title();
				$post_type_object = get_post_type_object( $post->post_type );
				$can_edit_post = current_user_can( $post_type_object->cap->edit_post, $post->ID );

				echo '<div class="code tips" data-tip="' . __( 'Edit giftcard', 'woocommerce' ) . '"><a href="' . esc_attr( $edit_link ) . '"><span>' . esc_html( $title ). '</span></a></div>';

				_post_states( $post );

				// Get actions
				$actions = array();

				if ( current_user_can( $post_type_object->cap->delete_post, $post->ID ) ) {
					if ( 'trash' == $post->post_status )
						$actions['untrash'] = "<a title='" . esc_attr( __( 'Restore this item from the Trash', 'woocommerce' ) ) . "' href='" . wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $post->ID ) ), 'untrash-post_' . $post->ID ) . "'>" . __( 'Restore', 'woocommerce' ) . "</a>";
					elseif ( EMPTY_TRASH_DAYS )
						$actions['trash'] = "<a class='submitdelete' title='" . esc_attr( __( 'Move this item to the Trash', 'woocommerce' ) ) . "' href='" . get_delete_post_link( $post->ID ) . "'>" . __( 'Trash', 'woocommerce' ) . "</a>";
					if ( 'trash' == $post->post_status || !EMPTY_TRASH_DAYS )
						$actions['delete'] = "<a class='submitdelete' title='" . esc_attr( __( 'Delete this item permanently', 'woocommerce' ) ) . "' href='" . get_delete_post_link( $post->ID, '', true ) . "'>" . __( 'Delete Permanently', 'woocommerce' ) . "</a>";
				}

				$actions = apply_filters( 'post_row_actions', $actions, $post );

				echo '<div class="row-actions">';

				$i = 0;
				$action_count = sizeof($actions);

				foreach ( $actions as $action => $link ) {
					++$i;
					( $i == $action_count ) ? $sep = '' : $sep = ' | ';
					echo "<span class='$action'>$link$sep</span>";
				}
				echo '</div>';

			break;
			case "amount" :
				echo woocommerce_price( get_post_meta( $post->ID, 'rpgc_amount', true ) );
			break;
			case "buyer" :
				echo '<div><strong>' . esc_html( get_post_meta( $post->ID, 'rpgc_from', true ) ) . '</strong><br />';
				echo '<span style="font-size: 0.9em">' . esc_html( get_post_meta( $post->ID, 'rpgc_email_from', true ) ) . '</div>';
				break;

			case "recipient" :
				echo '<div><strong>' . esc_html( get_post_meta( $post->ID, 'rpgc_to', true ) ) . '</strong><br />';
				echo '<span style="font-size: 0.9em">' . esc_html( get_post_meta( $post->ID, 'rpgc_email_to', true ) ) . '</span></div>';
			break;

			case "amount" :	
				echo woocommerce_price( get_post_meta( $post->ID, 'rpgc_amount', true ) );
			break;

			case "balance" :
				echo woocommerce_price( get_post_meta( $post->ID, 'rpgc_balance', true ) );
			break;
			case "expiry_date" :
				$expiry_date = get_post_meta($post->ID, 'expiry_date', true);

				if ( $expiry_date )
					echo esc_html( date_i18n( 'F j, Y', strtotime( $expiry_date ) ) );
				else
					echo '&ndash;';
			break;
			case "description" :
				echo wp_kses_post( $post->post_excerpt );
			break;
		}
	}

	/**
	 * Filter the giftcards by the type.
	 *
	 * @param array $vars
	 * @return array
	 */
	public function giftcards_by_type_query( $vars ) {
		global $typenow, $wp_query;
	    if ( $typenow == 'rp_shop_giftcard' && ! empty( $_GET['giftcard_type'] ) ) {

			$vars['meta_key'] = 'discount_type';
			$vars['meta_value'] = wc_clean( $_GET['giftcard_type'] );

		}

		return $vars;
	}

}

endif;

return new WPR_Giftcard_Admin_CPT_Shop_Giftcard();

