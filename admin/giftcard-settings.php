<?php
/**
 * WooCommerce Gift Card Settings
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WC_Settings_Accounts
 */
class RPGC_Settings extends WC_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'giftcard';
		$this->label = __( 'Gift Cards',  RPWCGC_CORE_TEXT_DOMAIN  );

		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
		add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_sections' ) );

		
		add_action( 'woocommerce_admin_field_addon_settings', array( $this, 'addon_setting' ) );
	}


	/**
	 * Get sections
	 *
	 * @return array
	 */
	public function get_sections() {

		$sections = array(
			''          => __( 'Gift Card Options', 'woocommerce' ),
			'extensions' => __( 'Premium Extensions', 'woocommerce' )
		);


		return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
	}

	/**
	 * Output sections
	 */
	public function output_sections() {
		global $current_section;

		$sections = $this->get_sections();

		if ( empty( $sections ) ) {
			return;
		}

		echo '<ul class="subsubsub">';

		$array_keys = array_keys( $sections );

		foreach ( $sections as $id => $label ) {
			echo '<li><a href="' . admin_url( 'admin.php?page=wc-settings&tab=' . $this->id . '&section=' . sanitize_title( $id ) ) . '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . $label . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
		}

		echo '</ul><br class="clear" />';
	}

	/**
	 * Output the settings
	 */
	public function output() {
		global $current_section;

		$settings = $this->get_settings( $current_section );

 		WC_Admin_Settings::output_fields( $settings );
	}

	/**
	 * Save settings
	 */
	public function save() {
		global $current_section;

		$settings = $this->get_settings( $current_section );
		WC_Admin_Settings::save_fields( $settings );
	}


	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function get_settings( $current_section = '' ) {
		if( $current_section == '' ) {

			$options = apply_filters( 'woocommerce_giftcard_settings', array(

				array( 'title' 		=> __( 'Processing Options',  RPWCGC_CORE_TEXT_DOMAIN  ), 'type' => 'title', 'id' => 'giftcard_processing_options_title' ),

				array(
					'title'         => __( 'Display on Cart?',  RPWCGC_CORE_TEXT_DOMAIN  ),
					'desc'          => __( 'Display the giftcard form on the cart page.',  RPWCGC_CORE_TEXT_DOMAIN  ),
					'id'            => 'woocommerce_enable_giftcard_cartpage',
					'default'       => 'yes',
					'type'          => 'checkbox',
					'autoload'      => false
				),

				array(
					'title'         => __( 'Display on Checkout?',  RPWCGC_CORE_TEXT_DOMAIN  ),
					'desc'          => __( 'Display the giftcard form on the checkout page.',  RPWCGC_CORE_TEXT_DOMAIN  ),
					'id'            => 'woocommerce_enable_giftcard_checkoutpage',
					'default'       => 'no',
					'type'          => 'checkbox',
					'autoload'      => false
				),

				array(
					'title'         => __( 'Shipping Charge?',  RPWCGC_CORE_TEXT_DOMAIN  ),
					'desc'          => __( 'Allow customers to pay for shipping with their gift card.',  RPWCGC_CORE_TEXT_DOMAIN  ),
					'id'            => 'woocommerce_enable_giftcard_process',
					'default'       => 'no',
					'type'          => 'checkbox',
					'autoload'      => false
				),
				array(
					'title'         => __( 'Customize Add to Cart?',  RPWCGC_CORE_TEXT_DOMAIN  ),
					'desc'          => __( 'Change Add to cart label and disable add to cart from product list.',  RPWCGC_CORE_TEXT_DOMAIN  ),
					'id'            => 'woocommerce_enable_addtocart',
					'default'       => 'no',
					'type'          => 'checkbox',
					'autoload'      => false
				),
				array(
					'title'         => __( 'Physical Card?',  RPWCGC_CORE_TEXT_DOMAIN  ),
					'desc'          => __( 'Select this if you would like to offer physical gift cards.',  RPWCGC_CORE_TEXT_DOMAIN  ),
					'id'            => 'woocommerce_enable_physical',
					'default'       => 'no',
					'type'          => 'checkbox',
					'autoload'      => false
				),

				array( 'type' => 'sectionend', 'id' => 'account_registration_options'),

				array( 'title' 		=> __( 'Product Options',  RPWCGC_CORE_TEXT_DOMAIN  ), 'type' => 'title', 'id' => 'giftcard_products_options_title' ),

				array(
					'name'     => __( 'To', RPWCGC_CORE_TEXT_DOMAIN ),
					'desc'     => __( 'This is the value that will display before a gift card number.', RPWCGC_CORE_TEXT_DOMAIN ),
					'id'       => 'woocommerce_giftcard_to',
					'std'      => 'To', // WooCommerce < 2.0
					'default'  => 'To', // WooCommerce >= 2.0
					'type'     => 'text',
					'desc_tip' =>  true,
				),

				array(
					'name'     => __( 'To Email', RPWCGC_CORE_TEXT_DOMAIN ),
					'desc'     => __( 'This is the value that will display before a gift card number.', RPWCGC_CORE_TEXT_DOMAIN ),
					'id'       => 'woocommerce_giftcard_toEmail',
					'std'      => 'Send To', // WooCommerce < 2.0
					'default'  => 'Send To', // WooCommerce >= 2.0
					'type'     => 'text',
					'desc_tip' =>  true,
				),

				array(
					'name'     => __( 'Note Option', RPWCGC_CORE_TEXT_DOMAIN ),
					'desc'     => __( 'This will change the placeholder field for the gift card note.', RPWCGC_CORE_TEXT_DOMAIN ),
					'id'       => 'woocommerce_giftcard_note',
					'std'      => 'Enter your note here.', // WooCommerce < 2.0
					'default'  => 'Enter your note here.', // WooCommerce >= 2.0
					'type'     => 'text',
					'desc_tip' =>  true,
				),

				array(
					'name'     => __( 'Gift Card Button Test', RPWCGC_CORE_TEXT_DOMAIN ),
					'desc'     => __( 'This is the text that will be displayed on the button to customize the information.', RPWCGC_CORE_TEXT_DOMAIN ),
					'id'       => 'woocommerce_giftcard_button',
					'std'      => 'Customize', // WooCommerce < 2.0
					'default'  => 'Customize', // WooCommerce >= 2.0
					'type'     => 'text',
					'desc_tip' =>  true,
				),

				array( 'type' => 'sectionend', 'id' => 'account_registration_options'),

			));
		} else {

			$options = array( 
				array (
					'title' 	=> __( 'Premium features available', RPWCGC_CORE_TEXT_DOMAIN),
					'type' 		=> 'title', 
					'desc' 		=> sprintf(__( 'You can now add additional functionallity to the gift card plugin using some of my premium plugins offered through %s.  If you are looking for some functionality that I have not created let me know and I would be happy to look into offering it in the future.  I also have a support forum for my premium plugins and the ones offered on Wordpress.org.  Please let me know if I can help with anything.', RPWCGC_CORE_TEXT_DOMAIN ), '<a href="wp-ronin.com">wp-ronin.com</a>'), 
					'id' 		=> 'rpgc_extra_features' 
				),

				array( 'type' 	=> 'sectionend', 'id' => 'giftcard_extensions' ),

				array( 'type' => 'addon_settings' ),

			); // End pages settings

		}

		return $options;

	}





	/**
	 * Output the frontend styles settings.
	 */
	public function addon_setting() {
		
		if( defined( 'RPWCGC_AUTO_CORE_TEXT_DOMAIN' ) || defined( 'WPR_CP_CORE_TEXT_DOMAIN' ) || defined( 'RPWCGC_CN_CORE_TEXT_DOMAIN' ) ) { ?>

			<h3>Activate Extensions</h3> 
			<?php do_action( 'wpr_add_license_field' ); ?>
			<br class="clear" />
		
		<?php } ?>
		
		<h3>Premium features available</h3>
		<p>
		You can now add additional functionallity to the gift card plugin using some of my premium plugins offered through <a href="wp-ronin.com">wp-ronin.com</a>.
		</p>
		<br class="clear" />
		<div class='wc_addons_wrap' style="margin-top:10px;">
		<ul class="products" style="overflow:hidden;">
		<?php

			$i = 0;

			if( defined( 'WPR_CP_CORE_TEXT_DOMAIN' ) ) {
				$addons[$i]["title"] = "Custom Price";
				$addons[$i]["image"] = "";
				$addons[$i]["excerpt"] = "Dont want to have to create multiple products to offer Gift Cards on your site.  Use this plugin to create a single product that allows your customers to put in the price.  Select 10 – 10000000 it wont matter.";
				$addons[$i]["link"] = "https://wp-ronin.com/downloads/woocommerce-gift-cards-custom-price/";
				$i++;
			}

			if( defined( 'RPWCGC_CN_CORE_TEXT_DOMAIN' ) ) {
				$addons[$i]["title"] = "Customize Card Number";
				$addons[$i]["image"] = "";
				$addons[$i]["excerpt"] = "Want to be able to customize the gift card number when it is created, this plugin will do it.";
				$addons[$i]["link"] = "https://wp-ronin.com/downloads/woocommerce-gift-cards-customize-gift-card/";
				$i++;
			}

			if( defined( 'RPWCGC_AUTO_CORE_TEXT_DOMAIN' ) ) {
				$addons[$i]["title"] = "Auto Send Card";
				$addons[$i]["image"] = "";
				$addons[$i]["excerpt"] = "Save time creating gift cards by using this plugin.  Enable it and customers will have their gift card sent out directly upon purchase or payment.";
				$addons[$i]["link"] = "https://wp-ronin.com/downloads/auto-send-email-woocommerce-gift-cards/";
				$i++;
			}

			$addons[$i]["title"] = "Bundle Package";
			$addons[$i]["image"] = "";
			$addons[$i]["excerpt"] = "Want to add all the plugins I have created to extend Woocommerce Gift Cards this is the right product to choose.";
			$addons[$i]["link"] = "https://wp-ronin.com/downloads/gift-card-premium-upgrade/";
			$i++;
			


			
			foreach ( $addons as $addon ) {
				echo '<li class="product" style="float:left; margin:0 1em 1em 0 !important; padding:0; vertical-align:top; width:300px;">';
				echo '<a href="' . $addon['link'] . '">';
				if ( ! empty( $addon['image'] ) ) {
					echo '<img src="' . $addon['image'] . '"/>';
				} else {
					echo '<h3>' . $addon['title'] . '</h3>';
				}
				echo '<p>' . $addon['excerpt'] . '</p>';
				echo '</a>';
				echo '</li>';
			}
		?>
		</ul>
		</div>
		<?php
	}

}


return new RPGC_Settings();
