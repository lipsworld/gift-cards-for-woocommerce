<?php
/**
 * Plugin Name: WooCommerce - Gift Cards
 * Plugin URI: http://wp-ronin.com
 * Description: WooCommerce - Gift Cards allows you to offer gift cards to your customer and allow them to place orders using them.
 * Author: WP Ronin
 * Author URI: http://wp-ronin.com
 * Version: 3.0.0b1
 * License: GPL2
 * 
 * Text Domain:     rpgiftcards
 *
 * @package         Gift-Cards-for-Woocommerce
 * @author          Ryan Pletcher
 * @copyright       Copyright (c) 2015
 *
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

if( ! class_exists( 'WPRWooGiftcards' ) ) {

    /**
     * Main WPRWooGiftcards class
     *
     * @since       1.0.0
     */
    class WPRWooGiftcards {

        /**
         * @var         WPRWooGiftcards $instance The one true WPRWooGiftcards
         * @since       1.0.0
         */
        private static $instance;


        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0.0
         * @return      object self::$instance The one true WPRWooGiftcards
         */
        public static function instance() {
            if( ! self::$instance ) {
                
                self::$instance = new WPRWooGiftcards();
                self::$instance->setup_constants();
                self::$instance->includes();
                self::$instance->load_textdomain();
                self::$instance->hooks();

            }

            return self::$instance;
        }
        

        /**
         * Setup plugin constants
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function setup_constants() {
            
            define( 'RPWCGC_VERSION', '3.0.0b1' ); // Plugin version
            define( 'RPWCGC_DIR',     plugin_dir_path( __FILE__ ) ); // Plugin Folder Path
            define( 'RPWCGC_URL',     plugins_url( 'gift-cards-for-woocommerce', 'giftcards.php' ) ); // Plugin Folder URL
            define( 'RPWCGC_FILE',    plugin_basename( __FILE__ ) ); // Plugin Root File
            
            if ( ! defined( 'WPR_STORE_URL' ) ) {
                define( 'WPR_STORE_URL', 'https://wp-ronin.com' ); // Premium Plugin Store
            }
        }


        /**
         * Include necessary files
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function includes() {
            // Include scripts
            if( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) {
                include( plugin_dir_path( __FILE__ ) . '/includes/plugin_updater.php' ); // load the custom updater for pro version of Gift Card Plugin
            }

            if( ! class_exists( 'WPR_Giftcard' ) ) {
                require_once RPWCGC_DIR . 'classes/class.giftcard.php';
            }

            require_once RPWCGC_DIR . 'includes/post-type.php';
            require_once RPWCGC_DIR . 'includes/meta.php';
            require_once RPWCGC_DIR . 'includes/functions.php';
            
            if( is_admin() ) {
                // Load all admin files
                require_once RPWCGC_DIR . 'admin/product.php';
                require_once RPWCGC_DIR . 'admin/scripts.php';
                require_once RPWCGC_DIR . 'admin/metabox-order.php';
                require_once RPWCGC_DIR . 'admin/metabox-product.php';

            } else {
                // Load all frontend files
                require_once RPWCGC_DIR . 'frontend/scripts.php';
                require_once RPWCGC_DIR . 'frontend/cart.php';
                require_once RPWCGC_DIR . 'frontend/checkout.php';
                require_once RPWCGC_DIR . 'frontend/product.php';
                require_once RPWCGC_DIR . 'frontend/shortcodes.php';

            }
            


            /*
            
            if( ! class_exists( 'WPR_Giftcard_Email' ) ) {
                require_once RPWCGC_DIR . 'includes/class.giftcardemail.php';
            }
            
            require_once RPWCGC_DIR . 'includes/giftcard-paypal.php';
            require_once RPWCGC_DIR . 'includes/widgets.php';

            */
        }


        /**
         * Internationalization
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function load_textdomain() {
            //add_filter( 'load_textdomain_mofile', array( $this, 'load_old_textdomain' ), 10, 2 );

            // Set filter for plugin's languages directory.
            $lang_dir  = dirname( plugin_basename( RPWCGC_DIR ) ) . '/languages/';
            $lang_dir  = apply_filters( 'giftcards_for_woocommerce_languages_directory', $lang_dir );

            // Traditional WordPress plugin locale filter.
            $locale        = apply_filters( 'plugin_locale',  get_locale(), 'rpgiftcards' );
            $mofile        = sprintf( '%1$s-%2$s.mo', 'rpgiftcards', $locale );

            // Look for wp-content/languages/wpo/rpgiftcards-{lang}_{country}.mo
            $mofile_global1 = WP_LANG_DIR . '/wpo/rpgiftcards-' . $locale . '.mo';

            // Look for wp-content/languages/wpo/wpr-{lang}_{country}.mo
            $mofile_global2 = WP_LANG_DIR . '/wpo/wpo-' . $locale . '.mo';

            // Look in wp-content/languages/plugins/rpgiftcards
            $mofile_global3 = WP_LANG_DIR . '/plugins/rpgiftcards/' . $mofile;

            if ( file_exists( $mofile_global1 ) ) {
                load_textdomain( 'rpgiftcards', $mofile_global1 );
            } elseif ( file_exists( $mofile_global2 ) ) {
                load_textdomain( 'rpgiftcards', $mofile_global2 );
            } elseif ( file_exists( $mofile_global3 ) ) {
                load_textdomain( 'rpgiftcards', $mofile_global3 );
            } else {
                // Load the default language files.
                load_plugin_textdomain( 'rpgiftcards', false, $lang_dir );
            }
        }


        /**
         * Run action and filter hooks
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         *
         */
        private function hooks() {
            // Register settings
            $wpr_woo_giftcard_settings = get_option( 'wpr_wg_options' );

            //add_filter( 'woocommerce_get_settings_pages', array( $this, 'rpgc_add_settings_page'), 10, 1);
            //add_filter( 'woocommerce_calculated_total', array( 'WPR_Giftcard', 'wpr_discount_total'), 10, 2 );
            //add_filter( 'plugin_action_links_' . RPWCGC_FILE, array( __CLASS__, 'plugin_action_links' ) );

            //add_action( 'woocommerce_checkout_order_processed', array( 'WPR_Giftcard', 'reload_card'), 10, 1);
        }


        public function rpgc_add_settings_page( $settings ) {

            require_once RPWCGC_DIR . 'includes/class.settings.php';

            $settings[] = new RPGC_Settings();

            return apply_filters( 'rpgc_setting_classes', $settings );
        }
        
        /**
         * Show action links on the plugin screen.
         *
         * @param   mixed $links Plugin Action links
         * @since       2.2.2
         * @return  array
         */
        public static function plugin_action_links( $links ) {
            $action_links = array(
                'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=giftcard' ) . '" title="' . esc_attr( __( 'View Gift Card Settings', 'rpgiftcards', 'gift-cards-for-woocommerce' ) ) . '">' . __( 'Settings', 'rpgiftcards', 'gift-cards-for-woocommerce' ) . '</a>',
            );

            return array_merge( $action_links, $links );
        }

    }
} // End if class_exists check


/**
 * The main function responsible for returning the one true WPRWooGiftcards
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      WPRWooGiftcards The one true WPRWooGiftcards
 *
 */
function WPRWooGiftcards_load() {
    if( ! class_exists( 'WooCommerce' ) ) {
        if( ! class_exists( 'WPR_Giftcard_Activation' ) ) {
            require_once 'classes/class.activation.php';
        }

        $activation = new WPR_Giftcard_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
        $activation = $activation->run();
        
        //return WPRWooGiftcards::instance();
    } else {
        return WPRWooGiftcards::instance();
    }

}
add_action( 'plugins_loaded', 'WPRWooGiftcards_load' );
