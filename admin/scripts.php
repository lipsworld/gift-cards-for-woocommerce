<?php
/**
 * Scripts
 *
 * @package     EDD\PluginName\Scripts
 * @since       1.0.0
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


/**
 * Load admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function wpr_giftcards_admin_scripts( $hook ) {
    global $post_type, $post;

    wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css' );

    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'jquery-ui-core' );
    wp_enqueue_script( 'jquery-ui-datepicker' );
    
    wp_enqueue_style( 'jquery-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );


    if( $post_type == 'rp_shop_giftcard' ) {
        wp_register_script( 'wpr_giftcards_admin_js', RPWCGC_URL . '/assets/js/admin.js', array( 'jquery' ), RPWCGC_VERSION, false );
        wp_enqueue_script( 'wpr_giftcards_admin_js');
        
        if( $hook == 'post-new.php' ) {
            wp_enqueue_style( 'wpr_giftcards_admin', RPWCGC_URL . '/assets/css/admin-new.css' );
        } else {
            wp_enqueue_style( 'wpr_giftcards_admin', RPWCGC_URL . '/assets/css/admin.css' );
        }
    }

}
add_action( 'admin_enqueue_scripts', 'wpr_giftcards_admin_scripts', 100 );

