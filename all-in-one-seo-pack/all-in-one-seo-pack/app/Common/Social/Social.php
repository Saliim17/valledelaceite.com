<?php
namespace AIOSEO\Plugin\Common\Social;

/**
 * Handles the Social Meta.
 *
 * @package AIOSEO\Plugin\Common\Social
 *
 * @since 4.0.0
 */
class Social {


	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->image = new Image();

		if ( wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}

		$this->facebook = new Facebook();
		$this->twitter  = new Twitter();
		$this->output   = new Output();
		$this->helpers  = new Helpers();

		$this->hooks();
	}

	/**
	 * Registers our hooks.
	 *
	 * @since 4.0.0
	 */
	protected function hooks() {
		if ( ! is_admin() || wp_doing_ajax() ) {
			add_filter( 'language_attributes', [ $this, 'addAttributes' ] );
		}

		// To avoid duplicate sets of meta tags.
		add_filter( 'jetpack_enable_open_graph', '__return_false' );
		// Adds special filters.
		add_filter( 'user_contactmethods', [ $this, 'addContactMethods' ] );

		// @TODO: [V4+] Needs access token for authentication.
		// Forces a refresh of the Facebook cache.
		//add_action( 'post_updated', [ $this, 'forceFbRefreshCache' ], 10, 2 );
	}

	/**
	 * Returns the social media contact methods.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $contactMethods The contact methods.
	 * @return array $contactMethods The filtered contact methods.
	 */
	public function addContactMethods( $contactMethods ) {
		if ( aioseo()->options->social->twitter->general->enable && aioseo()->options->social->twitter->general->showAuthor ) {
			$contactMethods['aioseo_contact_methods_header'] = AIOSEO_PLUGIN_NAME;
			$contactMethods['aioseo_twitter']                = 'Twitter'; // @TODO: Will need to migrate these from old installs. `twitter` becomes `aioseo_twitter`
		}

		if ( aioseo()->options->social->facebook->general->enable && aioseo()->options->social->facebook->general->showAuthor ) {
			if ( ! isset( $contactMethods['aioseo_contact_methods_header'] ) ) {
				$contactMethods['aioseo_contact_methods_header'] = AIOSEO_PLUGIN_NAME;
			}
			$contactMethods['aioseo_facebook'] = 'Facebook'; // @TODO: Will need to migrate these from old installs. `facebook` becomes `aioseo_facebook`
		}
		return $contactMethods;
	}

	/**
	 * Adds our attributes to the registered language attributes.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $htmlTag The 'html' tag as a string.
	 * @return string          The filtered 'html' tag as a string.
	 */
	public function addAttributes( $htmlTag ) {
		// Avoid having duplicate meta tags.
		$type = aioseo()->social->facebook->getObjectType();
		if ( empty( $type ) ) {
			$type = 'website';
		}

		$schemaTypes = [
			'album'      => 'MusicAlbum',
			'article'    => 'Article',
			'bar'        => 'BarOrPub',
			'blog'       => 'Blog',
			'book'       => 'Book',
			'cafe'       => 'CafeOrCoffeeShop',
			'city'       => 'City',
			'country'    => 'Country',
			'episode'    => 'Episode',
			'food'       => 'FoodEvent',
			'game'       => 'Game',
			'hotel'      => 'Hotel',
			'landmark'   => 'LandmarksOrHistoricalBuildings',
			'movie'      => 'Movie',
			'product'    => 'Product',
			'profile'    => 'ProfilePage',
			'restaurant' => 'Restaurant',
			'school'     => 'School',
			'sport'      => 'SportsEvent',
			'website'    => 'WebSite',
		];

		if ( ! empty( $schemaTypes[ $type ] ) ) {
			$type = $schemaTypes[ $type ];
		} else {
			$type = 'WebSite';
		}

		$attributes = apply_filters( 'aioseo_opengraph_attributes', [ 'prefix="og: https://ogp.me/ns#"' ] );

		foreach ( $attributes as $attr ) {
			if ( strpos( $htmlTag, $attr ) === false ) {
				$htmlTag .= "\n\t$attr ";
			}
		}
		return $htmlTag;
	}

	/**
	 * Forces FaceBook Open Graph to refresh on update.
	 *
	 * @since 4.0.0
	 *
	 * @see https://developers.facebook.com/docs/sharing/opengraph/using-objects#update
	 *
	 * @param  int     $postId The post ID.
	 * @param  WP_Post $post   The post object.
	 * @return void
	 */
	public function forceFbRefreshCache( $postId, $post ) {
		if ( 'publish' !== $post->post_status ) {
			return;
		}

		$postUrl = urldecode( get_permalink( $postId ) );

		// @TODO: [V4+] Needs access token for authentication.
		$endpoint = sprintf(
			'https://graph.facebook.com/?%s',
			http_build_query(
				[
					'id'     => $postUrl,
					'scrape' => true,
				]
			)
		);
		wp_remote_post( $endpoint, [ 'blocking' => false ] );
	}
}