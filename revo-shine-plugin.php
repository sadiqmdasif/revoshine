<?php

/**
 * @wordpress-plugin
 *
 * Plugin Name:       RevoSHINE - Flutter Woocommerce Full App Manager
 * Plugin URI:        https://revoapps.net
 * Description:       Mobile App Management. Manage everything from WP-ADMIN.
 * Version:           9.0.1
 * Build              1
 * Author:            Revo Apps
 * Author URI:        https://revoapps.net
 * Text Domain:       revoshine
 * Domain Path:       /languages
 * Requires PHP:      8.0
 * Requires Plugins:  woocommerce
 * Build:             2021
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'REVO_SHINE_ABSPATH' ) ) {
	define( 'REVO_SHINE_ABSPATH', plugin_dir_path( __FILE__ ) );
	define( 'REVO_SHINE_URL', plugin_dir_url( __FILE__ ) );
	define( 'REVO_SHINE_PLUGIN_NAME', 'RevoSHINE' );
	define( 'REVO_SHINE_PLUGIN_VERSION', '9.0.0' );
	define( 'REVO_SHINE_PLUGIN_SLUG', 'revo-apps-setting' );
	define( 'REVO_SHINE_NAMESPACE_API', 'revo-admin/v1' );
	define( 'REVO_SHINE_ASSET_URL', REVO_SHINE_URL . 'assets/' );
	define( 'REVO_SHINE_TEMPLATE_PATH', REVO_SHINE_ABSPATH . 'templates/' );
}

if ( file_exists( REVO_SHINE_ABSPATH . 'vendor/scoper-autoload.php' ) ) {
	require_once REVO_SHINE_ABSPATH . 'vendor/scoper-autoload.php';
}

/**
 * load plugin functions
 */
require_once REVO_SHINE_ABSPATH . 'functions.php';

/**
 * boot Revo plugin
 */
function revo_shine_boot_plugin(): void {
	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	require_once REVO_SHINE_ABSPATH . 'includes/class-revo-shine-init.php';

	Revo_Shine_Init::instance();

	do_action( 'revo_shine_plugin_loaded' );
}

add_action( 'plugins_loaded', 'revo_shine_boot_plugin', 11 );

/**
 * plugin installation
 */
function revo_shine_activate_plugin(): void {
	require_once REVO_SHINE_ABSPATH . 'includes/class-revo-shine-install.php';

	Revo_Shine_Install::activator();
}

register_activation_hook( __FILE__, 'revo_shine_activate_plugin' );

/**
 * plugin deactivation
 */
function revo_shine_deactivate_plugin(): void {
	require_once REVO_SHINE_ABSPATH . 'includes/class-revo-shine-install.php';

	Revo_Shine_Install::deactivator();
}

register_deactivation_hook( __FILE__, 'revo_shine_deactivate_plugin' );
