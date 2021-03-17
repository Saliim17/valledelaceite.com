<?php
namespace AIOSEO\Plugin\Common\Api;

use AIOSEO\Plugin\Common\Models;

/**
 * Route class for the API.
 *
 * @since 4.0.0
 */
class Settings {
	/**
	 * Update the settings.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function getOptions() {
		return new \WP_REST_Response( [
			'options'  => aioseo()->options->all(),
			'settings' => aioseo()->settings->all()
		], 200 );
	}

	/**
	 * Toggles a card in the settings.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function toggleCard( $request ) {
		$body  = $request->get_json_params();
		$card  = ! empty( $body['card'] ) ? sanitize_text_field( $body['card'] ) : null;
		$cards = aioseo()->settings->toggledCards;
		if ( array_key_exists( $card, $cards ) ) {
			$cards[ $card ] = ! $cards[ $card ];
			aioseo()->settings->toggledCards = $cards;
		}

		return new \WP_REST_Response( [
			'success' => true
		], 200 );
	}

	/**
	 * Toggles a radio in the settings.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function toggleRadio( $request ) {
		$body   = $request->get_json_params();
		$radio  = ! empty( $body['radio'] ) ? sanitize_text_field( $body['radio'] ) : null;
		$value  = ! empty( $body['value'] ) ? sanitize_text_field( $body['value'] ) : null;
		$radios = aioseo()->settings->toggledRadio;
		if ( array_key_exists( $radio, $radios ) ) {
			$radios[ $radio ] = $value;
			aioseo()->settings->toggledRadio = $radios;
		}

		return new \WP_REST_Response( [
			'success' => true
		], 200 );
	}

	/**
	 * Dismisses the upgrade bar.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function hideUpgradeBar() {
		aioseo()->settings->showUpgradeBar = false;

		return new \WP_REST_Response( [
			'success' => true
		], 200 );
	}

	/**
	 * Hides the Setup Wizard CTA.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function hideSetupWizard() {
		aioseo()->settings->showSetupWizard = false;

		return new \WP_REST_Response( [
			'success' => true
		], 200 );
	}

	/**
	 * Save options from the front end.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function saveChanges( $request ) {
		$body    = $request->get_json_params();
		$options = ! empty( $body['options'] ) ? $body['options'] : [];
		$network = ! empty( $body['network'] ) ? (bool) $body['network'] : false;

		// If this is the network admin, reset the options.
		if ( $network ) {
			aioseo()->options->initNetwork();
		}

		aioseo()->options->sanitizeAndSave( $options );

		// Re-initialize notices.
		aioseo()->notices->init();

		return new \WP_REST_Response( [
			'success'       => true,
			'notifications' => [
				'active'    => Models\Notification::getAllActiveNotifications(),
				'dismissed' => Models\Notification::getAllDismissedNotifications()
			]
		], 200 );
	}

	/**
	 * Reset settings.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response The response.
	 */
	public static function resetSettings( $request ) {
		$body     = $request->get_json_params();
		$settings = ! empty( $body['settings'] ) ? $body['settings'] : [];

		foreach ( $settings as $setting ) {
			switch ( $setting ) {
				case 'webmaster-tools':
					aioseo()->options->webmasterTools->reset();
					break;
				case 'rss-content':
					aioseo()->options->rssContent->reset();
					break;
				case 'search-appearance':
					aioseo()->options->searchAppearance->reset();
					break;
				case 'access-control':
					aioseo()->options->accessControl->reset();
					aioseo()->access->addCapabilities();
					break;
				case 'advanced':
					aioseo()->options->advanced->reset();
					break;
				case 'social-networks':
					aioseo()->options->social->reset();
					break;
				case 'sitemaps':
					aioseo()->options->sitemap->reset();
					break;
				case 'local-business-seo':
					aioseo()->options->localBusiness->reset();
					break;
				case 'robots-txt':
					aioseo()->options->tools->robots->reset();
					break;
				case 'bad-bot-blocker':
					aioseo()->options->deprecated->tools->blocker->reset();
					break;
				case 'image-seo':
					aioseo()->options->image->reset();
					break;
			}
		}

		return new \WP_REST_Response( [
			'success' => true,
			'options' => aioseo()->options->all()
		], 200 );
	}

	/**
	 * Import settings from external file.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function importSettings( $request ) {
		$file     = $request->get_file_params()['file'];
		$wpfs     = aioseo()->helpers->wpfs();
		$contents = @$wpfs->get_contents( $file['tmp_name'] );
		if ( ! empty( $file['type'] ) && 'application/json' === $file['type'] ) {
			// Since this could be any file, we need to pretend like every variable here is missing.
			$contents = json_decode( $contents, true );
			if ( empty( $contents ) ) {
				return new \WP_REST_Response( [
					'success' => false
				], 400 );
			}

			$imported = false;
			if ( ! empty( $contents['settings'] ) ) {
				$imported = true;
				aioseo()->options->sanitizeAndSave( $contents['settings'] );
			}

			if ( ! empty( $contents['postOptions'] ) ) {
				$imported = true;
				foreach ( $contents['postOptions'] as $postType => $postData ) {
					// Posts.
					if ( ! empty( $postData['posts'] ) ) {
						foreach ( $postData['posts'] as $post ) {
							unset( $post['id'] );
							$thePost = Models\Post::getPost( $post['post_id'] );
							$thePost->set( $post );
							$thePost->save();
						}
					}
				}
			}

			if ( ! $imported ) {
				return new \WP_REST_Response( [
					'success' => false
				], 400 );
			}
		}

		if ( ! empty( $file['type'] ) && 'application/octet-stream' === $file['type'] ) {
			$response = aioseo()->importExport->importIniData( $contents );
			if ( ! $response ) {
				return new \WP_REST_Response( [
					'success' => false
				], 400 );
			}
		}

		return new \WP_REST_Response( [
			'success' => true,
			'options' => aioseo()->options->all()
		], 200 );
	}

	/**
	 * Export settings.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function exportSettings( $request ) {
		$body        = $request->get_json_params();
		$settings    = ! empty( $body['settings'] ) ? $body['settings'] : [];
		$postOptions = ! empty( $body['postOptions'] ) ? $body['postOptions'] : [];
		$allSettings = [
			'settings'    => [],
			'postOptions' => []
		];

		if ( ! empty( $settings ) ) {
			$options = aioseo()->options->noConflict();
			foreach ( $settings as $setting ) {
				if ( $options->has( $setting ) ) {
					$allSettings['settings'][ $setting ] = $options->$setting->all();
				}
			}
		}

		if ( ! empty( $postOptions ) ) {
			foreach ( $postOptions as $postType ) {
				$allSettings['postOptions'][ $postType ] = [
					'posts' => aioseo()->db->start( 'aioseo_posts as ap' )
						->select( 'ap.*' )
						->join( 'posts as p', 'ap.post_id = p.ID' )
						->where( 'p.post_type', $postType )
						->run()
						->result()
				];
			}
		}

		return new \WP_REST_Response( [
			'success'  => true,
			'settings' => $allSettings
		], 200 );
	}

	/**
	 * Import other plugin settings.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function importPlugins( $request ) {
		$body     = $request->get_json_params();
		$plugins  = ! empty( $body['plugins'] ) ? $body['plugins'] : [];

		foreach ( $plugins as $plugin ) {
			aioseo()->importExport->startImport( $plugin['plugin'], $plugin['settings'] );
		}

		return new \WP_REST_Response( [
			'success' => true
		], 200 );
	}
}