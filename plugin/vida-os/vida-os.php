<?php
/**
 * Plugin Name:       Vida OS
 * Plugin URI:        https://example.com/vida-os
 * Description:       Base architecture for the Vida OS WordPress plugin.
 * Version:           0.1.0
 * Requires at least: 7.0
 * Requires PHP:      8.3
 * Author:            Vida OS
 * Text Domain:       vida-os
 * Domain Path:       /languages
 *
 * @package Vida_OS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'VIDA_OS_VERSION' ) ) {
	/**
	 * Current plugin version.
	 */
	define( 'VIDA_OS_VERSION', '0.1.0' );
}

if ( ! defined( 'VIDA_OS_PLUGIN_FILE' ) ) {
	/**
	 * Absolute path to the main plugin file.
	 */
	define( 'VIDA_OS_PLUGIN_FILE', __FILE__ );
}

require_once plugin_dir_path( VIDA_OS_PLUGIN_FILE ) . 'includes/class-loader.php';
require_once plugin_dir_path( VIDA_OS_PLUGIN_FILE ) . 'includes/class-activator.php';
require_once plugin_dir_path( VIDA_OS_PLUGIN_FILE ) . 'includes/class-deactivator.php';
require_once plugin_dir_path( VIDA_OS_PLUGIN_FILE ) . 'includes/class-admin.php';
require_once plugin_dir_path( VIDA_OS_PLUGIN_FILE ) . 'modules/isd/class-assessment-service.php';
require_once plugin_dir_path( VIDA_OS_PLUGIN_FILE ) . 'modules/isd/class-rest-controller.php';

/**
 * Coordinates the plugin bootstrap process.
 */
final class Vida_OS {

	/**
	 * Starts the plugin.
	 *
	 * @return void
	 */
	public static function run(): void {
		self::register_lifecycle_hooks();
		self::register_plugin_hooks();
	}

	/**
	 * Registers activation and deactivation callbacks.
	 *
	 * @return void
	 */
	private static function register_lifecycle_hooks(): void {
		register_activation_hook( VIDA_OS_PLUGIN_FILE, array( 'VidaOS_Activator', 'activate' ) );
		register_deactivation_hook( VIDA_OS_PLUGIN_FILE, array( 'Vida_OS_Deactivator', 'deactivate' ) );
	}

	/**
	 * Registers plugin runtime hooks.
	 *
	 * @return void
	 */
	private static function register_plugin_hooks(): void {
		$loader          = new Vida_OS_Loader();
		$admin           = new Vida_OS_Admin( VIDA_OS_PLUGIN_FILE, VIDA_OS_VERSION );
		$rest_controller = new VidaOS_REST_Controller();

		$loader->add_action( 'admin_menu', $admin, 'register_menu' );
		$loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_assets' );
		$loader->add_action( 'rest_api_init', $rest_controller, 'register_routes', 10, 0 );
		$loader->run();
	}
}

Vida_OS::run();
