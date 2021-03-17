<?php
namespace AIOSEO\Plugin\Common\Api;

/**
 * Route class for the API.
 *
 * @since 4.0.0
 */
class Ping {
	/**
	 * Returns a success if the API is alive.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function ping() {
		return new \WP_REST_Response( [
			'success' => true
		], 200 );
	}
}