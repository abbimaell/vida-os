<?php
/**
 * Uninstall handler for Vida OS.
 *
 * @package Vida_OS
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/*
 * Database tables are intentionally preserved until Vida OS defines a formal
 * data retention and deletion policy.
 */
