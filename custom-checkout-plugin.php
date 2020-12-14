<?php

use CustomCheckout\CustomCheckoutPlugin;

/**
 *
 * Plugin Name:       Custom Checkout Plugin
 * Plugin URI:        https://premmerce.com
 * Description:       
 * Version:           1.0
 * Author:            admin
 * Author URI:        https://premmerce.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       custom-checkout-plugin
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

call_user_func( function () {

	require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

	$main = new CustomCheckoutPlugin( __FILE__ );

	register_activation_hook( __FILE__, [ $main, 'activate' ] );

	register_deactivation_hook( __FILE__, [ $main, 'deactivate' ] );

	register_uninstall_hook( __FILE__, [ CustomCheckoutPlugin::class, 'uninstall' ] );

	$main->run();
} );