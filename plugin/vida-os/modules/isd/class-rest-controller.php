<?php
/**
 * REST controller for the ISD module.
 *
 * @package Vida_OS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and handles ISD REST routes.
 */
class VidaOS_REST_Controller {

	/**
	 * REST API namespace.
	 */
	private const NAMESPACE = 'vida-os/v1';

	/**
	 * Assessment route path.
	 */
	private const ASSESSMENT_ROUTE = '/assessment';

	/**
	 * Registers ISD REST routes.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			self::ASSESSMENT_ROUTE,
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'save_assessment' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Receives an assessment payload and delegates storage to the service.
	 *
	 * @param WP_REST_Request $request REST request object.
	 * @return WP_REST_Response REST response object.
	 */
	public function save_assessment( WP_REST_Request $request ): WP_REST_Response {
		if ( 'POST' !== $request->get_method() ) {
			return $this->error_response(
				__( 'Method not allowed.', 'vida-os' ),
				405
			);
		}

		$payload = $this->get_json_payload( $request );

		if ( is_wp_error( $payload ) ) {
			return $this->error_response(
				$payload->get_error_message(),
				400
			);
		}

		$result = VidaOS_Assessment_Service::save( $this->sanitize_payload( $payload ) );

		if ( true === ( $result['success'] ?? false ) ) {
			return new WP_REST_Response(
				$result,
				201
			);
		}

		return $this->error_response(
			$this->get_service_error_message( $result ),
			422
		);
	}

	/**
	 * Decodes and validates the request JSON body.
	 *
	 * @param WP_REST_Request $request REST request object.
	 * @return array<string, mixed>|WP_Error Decoded payload or validation error.
	 */
	private function get_json_payload( WP_REST_Request $request ): array|WP_Error {
		$body = trim( $request->get_body() );

		if ( '' === $body ) {
			return new WP_Error(
				'empty_json_body',
				__( 'JSON body is required.', 'vida-os' )
			);
		}

		$payload = json_decode( $body, true );

		if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $payload ) ) {
			return new WP_Error(
				'invalid_json',
				__( 'Invalid JSON payload.', 'vida-os' )
			);
		}

		return $payload;
	}

	/**
	 * Sanitizes a nested payload before it reaches the assessment service.
	 *
	 * @param mixed $value Raw payload value.
	 * @return mixed Sanitized payload value.
	 */
	private function sanitize_payload( mixed $value ): mixed {
		if ( is_array( $value ) ) {
			$sanitized = array();

			foreach ( $value as $payload_key => $payload_value ) {
				$key               = is_string( $payload_key )
					? sanitize_text_field( wp_unslash( $payload_key ) )
					: $payload_key;
				$sanitized[ $key ] = $this->sanitize_payload( $payload_value );
			}

			return $sanitized;
		}

		if ( is_string( $value ) ) {
			return sanitize_textarea_field( wp_unslash( $value ) );
		}

		if ( is_bool( $value ) || is_int( $value ) || is_float( $value ) || is_null( $value ) ) {
			return $value;
		}

		return sanitize_text_field( wp_unslash( (string) $value ) );
	}

	/**
	 * Extracts a user-facing message from a service response.
	 *
	 * @param array<string, mixed> $result Service response.
	 * @return string Error message.
	 */
	private function get_service_error_message( array $result ): string {
		if (
			isset( $result['error'] )
			&& is_array( $result['error'] )
			&& isset( $result['error']['message'] )
			&& is_string( $result['error']['message'] )
		) {
			return $result['error']['message'];
		}

		return __( 'The assessment could not be saved.', 'vida-os' );
	}

	/**
	 * Builds a standard REST error response.
	 *
	 * @param string $message Error message.
	 * @param int    $status  HTTP status code.
	 * @return WP_REST_Response REST response object.
	 */
	private function error_response( string $message, int $status ): WP_REST_Response {
		return new WP_REST_Response(
			array(
				'success' => false,
				'message' => $message,
			),
			$status
		);
	}
}
