<?php
/*
Plugin Name: WooCommerce - Gift Cards
Plugin URI: http://wp-ronin.com
Description: WooCommerce - Gift Cards allows you to offer gift cards to your customer and allow them to place orders using them.
Version: 1.3.8
Author: Ryan Pletcher
Author URI: http://ryanpletcher.com
License: GPL2
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Plugin version
if ( ! defined( 'RPWCGC_VERSION' ) )
	define( 'RPWCGC_VERSION', '1.3.8' );

// Plugin Folder Path
if ( ! defined( 'RPWCGC_PATH' ) )
	define( 'RPWCGC_PATH', plugin_dir_path( __FILE__ ) );

// Plugin Folder URL
if ( ! defined( 'RPWCGC_URL' ) )
	define( 'RPWCGC_URL', plugins_url( 'gift-cards-for-woocommerce', 'giftcards.php' ) );

// Plugin Root File
if ( ! defined( 'RPWCGC_FILE' ) )
	define( 'RPWCGC_FILE', plugin_basename( __FILE__ )  );

// Plugin Text Domian
if ( ! defined( 'RPWCGC_CORE_TEXT_DOMAIN' ) )
	define( 'RPWCGC_CORE_TEXT_DOMAIN', 'rpgiftcards');


function rpgc_woocommerce() {

	if ( !class_exists( 'woocommerce' ) )
		return;

	if ( is_admin() ) {
		// Create all admin functions and pages
		require_once RPWCGC_PATH . 'admin/gift-type.php';  
		require_once RPWCGC_PATH . 'admin/giftcard-metabox.php';  
		require_once RPWCGC_PATH . 'admin/giftcard-functions.php';
	}


	require_once RPWCGC_PATH . 'giftcard/giftcard-forms.php';
	require_once RPWCGC_PATH . 'giftcard/giftcard-checkout.php';
	require_once RPWCGC_PATH . 'giftcard/giftcard-paypal.php';
	require_once RPWCGC_PATH . 'admin/giftcard-product.php';

	function rpgc_create_post_type() {
		$show_in_menu = current_user_can( 'manage_woocommerce' ) ? 'woocommerce' : true;

		register_post_type( 'rp_shop_giftcard',
			array(
				'labels' => array(
					'name'      			=> __( 'Gift Cards', RPWCGC_CORE_TEXT_DOMAIN ),
					'singular_name'			=> __( 'Gift Card', RPWCGC_CORE_TEXT_DOMAIN ),
					'menu_name'    			=> _x( 'Gift Cards', 'Admin menu name', RPWCGC_CORE_TEXT_DOMAIN ),
					'add_new'     			=> __( 'Add Gift Card', RPWCGC_CORE_TEXT_DOMAIN ),
					'add_new_item'    		=> __( 'Add New Gift Card', RPWCGC_CORE_TEXT_DOMAIN ),
					'edit'      			=> __( 'Edit', RPWCGC_CORE_TEXT_DOMAIN ),
					'edit_item'    			=> __( 'Edit Gift Card', RPWCGC_CORE_TEXT_DOMAIN ),
					'new_item'     			=> __( 'New Gift Card', RPWCGC_CORE_TEXT_DOMAIN ),
					'view'      			=> __( 'View Gift Cards', RPWCGC_CORE_TEXT_DOMAIN ),
					'view_item'    			=> __( 'View Gift Card', RPWCGC_CORE_TEXT_DOMAIN ),
					'search_items'    		=> __( 'Search Gift Cards', RPWCGC_CORE_TEXT_DOMAIN ),
					'not_found'    			=> __( 'No Gift Cards found', RPWCGC_CORE_TEXT_DOMAIN ),
					'not_found_in_trash'	=> __( 'No Gift Cards found in trash', RPWCGC_CORE_TEXT_DOMAIN ),
					'parent'     			=> __( 'Parent Gift Card', RPWCGC_CORE_TEXT_DOMAIN )
				),
				'public'  		=> true,
				'has_archive' 	=> true,
				'show_in_menu'  => $show_in_menu,
				'hierarchical' 	=> false,
				'supports'   	=> array( 'title', 'comments' )
			)
		);
	
		register_post_status( 'zerobalance', array(
			'label'                     => __( 'Zero Balance', RPWCGC_CORE_TEXT_DOMAIN ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Zero Balance <span class="count">(%s)</span>', 'Zero Balance <span class="count">(%s)</span>' )
		) );
		
	}
	add_action( 'init', 'rpgc_create_post_type' );



	function rpgc_add_settings_page( $settings ) {
		$settings[] = include( RPWCGC_PATH . 'admin/giftcard-settings.php' );

		return apply_filters( 'rpgc_setting_classes', $settings );
	}
	add_filter('woocommerce_get_settings_pages','rpgc_add_settings_page', 10, 1);




}
add_action( 'plugins_loaded', 'rpgc_woocommerce', 30 );

/**
 * Load the Text Domain for i18n
 * @return void
 * @access public
*/
function rpwcgc_loaddomain() {
	load_plugin_textdomain( RPWCGC_CORE_TEXT_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'rpwcgc_loaddomain' ); 
