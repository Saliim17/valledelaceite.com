<?php
namespace AIOSEO\Plugin\Common\Api;

/**
 * Route class for the API.
 *
 * @since 4.0.0
 */
class Tags {
	/**
	 * Get all Tags.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function getTags() {
		return new \WP_REST_Response( [
			'tags' => aioseo()->tags->all( true )
		], 200 );
	}
}