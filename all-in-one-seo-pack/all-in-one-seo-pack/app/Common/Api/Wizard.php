<?php
namespace AIOSEO\Plugin\Common\Api;

use AIOSEO\Plugin\Common\Models;

/**
 * Route class for the API.
 *
 * @since 4.0.0
 */
class Wizard {
	/**
	 * Save the wizard information.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function saveWizard( $request ) {
		$body    = $request->get_json_params();
		$section = ! empty( $body['section'] ) ? sanitize_text_field( $body['section'] ) : null;
		$wizard  = ! empty( $body['wizard'] ) ? $body['wizard'] : null;

		aioseo()->internalOptions->internal->wizard = wp_json_encode( $wizard );

		// Process the importers.
		if ( 'importers' === $section && ! empty( $wizard['importers'] ) ) {
			$importers = $wizard['importers'];

			try {
				foreach ( $importers as $plugin ) {
					aioseo()->importExport->startImport( $plugin, [
						'settings',
						'postMeta',
						'termMeta'
					] );
				}
			} catch ( \Exception $e ) {
				// Import failed. Let's create a notification but move on.
				$notification = Models\Notification::getNotificationByName( 'import-failed' );
				if ( ! $notification->exists() ) {
					Models\Notification::addNotification( [
						'slug'              => uniqid(),
						'notification_name' => 'install-mi',
						'title'             => __( 'SEO Plugin Import Failed', 'all-in-one-seo-pack' ),
						'content'           => __( 'Unfortunately, there was an error importing your SEO plugin settings. This could be due to an incompatibility in the version installed. Make sure you are on the latest version of the plugin and try again.', 'all-in-one-seo-pack' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
						'type'              => 'error',
						'level'             => [ 'all' ],
						'button1_label'     => __( 'Try Again', 'all-in-one-seo-pack' ),
						'button1_action'    => 'http://route#aioseo-tools&aioseo-scroll=aioseo-import-others&aioseo-highlight=aioseo-import-others:import-export',
						'start'             => gmdate( 'Y-m-d H:i:s' )
					] );
				}
			}
		}

		// Save the category section.
		if (
			( 'category' === $section || 'searchAppearance' === $section ) && // We allow the user to update the site title/description in search appearance.
			! empty( $wizard['category'] )
		) {
			$category = $wizard['category'];
			if ( ! empty( $category['category'] ) ) {
				aioseo()->internalOptions->internal->category = $category['category'];
			}

			if ( ! empty( $category['categoryOther'] ) ) {
				aioseo()->internalOptions->internal->categoryOther = $category['categoryOther'];
			}

			// If the home page is a static page, let's find and set that,
			// otherwise set our home page settings.
			$staticHomePage = 'page' === get_option( 'show_on_front' ) ? get_post( get_option( 'page_on_front' ) ) : null;
			if ( ! empty( $category['siteTitle'] ) ) {
				if ( $staticHomePage ) {
					$page        = Models\Post::getPost( $staticHomePage->ID );
					$page->title = $category['siteTitle'];
				} else {
					aioseo()->options->searchAppearance->global->siteTitle = $category['siteTitle'];
				}
			}

			if ( ! empty( $category['metaDescription'] ) ) {
				if ( $staticHomePage ) {
					$page              = Models\Post::getPost( $staticHomePage->ID );
					$page->description = $category['metaDescription'];
				} else {
					aioseo()->options->searchAppearance->global->metaDescription = $category['metaDescription'];
				}
			}
		}

		// Save the additional information section.
		if ( 'additionalInformation' === $section && ! empty( $wizard['additionalInformation'] ) ) {
			$additionalInformation = $wizard['additionalInformation'];
			if ( ! empty( $additionalInformation['siteRepresents'] ) ) {
				aioseo()->options->searchAppearance->global->schema->siteRepresents = $additionalInformation['siteRepresents'];
			}

			if ( ! empty( $additionalInformation['person'] ) ) {
				aioseo()->options->searchAppearance->global->schema->person = $additionalInformation['person'];
			}

			if ( ! empty( $additionalInformation['organizationName'] ) ) {
				aioseo()->options->searchAppearance->global->schema->organizationName = $additionalInformation['organizationName'];
			}

			if ( ! empty( $additionalInformation['phone'] ) ) {
				aioseo()->options->searchAppearance->global->schema->phone = $additionalInformation['phone'];
			}

			if ( ! empty( $additionalInformation['organizationLogo'] ) ) {
				aioseo()->options->searchAppearance->global->schema->organizationLogo = $additionalInformation['organizationLogo'];
			}

			if ( ! empty( $additionalInformation['personName'] ) ) {
				aioseo()->options->searchAppearance->global->schema->personName = $additionalInformation['personName'];
			}

			if ( ! empty( $additionalInformation['personLogo'] ) ) {
				aioseo()->options->searchAppearance->global->schema->personLogo = $additionalInformation['personLogo'];
			}

			if ( ! empty( $additionalInformation['contactType'] ) ) {
				aioseo()->options->searchAppearance->global->schema->contactType = $additionalInformation['contactType'];
			}

			if ( ! empty( $additionalInformation['contactManual'] ) ) {
				aioseo()->options->searchAppearance->global->schema->contactManual = $additionalInformation['contactManual'];
			}

			if ( ! empty( $additionalInformation['socialShareImage'] ) ) {
				aioseo()->options->social->facebook->general->defaultImagePosts = $additionalInformation['socialShareImage'];
				aioseo()->options->social->twitter->general->defaultImagePosts  = $additionalInformation['socialShareImage'];
			}

			if ( ! empty( $additionalInformation['social'] ) && ! empty( $additionalInformation['social']['profiles'] ) ) {
				$profiles = $additionalInformation['social']['profiles'];
				if ( ! empty( $profiles['sameUsername'] ) ) {
					$sameUsername = $profiles['sameUsername'];
					if ( isset( $sameUsername['enable'] ) ) {
						aioseo()->options->social->profiles->sameUsername->enable = $sameUsername['enable'];
					}

					if ( ! empty( $sameUsername['username'] ) ) {
						aioseo()->options->social->profiles->sameUsername->username = $sameUsername['username'];
					}

					if ( ! empty( $sameUsername['included'] ) ) {
						aioseo()->options->social->profiles->sameUsername->included = $sameUsername['included'];
					}
				}

				if ( ! empty( $profiles['urls'] ) ) {
					$urls = $profiles['urls'];
					if ( ! empty( $urls['facebookPageUrl'] ) ) {
						aioseo()->options->social->profiles->urls->facebookPageUrl = $urls['facebookPageUrl'];
					}

					if ( ! empty( $urls['twitterUrl'] ) ) {
						aioseo()->options->social->profiles->urls->twitterUrl = $urls['twitterUrl'];
					}

					if ( ! empty( $urls['instagramUrl'] ) ) {
						aioseo()->options->social->profiles->urls->instagramUrl = $urls['instagramUrl'];
					}

					if ( ! empty( $urls['pinterestUrl'] ) ) {
						aioseo()->options->social->profiles->urls->pinterestUrl = $urls['pinterestUrl'];
					}

					if ( ! empty( $urls['youtubeUrl'] ) ) {
						aioseo()->options->social->profiles->urls->youtubeUrl = $urls['youtubeUrl'];
					}

					if ( ! empty( $urls['linkedinUrl'] ) ) {
						aioseo()->options->social->profiles->urls->linkedinUrl = $urls['linkedinUrl'];
					}

					if ( ! empty( $urls['tumblrUrl'] ) ) {
						aioseo()->options->social->profiles->urls->tumblrUrl = $urls['tumblrUrl'];
					}

					if ( ! empty( $urls['yelpPageUrl'] ) ) {
						aioseo()->options->social->profiles->urls->yelpPageUrl = $urls['yelpPageUrl'];
					}

					if ( ! empty( $urls['soundCloudUrl'] ) ) {
						aioseo()->options->social->profiles->urls->soundCloudUrl = $urls['soundCloudUrl'];
					}

					if ( ! empty( $urls['wikipediaUrl'] ) ) {
						aioseo()->options->social->profiles->urls->wikipediaUrl = $urls['wikipediaUrl'];
					}

					if ( ! empty( $urls['myspaceUrl'] ) ) {
						aioseo()->options->social->profiles->urls->myspaceUrl = $urls['myspaceUrl'];
					}

					if ( ! empty( $urls['googlePlacesUrl'] ) ) {
						aioseo()->options->social->profiles->urls->googlePlacesUrl = $urls['googlePlacesUrl'];
					}
				}
			}

			return new \WP_REST_Response( [
				'success' => true
			], 200 );
		}

		// Save the features section.
		if ( 'features' === $section && ! empty( $wizard['features'] ) ) {
			$features = $wizard['features'];

			// Install MI.
			if ( in_array( 'analytics', $features, true ) ) {
				$cantInstall = false;
				$pluginData  = aioseo()->helpers->getPluginData();
				if ( ! $pluginData['miPro']['activated'] && ! $pluginData['miLite']['activated'] ) {
					if ( $pluginData['miPro']['installed'] ) {
						aioseo()->addons->installAddon( 'miPro' );

						// Stop the redirect from happening.
						delete_transient( '_monsterinsights_activation_redirect' );
					} else {
						if ( $pluginData['miPro']['installed'] || aioseo()->addons->canInstall() ) {
							aioseo()->addons->installAddon( 'miLite' );

							// Stop the redirect from happening.
							delete_transient( '_monsterinsights_activation_redirect' );
						} else {
							$cantInstall = true;
						}
					}
				}

				if ( $cantInstall ) {
					$notification = Models\Notification::getNotificationByName( 'install-mi' );
					if ( ! $notification->exists() ) {
						Models\Notification::addNotification( [
							'slug'              => uniqid(),
							'notification_name' => 'install-mi',
							'title'             => __( 'Install MonsterInsights', 'all-in-one-seo-pack' ),
							'content'           => sprintf(
								// Translators: 1 - The plugin short name ("AIOSEO").
								__( 'You selected to install the free MonsterInsights Analytics plugin during the setup of %1$s, but there was an issue during installation. Click below to manually install.', 'all-in-one-seo-pack' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
								AIOSEO_PLUGIN_SHORT_NAME
							),
							'type'              => 'info',
							'level'             => [ 'all' ],
							'button1_label'     => __( 'Install MonsterInsights', 'all-in-one-seo-pack' ),
							'button1_action'    => $pluginData['miLite']['wpLink'],
							'button2_label'     => __( 'Remind Me Later', 'all-in-one-seo-pack' ),
							'button2_action'    => 'http://action#notification/install-mi-reminder',
							'start'             => gmdate( 'Y-m-d H:i:s' )
						] );
					}
				}
			}
		}

		// Save the search appearance section.
		if ( 'searchAppearance' === $section && ! empty( $wizard['searchAppearance'] ) ) {
			$searchAppearance = $wizard['searchAppearance'];

			if ( isset( $searchAppearance['underConstruction'] ) ) {
				update_option( 'blog_public', ! $searchAppearance['underConstruction'] );
			}

			if (
				! empty( $searchAppearance['postTypes'] ) &&
				! empty( $searchAppearance['postTypes']['postTypes'] )
			) {
				// Robots.
				if ( ! empty( $searchAppearance['postTypes']['postTypes']['all'] ) ) {
					$options = aioseo()->options->noConflict();
					foreach ( aioseo()->helpers->getPublicPostTypes( true ) as $postType ) {
						if ( $options->searchAppearance->dynamic->postTypes->has( $postType ) ) {
							$options->searchAppearance->dynamic->postTypes->$postType->show                          = true;
							$options->searchAppearance->dynamic->postTypes->$postType->advanced->robotsMeta->default = true;
							$options->searchAppearance->dynamic->postTypes->$postType->advanced->robotsMeta->noindex = false;
						}
					}

					aioseo()->options->refresh();
				} else {
					$options = aioseo()->options->noConflict();
					foreach ( aioseo()->helpers->getPublicPostTypes( true ) as $postType ) {
						if ( $options->searchAppearance->dynamic->postTypes->has( $postType ) ) {
							if ( in_array( $postType, (array) $searchAppearance['postTypes']['postTypes']['included'], true ) ) {
								$options->searchAppearance->dynamic->postTypes->$postType->show                          = true;
								$options->searchAppearance->dynamic->postTypes->$postType->advanced->robotsMeta->default = true;
								$options->searchAppearance->dynamic->postTypes->$postType->advanced->robotsMeta->noindex = false;
							} else {
								$options->searchAppearance->dynamic->postTypes->$postType->show                          = false;
								$options->searchAppearance->dynamic->postTypes->$postType->advanced->robotsMeta->default = false;
								$options->searchAppearance->dynamic->postTypes->$postType->advanced->robotsMeta->noindex = true;
							}
						}
					}

					aioseo()->options->refresh();
				}

				// Sitemaps.
				if ( isset( $searchAppearance['postTypes']['postTypes']['all'] ) ) {
					aioseo()->options->sitemap->general->postTypes->all = $searchAppearance['postTypes']['postTypes']['all'];
				}

				if ( isset( $searchAppearance['postTypes']['postTypes']['included'] ) ) {
					aioseo()->options->sitemap->general->postTypes->included = $searchAppearance['postTypes']['postTypes']['included'];
				}
			}

			if ( isset( $searchAppearance['multipleAuthors'] ) ) {
				aioseo()->options->searchAppearance->archives->author->show                          = $searchAppearance['multipleAuthors'];
				aioseo()->options->searchAppearance->archives->author->advanced->robotsMeta->default = $searchAppearance['multipleAuthors'];
				aioseo()->options->searchAppearance->archives->author->advanced->robotsMeta->noindex = ! $searchAppearance['multipleAuthors'];
			}

			$options = aioseo()->options->noConflict();
			if ( isset( $searchAppearance['redirectAttachmentPages'] ) && $options->searchAppearance->dynamic->postTypes->has( 'attachment' ) ) {
				$options->searchAppearance->dynamic->postTypes->attachment->redirectAttachmentUrls = $searchAppearance['redirectAttachmentPages'] ? 'attachment' : 'disabled';
				aioseo()->options->refresh();
			}
		}

		// Save the smart recommendations section.
		if ( 'smartRecommendations' === $section && ! empty( $wizard['smartRecommendations'] ) ) {
			$smartRecommendations = $wizard['smartRecommendations'];
			if ( ! empty( $smartRecommendations['accountInfo'] ) && ! aioseo()->internalOptions->internal->siteAnalysis->connectToken ) {
				$url      = defined( 'AIOSEO_CONNECT_DIRECT_URL' ) ? AIOSEO_CONNECT_DIRECT_URL : 'https://aioseo.com/wp-json/aioseo-lite-connect/v1/connect/';
				$response = wp_remote_post( $url, [
					'headers' => [
						'Content-Type' => 'application/json'
					],
					'body'    => wp_json_encode( [
						'accountInfo' => $smartRecommendations['accountInfo'],
						'homeurl'     => home_url()
					] )
				] );

				$token = json_decode( wp_remote_retrieve_body( $response ) );
				if ( ! empty( $token->token ) ) {
					aioseo()->internalOptions->internal->siteAnalysis->connectToken = $token->token;
				}
			}
		}

		return new \WP_REST_Response( [
			'success' => true,
			'options' => aioseo()->options->all()
		], 200 );
	}
}