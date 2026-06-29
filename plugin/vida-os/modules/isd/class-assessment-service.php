<?php
/**
 * Assessment storage service for the ISD module.
 *
 * @package Vida_OS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stores ISD assessment submissions.
 */
class VidaOS_Assessment_Service {

	/**
	 * Saves an assessment and links it to a person.
	 *
	 * Expected data:
	 * - email: Person email address.
	 * - display_name: Optional person display name.
	 * - evaluation_period: Evaluation period identifier.
	 * - responses: Complete assessment responses.
	 *
	 * @param array<string, mixed> $assessment_data Raw assessment data.
	 * @return array<string, mixed> Structured success or error response.
	 */
	public static function save( array $assessment_data ): array {
		$normalized_data = self::normalize_assessment_data( $assessment_data );

		if ( is_wp_error( $normalized_data ) ) {
			return self::error_response(
				$normalized_data->get_error_code(),
				$normalized_data->get_error_message()
			);
		}

		$person_id = self::find_person_id_by_email( $normalized_data['email'] );

		if ( 0 === $person_id ) {
			$person_id = self::create_person(
				$normalized_data['email'],
				$normalized_data['display_name']
			);
		}

		if ( 0 === $person_id ) {
			return self::error_response(
				'person_not_saved',
				__( 'The person record could not be saved.', 'vida-os' )
			);
		}

		$assessment_id = self::create_assessment(
			$person_id,
			$normalized_data['evaluation_period'],
			$normalized_data['responses_json']
		);

		if ( 0 === $assessment_id ) {
			return self::error_response(
				'assessment_not_saved',
				__( 'The assessment record could not be saved.', 'vida-os' )
			);
		}

		return array(
			'success'       => true,
			'person_id'     => $person_id,
			'assessment_id' => $assessment_id,
		);
	}

	/**
	 * Normalizes and validates assessment input.
	 *
	 * @param array<string, mixed> $assessment_data Raw assessment data.
	 * @return array<string, string>|WP_Error Normalized data or validation error.
	 */
	private static function normalize_assessment_data( array $assessment_data ): array|WP_Error {
		$email = isset( $assessment_data['email'] )
			? sanitize_email( wp_unslash( (string) $assessment_data['email'] ) )
			: '';

		if ( '' === $email || ! is_email( $email ) ) {
			return new WP_Error(
				'invalid_email',
				__( 'A valid email address is required.', 'vida-os' )
			);
		}

		$display_name = isset( $assessment_data['display_name'] )
			? sanitize_text_field( wp_unslash( (string) $assessment_data['display_name'] ) )
			: '';

		if ( '' === $display_name ) {
			$display_name = $email;
		}

		$evaluation_period = isset( $assessment_data['evaluation_period'] )
			? sanitize_text_field( wp_unslash( (string) $assessment_data['evaluation_period'] ) )
			: '';

		if ( '' === $evaluation_period ) {
			return new WP_Error(
				'invalid_evaluation_period',
				__( 'An evaluation period is required.', 'vida-os' )
			);
		}

		if ( ! array_key_exists( 'responses', $assessment_data ) ) {
			return new WP_Error(
				'missing_responses',
				__( 'Assessment responses are required.', 'vida-os' )
			);
		}

		$responses_json = wp_json_encode( self::sanitize_response_value( $assessment_data['responses'] ) );

		if ( false === $responses_json ) {
			return new WP_Error(
				'invalid_responses',
				__( 'Assessment responses could not be encoded as JSON.', 'vida-os' )
			);
		}

		return array(
			'email'             => $email,
			'display_name'      => $display_name,
			'evaluation_period' => $evaluation_period,
			'responses_json'    => $responses_json,
		);
	}

	/**
	 * Finds an existing person by email address.
	 *
	 * @param string $email Sanitized email address.
	 * @return int Person ID, or 0 when not found.
	 */
	private static function find_person_id_by_email( string $email ): int {
		global $wpdb;

		$people_table = $wpdb->prefix . 'vidaos_people';
		$person_id    = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT person_id FROM {$people_table} WHERE email = %s LIMIT 1",
				$email
			)
		);

		return $person_id ? absint( $person_id ) : 0;
	}

	/**
	 * Creates a person record.
	 *
	 * @param string $email        Sanitized email address.
	 * @param string $display_name Sanitized display name.
	 * @return int Created person ID, or 0 on failure.
	 */
	private static function create_person( string $email, string $display_name ): int {
		global $wpdb;

		$people_table = $wpdb->prefix . 'vidaos_people';
		$inserted     = $wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$people_table} " .
				'(display_name, email, created_at) VALUES (%s, %s, %s)',
				$display_name,
				$email,
				current_time( 'mysql' )
			)
		);

		return false === $inserted ? 0 : absint( $wpdb->insert_id );
	}

	/**
	 * Creates an assessment record.
	 *
	 * @param int    $person_id         Person ID linked to the assessment.
	 * @param string $evaluation_period Sanitized evaluation period.
	 * @param string $responses_json    JSON-encoded assessment responses.
	 * @return int Created assessment ID, or 0 on failure.
	 */
	private static function create_assessment(
		int $person_id,
		string $evaluation_period,
		string $responses_json
	): int {
		global $wpdb;

		$assessments_table = $wpdb->prefix . 'vidaos_assessments';
		$inserted          = $wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$assessments_table} " .
				'(person_id, evaluation_period, responses_json, created_at) ' .
				'VALUES (%d, %s, %s, %s)',
				$person_id,
				$evaluation_period,
				$responses_json,
				current_time( 'mysql' )
			)
		);

		return false === $inserted ? 0 : absint( $wpdb->insert_id );
	}

	/**
	 * Sanitizes nested assessment responses before JSON encoding.
	 *
	 * @param mixed $value Raw response value.
	 * @return mixed Sanitized response value.
	 */
	private static function sanitize_response_value( mixed $value ): mixed {
		if ( is_array( $value ) ) {
			$sanitized = array();

			foreach ( $value as $response_key => $response_value ) {
				$key               = is_string( $response_key )
					? sanitize_text_field( wp_unslash( $response_key ) )
					: $response_key;
				$sanitized[ $key ] = self::sanitize_response_value( $response_value );
			}

			return $sanitized;
		}

		if ( is_object( $value ) ) {
			return self::sanitize_response_value( get_object_vars( $value ) );
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
	 * Builds a structured error response.
	 *
	 * @param string $code    Machine-readable error code.
	 * @param string $message Human-readable error message.
	 * @return array<string, mixed> Structured error response.
	 */
	private static function error_response( string $code, string $message ): array {
		return array(
			'success' => false,
			'error'   => array(
				'code'    => $code,
				'message' => $message,
			),
		);
	}
}
