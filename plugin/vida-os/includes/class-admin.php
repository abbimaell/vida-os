<?php
/**
 * Admin area setup for Vida OS.
 *
 * @package Vida_OS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles WordPress admin menus, screens, and assets.
 */
class Vida_OS_Admin {

	/**
	 * Main plugin file path.
	 *
	 * @var string
	 */
	private string $plugin_file;

	/**
	 * Current plugin version.
	 *
	 * @var string
	 */
	private string $version;

	/**
	 * Creates the admin service.
	 *
	 * @param string $plugin_file Absolute path to the main plugin file.
	 * @param string $version     Current plugin version.
	 */
	public function __construct( string $plugin_file, string $version ) {
		$this->plugin_file = $plugin_file;
		$this->version     = $version;
	}

	/**
	 * Registers the Vida OS main admin menu.
	 *
	 * @return void
	 */
	public function register_menu(): void {
		add_menu_page(
			__( 'Dashboard', 'vida-os' ),
			__( 'Vida OS', 'vida-os' ),
			'manage_options',
			'vida-os',
			array( $this, 'render_dashboard' ),
			'dashicons-admin-generic',
			26
		);
	}

	/**
	 * Enqueues admin assets only on Vida OS admin pages.
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 * @return void
	 */
	public function enqueue_assets( string $hook_suffix ): void {
		if ( 'toplevel_page_vida-os' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style(
			'vida-os-admin',
			plugin_dir_url( $this->plugin_file ) . 'assets/css/admin.css',
			array(),
			$this->version
		);

		wp_enqueue_script(
			'vida-os-admin',
			plugin_dir_url( $this->plugin_file ) . 'assets/js/admin.js',
			array(),
			$this->version,
			true
		);
	}

	/**
	 * Loads the dashboard template.
	 *
	 * @return void
	 */
	public function render_dashboard(): void {
		$template = plugin_dir_path( $this->plugin_file ) . 'templates/dashboard.php';

		if ( file_exists( $template ) ) {
			include $template;
		}
	}
}
