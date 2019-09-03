<?php

/*
    Plugin Name: Disable cart page for WooCommerce
    Plugin URI: https://code4life.it/risorse/disable-cart-page-for-woocommerce/
    Description: Disable cart page and redirect to checkout for each purchase.
    Author: Code4Life
    Author URI: https://code4life.it/
    Version: 1.1.0
    Text Domain: wcdcp
 	Domain Path: /i18n/
	License: GPLv3
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Function to execute on plugin activation
register_activation_hook( __FILE__, function() {
	if ( ! current_user_can( 'activate_plugins' ) ) { return; }

    $plugin = isset( $_REQUEST[ 'plugin' ] ) ? $_REQUEST[ 'plugin' ] : null;
    check_admin_referer( 'activate-plugin_' . $plugin );

    /* Code here */
} );

// Function to execute on plugin deactivation
register_deactivation_hook( __FILE__, function() {
	if ( ! current_user_can( 'activate_plugins' ) ) { return; }

    $plugin = isset( $_REQUEST[ 'plugin' ] ) ? $_REQUEST[ 'plugin' ] : null;
    check_admin_referer( 'deactivate-plugin_' . $plugin );

    /* Code here */
} );

// Add language support to internationalize plugin
add_action( 'init', function() {
	load_plugin_textdomain( 'wcdcp', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/' );
} );

// Add link to configuration page into plugin
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function( $links ) {
	return array_merge( array(
		'view' => '<a href="' . admin_url( 'admin.php?page=wcdcp' ) . '">' . __( 'View', 'wcdcp' ) . '</a>'
	), $links );
} );

// Add checks and notices
add_action( 'admin_notices', function() {
	if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) && !defined('WC_PLUGIN_FILE') ) {
		?><div class="notice notice-error"><p><?php _e( 'Warning! To use Disable cart page for WooCommerce it need WooCommerce is installed and active.', 'wcdcp' ); ?></p></div><?php
	}
} );

// Remove WooCommerce cart options
update_option( 'woocommerce_cart_redirect_after_add', 'no' );
update_option( 'woocommerce_enable_ajax_add_to_cart', 'no' );

// Empty cart when product is added to cart, so we can't have multiple products in cart
add_action( 'woocommerce_add_cart_item_data', function() {
	wc_empty_cart();
} );

// When add a product to cart, redirect to checkout
add_action( 'woocommerce_init', function() {
	if ( version_compare( WC_VERSION, '3.0.0', '>' ) ) {
		add_filter ( 'add_to_cart_redirect', function() {
			// Remove messages
			wc_clear_notices();

			return wc_get_checkout_url();
		} );
	} else {
		add_filter ( 'woocommerce_add_to_cart_redirect', function() {
			// Remove messages
			wc_clear_notices();

			return wc_get_checkout_url();
		} );
	}
} );

// If someone reaches the cart page, redirect to checkout permanently
add_action( 'template_redirect', function() {
	if ( ! is_cart() ) { return; }

    // Redirect to checkout page
	wp_redirect( wc_get_checkout_url(), '301' );
	exit;
} );

// Change add to cart button text ( in loop )
add_filter( 'add_to_cart_text', function() {
	return __( 'Buy now', 'wcdcp' );
} );

// Change add to cart button text ( in product page )
add_filter( 'woocommerce_product_single_add_to_cart_text', function() {
	return __( 'Buy now', 'wcdcp' );
} );
