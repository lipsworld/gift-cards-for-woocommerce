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
 * Load frontend scripts
 *
 * @since       1.0.0
 * @return      void
 */
function wpr_giftcards_scripts( $hook ) {
    global $post;

    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'jquery-ui-core' );
    wp_enqueue_script( 'jquery-ui-datepicker' );

    wp_enqueue_style( 'jquery-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );

    wp_enqueue_script( 'wpr_giftcards_js', RPWCGC_URL . '/assets/js/scripts.js', array( 'jquery' ) );
    wp_enqueue_style( 'wpr_giftcards_css', RPWCGC_URL . '/assets/css/styles.css' );

    if( is_checkout() ) {
        wp_register_script( 'wpr_giftcards_checkout_js', RPWCGC_URL . '/assets/js/checkout.js', array( 'jquery' ), RPWCGC_VERSION, false );
        wp_enqueue_script( 'wpr_giftcards_checkout_js' );
    }

}

add_action( 'wp_enqueue_scripts', 'wpr_giftcards_scripts' );
