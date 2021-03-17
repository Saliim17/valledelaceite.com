<?php
namespace AIOSEO\Plugin\Common\ImportExport\YoastSeo;

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates the Social Meta.
 *
 * @since 4.0.0
 */
class SocialMeta {

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->options = get_option( 'wpseo_social' );

		if ( empty( $this->options ) ) {
			return;
		}

		$this->migrateSocialUrls();
		$this->migrateFacebookSettings();
		$this->migrateTwitterSettings();
		$this->migrateFacebookAdminId();

		$settings = [
			'pinterestverify' => [ 'type' => 'string', 'newOption' => [ 'webmasterTools', 'pinterest' ] ]
		];

		aioseo()->importExport->yoastSeo->helpers->mapOldToNew( $settings, $this->options );
	}

	private function migrateSocialUrls() {
		$settings = [
			'facebook_site' => [ 'type' => 'string', 'newOption' => [ 'social', 'profiles', 'urls', 'facebookPageUrl' ] ],
			'instagram_url' => [ 'type' => 'string', 'newOption' => [ 'social', 'profiles', 'urls', 'instagramUrl' ] ],
			'linkedin_url'  => [ 'type' => 'string', 'newOption' => [ 'social', 'profiles', 'urls', 'linkedinUrl' ] ],
			'myspace_url'   => [ 'type' => 'string', 'newOption' => [ 'social', 'profiles', 'urls', 'myspaceUrl' ] ],
			'pinterest_url' => [ 'type' => 'string', 'newOption' => [ 'social', 'profiles', 'urls', 'pinterestUrl' ] ],
			'youtube_url'   => [ 'type' => 'string', 'newOption' => [ 'social', 'profiles', 'urls', 'youtubeUrl' ] ],
			'wikipedia_url' => [ 'type' => 'string', 'newOption' => [ 'social', 'profiles', 'urls', 'wikipediaUrl' ] ]
		];

		if ( ! empty( $this->options['twitter_site'] ) ) {
			aioseo()->options->social->profiles->urls->twitterUrl =
				'https://twitter.com/' . aioseo()->helpers->sanitizeOption( $this->options['twitter_site'] );
		}

		aioseo()->importExport->yoastSeo->helpers->mapOldToNew( $settings, $this->options );
	}

	/**
	 * Migrates the Facebook settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateFacebookSettings() {
		if ( ! empty( $this->options['og_default_image'] ) ) {
			$defaultImage = esc_url( $this->options['og_default_image'] );
			aioseo()->options->social->facebook->general->defaultImagePosts       = $defaultImage;
			aioseo()->options->social->facebook->general->defaultImageSourcePosts = 'default';

			aioseo()->options->social->twitter->general->defaultImagePosts       = $defaultImage;
			aioseo()->options->social->twitter->general->defaultImageSourcePosts = 'default';
		}

		$settings = [
			'opengraph'          => [ 'type' => 'boolean', 'newOption' => [ 'social', 'facebook', 'general', 'enable' ] ],
			'og_frontpage_title' => [ 'type' => 'string', 'newOption' => [ 'social', 'facebook', 'homePage', 'title' ] ],
			'og_frontpage_desc'  => [ 'type' => 'string', 'newOption' => [ 'social', 'facebook', 'homePage', 'description' ] ],
			'og_frontpage_image' => [ 'type' => 'string', 'newOption' => [ 'social', 'facebook', 'homePage', 'image' ] ],
		];

		aioseo()->importExport->yoastSeo->helpers->mapOldToNew( $settings, $this->options, true );
	}

	/**
	 * Migrates the Twitter settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateTwitterSettings() {
		$settings = [
			'twitter'           => [ 'type' => 'boolean', 'newOption' => [ 'social', 'twitter', 'general', 'enable' ] ],
			'twitter_card_type' => [ 'type' => 'string', 'newOption' => [ 'social', 'twitter', 'general', 'defaultCardType' ] ],
		];

		aioseo()->importExport->yoastSeo->helpers->mapOldToNew( $settings, $this->options );
	}

	/**
	 * Migrates the Facebook admin ID.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function migrateFacebookAdminId() {
		if ( ! empty( $this->options['fbadminapp'] ) ) {
			aioseo()->options->social->facebook->advanced->enable = true;
			aioseo()->options->social->facebook->advanced->adminId = aioseo()->helpers->sanitizeOption( $this->options['fbadminapp'] );
		}
	}
}