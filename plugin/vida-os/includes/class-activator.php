<?php
/**
 * Activation routines for Vida OS.
 *
 * @package Vida_OS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Runs plugin activation tasks.
 */
class VidaOS_Activator {

	/**
	 * Current database schema version.
	 *
	 * Future migrations should increment this value and add a dedicated
	 * migration method.
	 */
	private const DB_VERSION = '1.1.0';

	/**
	 * WordPress option used to store the installed database schema version.
	 */
	private const DB_VERSION_OPTION = 'vidaos_db_version';

	/**
	 * Handles plugin activation.
	 *
	 * @return void
	 */
	public static function activate(): void {
		self::run_migrations();
	}

	/**
	 * Runs pending database migrations.
	 *
	 * @return void
	 */
	private static function run_migrations(): void {
		$installed_version = (string) get_option( self::DB_VERSION_OPTION, '0.0.0' );

		if ( version_compare( $installed_version, self::DB_VERSION, '<' ) ) {
			self::create_or_update_tables();
		}

		update_option( self::DB_VERSION_OPTION, self::DB_VERSION, false );
	}

	/**
	 * Creates or updates the Vida OS database tables.
	 *
	 * @return void
	 */
	private static function create_or_update_tables(): void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate   = $wpdb->get_charset_collate();
		$people_table      = $wpdb->prefix . 'vidaos_people';
		$assessments_table = $wpdb->prefix . 'vidaos_assessments';

		$people_schema = "CREATE TABLE {$people_table} (
			person_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			display_name varchar(191) NOT NULL DEFAULT '',
			email varchar(191) DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT NULL,
			PRIMARY KEY  (person_id),
			KEY email (email)
		) {$charset_collate};";

		$assessments_schema = "CREATE TABLE {$assessments_table} (
			assessment_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			person_id bigint(20) unsigned NOT NULL,
			evaluation_period varchar(32) NOT NULL DEFAULT '',
			responses_json longtext NOT NULL,
			status varchar(32) NOT NULL DEFAULT 'draft',
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT NULL,
			PRIMARY KEY  (assessment_id),
			KEY person_id (person_id),
			KEY evaluation_period (evaluation_period),
			KEY person_period (person_id, evaluation_period)
		) {$charset_collate};";

		dbDelta(
			array(
				$people_schema,
				$assessments_schema,
			)
		);
	}
}
