<?php
namespace AIOSEO\Plugin\Common\Api;

use AIOSEO\Plugin\Common\Migration as CommonMigration;
use AIOSEO\Plugin\Common\Models;

/**
 * Route class for the API.
 *
 * @since 4.0.6
 */
class Migration {

	/**
	 * Resets blank title formats and retriggers the post/term meta migration.
	 *
	 * @since 4.0.6
	 *
	 * @return \WP_REST_Response The response.
	 */
	public static function fixBlankFormats() {
		$oldOptions = ( new CommonMigration\OldOptions() )->oldOptions;
		if ( ! $oldOptions ) {
			return new \WP_REST_Response( [
				'success' => true,
				'message' => 'Could not load v3 options.'
			], 400 );
		}

		$postTypes  = aioseo()->helpers->getPublicPostTypes( true );
		$taxonomies = aioseo()->helpers->getPublicTaxonomies( true );
		foreach ( $oldOptions as $k => $v ) {
			if ( ! preg_match( '/^aiosp_([a-zA-Z]*)_title_format$/', $k, $match ) || ! empty( $v ) ) {
				continue;
			}

			$objectName = $match[1];
			if ( in_array( $objectName, $postTypes, true ) && aioseo()->options->searchAppearance->dynamic->postTypes->has( $objectName ) ) {
				aioseo()->options->searchAppearance->dynamic->postTypes->$objectName->title = '#post_title #separator_sa #site_title';
				continue;
			}

			if ( in_array( $objectName, $taxonomies, true ) && aioseo()->options->searchAppearance->dynamic->taxonomies->has( $objectName ) ) {
				aioseo()->options->searchAppearance->dynamic->taxonomies->$objectName->title = '#taxonomy_title #separator_sa #site_title';
			}
		}

		aioseo()->migration->redoMetaMigration();

		Models\Notification::deleteNotificationByName( 'v3-migration-title-formats-blank' );

		return new \WP_REST_Response( [
			'success'       => true,
			'message'       => 'Title formats have been reset; post/term migration has been scheduled.',
			'notifications' => [
				'active'    => Models\Notification::getAllActiveNotifications(),
				'dismissed' => Models\Notification::getAllDismissedNotifications()
			]
		], 200 );
	}
}