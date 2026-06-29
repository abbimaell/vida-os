<?php
/**
 * Defines the hook loader for Vida OS.
 *
 * @package Vida_OS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers WordPress hooks for the plugin.
 */
class Vida_OS_Loader {

	/**
	 * WordPress actions queued by the plugin.
	 *
	 * @var array<int, array{hook:string, component:object, callback:string, priority:int, accepted_args:int}>
	 */
	private array $actions = array();

	/**
	 * Adds an action to the internal hook registry.
	 *
	 * @param string $hook          WordPress action hook name.
	 * @param object $component     Instance that owns the callback.
	 * @param string $callback      Method name to call.
	 * @param int    $priority      Hook priority.
	 * @param int    $accepted_args Number of accepted callback arguments.
	 * @return void
	 */
	public function add_action(
		string $hook,
		object $component,
		string $callback,
		int $priority = 10,
		int $accepted_args = 1
	): void {
		$this->actions[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		);
	}

	/**
	 * Registers all queued hooks with WordPress.
	 *
	 * @return void
	 */
	public function run(): void {
		foreach ( $this->actions as $action ) {
			add_action(
				$action['hook'],
				array( $action['component'], $action['callback'] ),
				$action['priority'],
				$action['accepted_args']
			);
		}
	}
}
