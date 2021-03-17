<?php
namespace AIOSEO\Plugin\Common\Utils;

use AIOSEO\Plugin\Common\Models;
use AIOSEO\Plugin\Common\Tools;

class Helpers {
	/**
	 * Whether or not we have a local connection.
	 *
	 * @since 4.0.0
	 *
	 * @var bool
	 */
	private static $connection = false;

	/**
	 * Generate a UTM URL from the url and medium/content passed in.
	 *
	 * @since 4.0.0
	 *
	 * @param  string      $url     The URL to parse.
	 * @param  string      $medium  The UTM medium parameter.
	 * @param  string|null $content The UTM content parameter or null.
	 * @return string               The new URL.
	 */
	public function utmUrl( $url, $medium, $content = null ) {
		// First, remove any existing utm parameters on the URL.
		$url = remove_query_arg( [
			'utm_source',
			'utm_medium',
			'utm_campaign',
			'utm_content'
		], $url );

		// Generate the new arguments.
		$args = [
			'utm_source'   => 'WordPress',
			'utm_campaign' => aioseo()->pro ? 'proplugin' : 'liteplugin',
			'utm_medium'   => $medium
		];

		// Content is not used by default.
		if ( $content ) {
			$args['utm_content'] = $content;
		}

		// Return the new URL.
		return esc_url( add_query_arg( $args, $url ) );
	}

	/**
	 * Returns the blog page.
	 *
	 * @since 4.0.0
	 *
	 * @return WP_Post The blog page.
	 */
	public function getBlogPage() {
		static $blogPage = null;
		if ( $blogPage ) {
			return $blogPage;
		}

		$isStaticHomepage = ( 'page' === get_option( 'show_on_front' ) );
		$pageForPosts     = (int) get_option( 'page_for_posts' );
		if ( $isStaticHomepage && $pageForPosts ) {
			$blogPage = get_post( $pageForPosts );
		}
		return $blogPage;
	}

	/**
	 * Returns the All in One SEO Logo
	 *
	 * @since 4.0.0
	 *
	 * @param  string $width     The width of the image.
	 * @param  string $height    The height of the image.
	 * @param  string $colorCode The color of the image.
	 * @return string            The logo as a string.
	 */
	public function logo( $width, $height, $colorCode ) {
		return '<svg viewBox="0 0 20 20" width="' . $width . '" height="' . $height . '" fill="none" xmlns="http://www.w3.org/2000/svg" class="aioseo-gear"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.98542 19.9708C15.5002 19.9708 19.9708 15.5002 19.9708 9.98542C19.9708 4.47063 15.5002 0 9.98542 0C4.47063 0 0 4.47063 0 9.98542C0 15.5002 4.47063 19.9708 9.98542 19.9708ZM8.39541 3.65464C8.26016 3.4485 8.0096 3.35211 7.77985 3.43327C7.51816 3.52572 7.26218 3.63445 7.01349 3.7588C6.79519 3.86796 6.68566 4.11731 6.73372 4.36049L6.90493 5.22694C6.949 5.44996 6.858 5.6763 6.68522 5.82009C6.41216 6.04734 6.16007 6.30426 5.93421 6.58864C5.79383 6.76539 5.57233 6.85907 5.35361 6.81489L4.50424 6.6433C4.26564 6.5951 4.02157 6.70788 3.91544 6.93121C3.85549 7.05738 3.79889 7.1862 3.74583 7.31758C3.69276 7.44896 3.64397 7.58105 3.59938 7.71369C3.52048 7.94847 3.61579 8.20398 3.81839 8.34133L4.53958 8.83027C4.72529 8.95617 4.81778 9.1819 4.79534 9.40826C4.75925 9.77244 4.76072 10.136 4.79756 10.4936C4.82087 10.7198 4.72915 10.9459 4.54388 11.0724L3.82408 11.5642C3.62205 11.7022 3.52759 11.9579 3.60713 12.1923C3.69774 12.4593 3.8043 12.7205 3.92615 12.9743C4.03313 13.1971 4.27749 13.3088 4.51581 13.2598L5.36495 13.0851C5.5835 13.0401 5.80533 13.133 5.94623 13.3093C6.16893 13.5879 6.42071 13.8451 6.6994 14.0756C6.87261 14.2188 6.96442 14.4448 6.92112 14.668L6.75296 15.5348C6.70572 15.7782 6.81625 16.0273 7.03511 16.1356C7.15876 16.1967 7.285 16.2545 7.41375 16.3086C7.54251 16.3628 7.67196 16.4126 7.80195 16.4581C8.18224 16.5912 8.71449 16.1147 9.108 15.7625C9.30205 15.5888 9.42174 15.343 9.42301 15.0798C9.42301 15.0784 9.42302 15.077 9.42302 15.0756L9.42301 13.6263C9.42301 13.6109 9.4236 13.5957 9.42476 13.5806C8.26248 13.2971 7.39838 12.2301 7.39838 10.9572V9.41823C7.39838 9.30125 7.49131 9.20642 7.60596 9.20642H8.32584V7.6922C8.32584 7.48312 8.49193 7.31364 8.69683 7.31364C8.90171 7.31364 9.06781 7.48312 9.06781 7.6922V9.20642H11.0155V7.6922C11.0155 7.48312 11.1816 7.31364 11.3865 7.31364C11.5914 7.31364 11.7575 7.48312 11.7575 7.6922V9.20642H12.4773C12.592 9.20642 12.6849 9.30125 12.6849 9.41823V10.9572C12.6849 12.2704 11.7653 13.3643 10.5474 13.6051C10.5477 13.6121 10.5478 13.6192 10.5478 13.6263L10.5478 15.0694C10.5478 15.3377 10.6711 15.5879 10.871 15.7622C11.2715 16.1115 11.8129 16.5837 12.191 16.4502C12.4527 16.3577 12.7086 16.249 12.9573 16.1246C13.1756 16.0155 13.2852 15.7661 13.2371 15.5229L13.0659 14.6565C13.0218 14.4334 13.1128 14.2071 13.2856 14.0633C13.5587 13.8361 13.8107 13.5792 14.0366 13.2948C14.177 13.118 14.3985 13.0244 14.6172 13.0685L15.4666 13.2401C15.7052 13.2883 15.9493 13.1756 16.0554 12.9522C16.1153 12.8261 16.1719 12.6972 16.225 12.5659C16.2781 12.4345 16.3269 12.3024 16.3714 12.1698C16.4503 11.935 16.355 11.6795 16.1524 11.5421L15.4312 11.0532C15.2455 10.9273 15.153 10.7015 15.1755 10.4752C15.2116 10.111 15.2101 9.74744 15.1733 9.38986C15.1499 9.16361 15.2417 8.93757 15.4269 8.811L16.1467 8.31927C16.3488 8.18126 16.4432 7.92558 16.3637 7.69115C16.2731 7.42411 16.1665 7.16292 16.0447 6.90915C15.9377 6.68638 15.6933 6.57462 15.455 6.62366L14.6059 6.79837C14.3873 6.84334 14.1655 6.75048 14.0246 6.57418C13.8019 6.29554 13.5501 6.03832 13.2714 5.80784C13.0982 5.6646 13.0064 5.43858 13.0497 5.2154L13.2179 4.34868C13.2651 4.10521 13.1546 3.85616 12.9357 3.74787C12.8121 3.68669 12.6858 3.62895 12.5571 3.5748C12.4283 3.52065 12.2989 3.47086 12.1689 3.42537C11.9388 3.34485 11.6884 3.44211 11.5538 3.64884L11.0746 4.38475C10.9513 4.57425 10.73 4.66862 10.5082 4.64573C10.1513 4.6089 9.79502 4.61039 9.44459 4.64799C9.22286 4.67177 9.00134 4.57818 8.87731 4.38913L8.39541 3.65464Z" fill="' . $colorCode . '" /></svg>'; // phpcs:ignore Generic.Files.LineLength.MaxExceeded
	}

	/**
	 * Returns Jed-formatted localization data. Added for backwards-compatibility.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $domain Translation domain.
	 * @return array          An array of information for the locale.
	 */
	public function getJedLocaleData( $domain ) {
		$translations = get_translations_for_domain( $domain );

		$locale = [
			'' => [
				'domain' => $domain,
				'lang'   => is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale(),
			],
		];

		if ( ! empty( $translations->headers['Plural-Forms'] ) ) {
			$locale['']['plural_forms'] = $translations->headers['Plural-Forms'];
		}

		foreach ( $translations->entries as $msgid => $entry ) {
			$locale[ $msgid ] = $entry->translations;
		}

		return $locale;
	}

	/**
	 * Checks whether current page supports meta.
	 *
	 * @since 4.0.0
	 *
	 * @return boolean Whether the current page supports meta.
	 */
	public function supportsMeta() {
		return ! is_date() && ! is_author() && ! is_search() && ! is_404();
	}

	/**
	 * Checks whether the current page is a taxonomy term archive.
	 *
	 * @since 4.0.0
	 *
	 * @return boolean Whether the current page is a taxonomy term archive.
	 */
	public function isTaxTerm() {
		$object = get_queried_object();
		return $object instanceof \WP_Term;
	}

	/**
	 * Checks whether the current page is a static one.
	 *
	 * @since 4.0.0
	 *
	 * @return boolean Whether the current page is a static one.
	 */
	public function isStaticPage() {
		return $this->isStaticHomePage() || $this->isStaticPostsPage() || $this->isWooCommerceShopPage();
	}

	/**
	 * Checks whether the current page is the static homepage.
	 *
	 * @since 4.0.0
	 *
	 * @param  mixed   $post Pass in an optional post to check if its the static home page.
	 * @return boolean       Whether the current page is the static homepage.
	 */
	public function isStaticHomePage( $post = null ) {
		static $isHomePage = null;
		if ( null !== $isHomePage ) {
			return $isHomePage;
		}

		$post = aioseo()->helpers->getPost( $post );
		return ( 'page' === get_option( 'show_on_front' ) && is_page() && ! empty( $post ) && (int) get_option( 'page_on_front' ) === $post->ID );
	}

	/**
	 * Checks whether the current page is the static posts page.
	 *
	 * @since 4.0.0
	 *
	 * @return boolean Whether the current page is the static posts page.
	 */
	public function isStaticPostsPage() {
		return is_home() && ( 0 !== (int) get_option( 'page_for_posts' ) );
	}

	/**
	 * Checks whether WooCommerce is active.
	 *
	 * @since 4.0.0
	 *
	 * @return boolean Whether WooCommerce is active.
	 */
	public function isWooCommerceActive() {
		return class_exists( 'woocommerce' );
	}

	/**
	 * Checks whether the queried object is the WooCommerce shop page.
	 *
	 * @since 4.0.0
	 *
	 * @return boolean
	 */
	public function isWooCommerceShopPage() {
		$screenCheck = true;
		if ( is_admin() && function_exists( 'get_current_screen' ) ) {
			$screen      = get_current_screen();
			$screenCheck = 'edit' !== $screen->base;
		}
		return $this->isWooCommerceActive() && function_exists( 'is_shop' ) && is_shop() && $screenCheck;
	}

	/**
	 * Checks whether BuddyPress is active.
	 *
	 * @since 4.0.0
	 *
	 * @return boolean
	 */
	public function isBuddyPressActive() {
		return class_exists( 'BuddyPress' );
	}

	/**
	 * Checks whether the queried object is a buddy press user page.
	 *
	 * @since 4.0.0
	 *
	 * @return boolean
	 */
	public function isBuddyPressUser() {
		return $this->isBuddyPressActive() && function_exists( 'bp_is_user' ) && bp_is_user();
	}

	/**
	 * Helper method to enqueue scripts.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $script The script to enqueue.
	 * @param  string $url    The URL of the script.
	 * @param  bool   $vue    Whether or not this is a vue script.
	 * @return void
	 */
	public function enqueueScript( $script, $url, $vue = true ) {
		if ( ! wp_script_is( $script, 'enqueued' ) ) {
			wp_enqueue_script(
				$script,
				$this->getScriptUrl( $url, $vue ),
				[],
				aioseo()->version,
				true
			);
		}
	}

	/**
	 * Helper method to enqueue stylesheets.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $style The stylesheet to enqueue.
	 * @param  string $url   The URL of the stylesheet.
	 * @param  bool   $vue    Whether or not this is a vue stylesheet.
	 * @return void
	 */
	public function enqueueStyle( $style, $url, $vue = true ) {
		if ( ! wp_style_is( $style, 'enqueued' ) && $this->shouldEnqueue( $url ) ) {
			wp_enqueue_style(
				$style,
				$this->getScriptUrl( $url, $vue ),
				[],
				aioseo()->version
			);
		}
	}

	/**
	 * Localizes a given URL.
	 *
	 * This is required for compatibility with WPML.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $path The relative path of the URL.
	 * @return string $url  The filtered URL.
	 */
	public function localizedUrl( $path ) {
		$url = apply_filters( 'wpml_home_url', home_url( '/' ) );

		// Remove URL parameters.
		preg_match_all( '/\?[\s\S]+/', $url, $matches );

		// Get the base URL.
		$url  = preg_replace( '/\?[\s\S]+/', '', $url );
		$url  = trailingslashit( $url );
		$url .= preg_replace( '/\//', '', $path, 1 );

		// Readd URL parameters.
		if ( $matches && $matches[0] ) {
			$url .= $matches[0][0];
		}

		return $url;
	}

	/**
	 * Unsets a given value in a given array.
	 *
	 * This function should only be used if the given value only appears once in the array.
	 *
	 * @since 4.0.0
	 *
	 * @param  array  $array The array.
	 * @param  string $value The value that needs to be removed from the array.
	 * @return array  $array The filtered array.
	 */
	public function unsetValue( $array, $value ) {
		if ( in_array( $value, $array, true ) ) {
			unset( $array[ array_search( $value, $array, true ) ] );
		};
		return $array;
	}

	/**
	 * Formats a timestamp as an ISO 8601 date.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $dateTime The raw datetime.
	 * @return string           The formatted datetime.
	 */
	public function formatDateTime( $dateTime ) {
		return gmdate( 'c', mysql2date( 'U', $dateTime ) );
	}

	/**
	 * Formats a given URl as an absolute URL if it is relative.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $url The URL.
	 * @return string $url The absolute URL.
	 */
	public function makeUrlAbsolute( $url ) {
		if ( 0 !== strpos( $url, 'http' ) && '/' !== $url ) {
			if ( 0 === strpos( $url, '//' ) ) {
				$scheme = wp_parse_url( home_url(), PHP_URL_SCHEME );
				$url    = $scheme . ':' . $url;
			} else {
				$url = home_url( $url );
			}
		}
		return $url;
	}

	/** Whether or not we should enqueue a file.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $url The url to check against.
	 * @return bool        Whether or not we should enqueue.
	 */
	private function shouldEnqueue( $url ) {
		$version  = strtoupper( aioseo()->versionPath );
		$host     = defined( 'AIOSEO_DEV_' . $version ) ? constant( 'AIOSEO_DEV_' . $version ) : false;

		if ( ! $host ) {
			return true;
		}

		if ( false !== strpos( $url, 'chunk-common.css' ) ) {
			// return false;
		}

		return true;
	}

	/**
	 * Retrieve the proper URL for this script or style.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $url The url.
	 * @param  bool   $vue Whether or not this is a vue script.
	 * @return string      The modified url.
	 */
	public function getScriptUrl( $url, $vue = true ) {
		$version  = strtoupper( aioseo()->versionPath );
		$host     = $vue && defined( 'AIOSEO_DEV_' . $version ) ? constant( 'AIOSEO_DEV_' . $version ) : false;
		$localUrl = $url;
		$url      = plugins_url( 'dist/' . aioseo()->versionPath . '/assets/' . $url, AIOSEO_FILE );

		if ( ! $host ) {
			return $url;
		}

		if ( $host && ! self::$connection ) {
			$splitHost        = explode( ':', str_replace( '/', '', str_replace( 'http://', '', $host ) ) );
			self::$connection = @fsockopen( $splitHost[0], $splitHost[1] ); // phpcs:ignore WordPress
		}

		if ( ! self::$connection ) {
			return $url;
		}

		return $host . $localUrl;
	}

	/**
	 * Convert to snake case.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $string The string to convert.
	 * @return string         The converted string.
	 */
	public function toSnakeCase( $string ) {
		$string[0] = strtolower( $string[0] );
		return preg_replace_callback( '/([A-Z])/', function ( $value ) {
			return '_' . strtolower( $value[1] );
		}, $string );
	}

	/**
	 * Convert to camel case.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $string     The string to convert.
	 * @param  bool   $capitalize Whether or not to capitalize the first letter.
	 * @return string             The converted string.
	 */
	public function toCamelCase( $string, $capitalize = false ) {
		if ( $capitalize ) {
			$string[0] = strtoupper( $string[0] );
		}
		return preg_replace_callback( '/_([a-z0-9])/', function ( $value ) {
			return strtoupper( $value[1] );
		}, $string );
	}

	/**
	 * Converts kebab case to camel case.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $string     The string to convert.
	 * @param  bool   $capitalize Whether or not to capitalize the first letter.
	 * @return string             The converted string.
	 */
	public function dashesToCamelCase( $string, $capitalizeFirstCharacter = false ) {
		$string = str_replace( ' ', '', ucwords( str_replace( '-', ' ', $string ) ) );
		if ( ! $capitalizeFirstCharacter ) {
			$string[0] = strtolower( $string[0] );
		}
		return $string;
	}

	/**
	 * Gets the data for vue.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $page The current page.
	 * @return array        An array of data.
	 */
	public function getVueData( $page = null ) {
		$postTypeObj = get_post_type_object( get_post_type( get_the_ID() ) );
		$screen      = get_current_screen();

		// Check if user has a custom filename from the V3 migration.
		$sitemapFilename = aioseo()->sitemap->helpers->filename( 'general' );
		$sitemapFilename = $sitemapFilename ? $sitemapFilename : 'sitemap';

		$isStaticHomePage = 'page' === get_option( 'show_on_front' );
		$staticHomePage   = intval( get_option( 'page_on_front' ) );
		$data = [
			'page'             => $page,
			'translations'     => $this->getJedLocaleData( 'all-in-one-seo-pack' ),
			'screen'           => [
				'base'        => $screen->base,
				'postType'    => $screen->post_type,
				'blockEditor' => isset( $screen->is_block_editor ) ? $screen->is_block_editor : false
			],
			'internalOptions'  => aioseo()->internalOptions->all(),
			'options'          => aioseo()->options->all(),
			'settings'         => aioseo()->settings->all(),
			'tags'             => aioseo()->tags->all( true ),
			'nonce'            => wp_create_nonce( 'wp_rest' ),
			'urls'             => [
				'domain'            => $this->getSiteDomain(),
				'mainSiteUrl'       => $this->getSiteUrl(),
				'home'              => home_url(),
				'restUrl'           => rest_url(),
				'publicPath'        => plugin_dir_url( AIOSEO_FILE ),
				'rssFeedUrl'        => get_bloginfo( 'rss2_url' ),
				'generalSitemapUrl' => home_url( "/$sitemapFilename.xml" ),
				'rssSitemapUrl'     => home_url( '/sitemap.rss' ),
				'robotsTxtUrl'      => $this->getSiteUrl() . '/robots.txt',
				'blockedBotsLogUrl' => wp_upload_dir()['baseurl'] . '/aioseo-logs/aioseo-bad-bot-blocker.log',
				'staticHomePage'    => 'page' === get_option( 'show_on_front' ) ? get_edit_post_link( get_option( 'page_on_front' ), 'url' ) : null,
				'connect'           => add_query_arg( [
					'siteurl'  => site_url(),
					'homeurl'  => home_url(),
					'redirect' => rawurldecode( base64_encode( admin_url( 'index.php?page=aioseo-connect' ) ) )
				], defined( 'AIOSEO_CONNECT_URL' ) ? AIOSEO_CONNECT_URL : 'https://connect.aioseo.com'),
				'aio'               => [
					'wizard'           => admin_url( 'index.php?page=aioseo-setup-wizard' ),
					'dashboard'        => admin_url( 'admin.php?page=aioseo' ),
					'settings'         => admin_url( 'admin.php?page=aioseo-settings' ),
					'localSeo'         => admin_url( 'admin.php?page=aioseo-local-seo' ),
					'featureManager'   => admin_url( 'admin.php?page=aioseo-feature-manager' ),
					'sitemaps'         => admin_url( 'admin.php?page=aioseo-sitemaps' ),
					'seoAnalysis'      => admin_url( 'admin.php?page=aioseo-seo-analysis' ),
					'searchAppearance' => admin_url( 'admin.php?page=aioseo-search-appearance' ),
					'socialNetworks'   => admin_url( 'admin.php?page=aioseo-social-networks' ),
					'tools'            => admin_url( 'admin.php?page=aioseo-tools' ),
					'monsterinsights'  => admin_url( 'admin.php?page=aioseo-monsterinsights' )
				]
			],
			'backups'          => [],
			'importers'        => [],
			'data'             => [
				'server'              => [
					'apache' => null,
					'nginx'  => null
				],
				'robots'              => [
					'defaultRules'      => [],
					'hasPhysicalRobots' => null,
					'rewriteExists'     => null,
					'sitemapUrls'       => []
				],
				'logSizes'            => [
					'badBotBlockerLog' => null
				],
				'status'              => [],
				'htaccess'            => '',
				'multisite'           => is_multisite(),
				'network'             => is_network_admin(),
				'mainSite'            => is_main_site(),
				'subdomain'           => defined( 'SUBDOMAIN_INSTALL' ) && SUBDOMAIN_INSTALL,
				'isWooCommerceActive' => $this->isWooCommerceActive(),
				'isBBPressActive'     => class_exists( 'bbPress' ),
				'staticHomePage'      => $isStaticHomePage ? $staticHomePage : false,
			],
			'user'             => [
				'email'          => wp_get_current_user()->user_email,
				'roles'          => $this->getUserRoles(),
				'capabilities'   => aioseo()->access->getAllCapabilities(),
				'unfilteredHtml' => current_user_can( 'unfiltered_html' ),
				'locale'         => function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale()
			],
			'plugins'          => $this->getPluginData(),
			'postData'         => [
				'postTypes'  => $this->getPublicPostTypes( false, false, true ),
				'taxonomies' => $this->getPublicTaxonomies( false, true ),
				'archives'   => $this->getPublicPostTypes( false, true, true )
			],
			'notifications'    => [
				'active'    => Models\Notification::getAllActiveNotifications(),
				'dismissed' => Models\Notification::getAllDismissedNotifications()
			],
			'addons'           => aioseo()->addons->getAddons(),
			'version'          => AIOSEO_VERSION,
			'helpPanel'        => json_decode( aioseo()->help->getDocs() ),
			'scheduledActions' => [
				'sitemaps' => []
			]
		];

		if ( is_multisite() && ! is_network_admin() ) {
			switch_to_blog( $this->getNetworkId() );
			$options = aioseo()->options->noConflict();
			$options->initNetwork();
			$data['networkOptions'] = $options->all();
			restore_current_blog();
		}

		if ( 'post' === $page ) {
			$postId              = get_the_ID();
			$post                = Models\Post::getPost( $postId );

			$data['currentPost'] = [
				'context'                     => 'post',
				'tags'                        => aioseo()->tags->getDefaultPostTags( $postId ),
				'id'                          => $postId,
				'priority'                    => ! empty( $post->priority ) ? $post->priority : 'default',
				'frequency'                   => ! empty( $post->frequency ) ? $post->frequency : 'default',
				'permalink'                   => get_the_permalink(),
				'title'                       => ! empty( $post->title ) ? $post->title : '',
				'description'                 => ! empty( $post->description ) ? $post->description : '',
				'keywords'                    => ! empty( $post->keywords ) ? $post->keywords : wp_json_encode( [] ),
				'keyphrases'                  => ! empty( $post->keyphrases )
					? json_decode( $post->keyphrases )
					: json_decode( '{"focus":{},"additional":[]}' ),
				'page_analysis'               => ! empty( $post->page_analysis )
					? json_decode( $post->page_analysis )
					: Models\Post::getPageAnalysisDefaults(),
				'loading'                     => [
					'focus'      => false,
					'additional' => [],
				],
				'type'                        => $postTypeObj->labels->singular_name,
				'postType'                    => 'type' === $postTypeObj->name ? '_aioseo_type' : $postTypeObj->name,
				'isSpecialPage'               => $this->isSpecialPage( $postId ),
				'isWooCommercePage'           => $this->isWooCommercePage( $postId ),
				'seo_score'                   => (int) $post->seo_score,
				'pillar_content'              => ( (int) $post->pillar_content ) === 0 ? false : true,
				'canonicalUrl'                => $post->canonical_url,
				'default'                     => ( (int) $post->robots_default ) === 0 ? false : true,
				'noindex'                     => ( (int) $post->robots_noindex ) === 0 ? false : true,
				'noarchive'                   => ( (int) $post->robots_noarchive ) === 0 ? false : true,
				'nosnippet'                   => ( (int) $post->robots_nosnippet ) === 0 ? false : true,
				'nofollow'                    => ( (int) $post->robots_nofollow ) === 0 ? false : true,
				'noimageindex'                => ( (int) $post->robots_noimageindex ) === 0 ? false : true,
				'noodp'                       => ( (int) $post->robots_noodp ) === 0 ? false : true,
				'notranslate'                 => ( (int) $post->robots_notranslate ) === 0 ? false : true,
				'maxSnippet'                  => null === $post->robots_max_snippet ? -1 : (int) $post->robots_max_snippet,
				'maxVideoPreview'             => null === $post->robots_max_videopreview ? -1 : (int) $post->robots_max_videopreview,
				'maxImagePreview'             => $post->robots_max_imagepreview,
				'modalOpen'                   => false,
				'tabs'                        => ( ! empty( $post->tabs ) )
					? json_decode( $post->tabs )
					: json_decode( Models\Post::getDefaultTabsOptions() ),
				'generalMobilePrev'           => false,
				'socialMobilePreview'         => false,
				'og_object_type'              => ! empty( $post->og_object_type ) ? $post->og_object_type : 'default',
				'og_title'                    => $post->og_title,
				'og_description'              => $post->og_description,
				'og_image_custom_url'         => $post->og_image_custom_url,
				'og_image_custom_fields'      => $post->og_image_custom_fields,
				'og_image_type'               => ! empty( $post->og_image_type ) ? $post->og_image_type : 'default',
				'og_video'                    => ! empty( $post->og_video ) ? $post->og_video : '',
				'og_article_section'          => ! empty( $post->og_article_section ) ? $post->og_article_section : '',
				'og_article_tags'             => ! empty( $post->og_article_tags ) ? $post->og_article_tags : wp_json_encode( [] ),
				'twitter_use_og'              => ( (int) $post->twitter_use_og ) === 0 ? false : true,
				'twitter_card'                => $post->twitter_card,
				'twitter_image_custom_url'    => $post->twitter_image_custom_url,
				'twitter_image_custom_fields' => $post->twitter_image_custom_fields,
				'twitter_image_type'          => $post->twitter_image_type,
				'twitter_title'               => $post->twitter_title,
				'twitter_description'         => $post->twitter_description,
				'schema_type'                 => ( ! empty( $post->schema_type ) ) ? $post->schema_type : null,
				'schema_type_options'         => ( ! empty( $post->schema_type_options ) )
					? json_decode( $post->schema_type_options )
					: json_decode( Models\Post::getDefaultSchemaOptions() ),
				'local_seo'                   => ( ! empty( $post->local_seo ) )
					? json_decode( $post->local_seo )
					: json_decode( Models\Post::getDefaultLocalSeoOptions() ),
				'metaDefaults'                => [
					'title'       => aioseo()->meta->title->getPostTypeTitle( $postTypeObj->name ),
					'description' => aioseo()->meta->description->getPostTypeDescription( $postTypeObj->name )
				]
			];

			if ( ! $post->exists() ) {
				$oldPostMeta = aioseo()->migration->meta->getMigratedPostMeta( $postId );
				foreach ( $oldPostMeta as $k => $v ) {
					if ( preg_match( '#robots_.*#', $k ) ) {
						$oldPostMeta[ preg_replace( '#robots_#', '', $k ) ] = $v;
						continue;
					}
					if ( 'canonical_url' === $k ) {
						$oldPostMeta['canonicalUrl'] = $v;
					}
				}
				$data['currentPost'] = array_merge( $data['currentPost'], $oldPostMeta );
			}
		}

		// @TODO: Contextualize all data attributes above to only show on pages that they are used on.

		if ( 'sitemaps' === $page ) {
			try {
				if ( as_next_scheduled_action( 'aioseo_static_sitemap_regeneration' ) ) {
					$data['scheduledActions']['sitemap'][] = 'staticSitemapRegeneration';
				}
			} catch ( \Exception $e ) {
				// Do nothing.
			}
		}

		if ( 'setup-wizard' === $page ) {
			$data['users']     = $this->getSiteUsers( [ 'administrator', 'editor', 'author' ] );
			$data['importers'] = aioseo()->importExport->plugins();
		}

		if ( 'search-appearance' === $page ) {
			$data['users'] = $this->getSiteUsers( [ 'administrator', 'editor', 'author' ] );
			$data['data'] += [
				'staticHomePageTitle'       => $isStaticHomePage ? aioseo()->meta->title->getTitle( $staticHomePage ) : '',
				'staticHomePageDescription' => $isStaticHomePage ? aioseo()->meta->description->getDescription( $staticHomePage ) : '',
			];
		}

		if ( 'social-networks' === $page ) {
			$data['data'] += [
				'staticHomePageOgTitle'            => $isStaticHomePage ? aioseo()->social->facebook->getTitle( $staticHomePage ) : '',
				'staticHomePageOgDescription'      => $isStaticHomePage ? aioseo()->social->facebook->getDescription( $staticHomePage ) : '',
				'staticHomePageTwitterTitle'       => $isStaticHomePage ? aioseo()->social->twitter->getTitle( $staticHomePage ) : '',
				'staticHomePageTwitterDescription' => $isStaticHomePage ? aioseo()->social->twitter->getDescription( $staticHomePage ) : '',
			];
		}

		if ( 'tools' === $page ) {
			$data['backups']        = array_reverse( aioseo()->backup->all() );
			$data['importers']      = aioseo()->importExport->plugins();
			$data['data']['server'] = [
				'apache' => $this->isApache(),
				'nginx'  => $this->isNginx()
			];
			$data['data']['robots'] = [
				'defaultRules'      => $page ? aioseo()->robotsTxt->getDefaultRules() : [],
				'hasPhysicalRobots' => aioseo()->robotsTxt->hasPhysicalRobotsTxt(),
				'rewriteExists'     => aioseo()->robotsTxt->rewriteRulesExist(),
				'sitemapUrls'       => array_merge( aioseo()->sitemap->helpers->getSitemapUrls(), $this->extractSitemapUrlsFromRobotsTxt() )
			];
			$data['data']['logSizes'] = [
				'badBotBlockerLog' => $this->convertFileSize( aioseo()->badBotBlocker->getLogSize() )
			];
			$data['data']['status']   = Tools\SystemStatus::getSystemStatusInfo();
			$data['data']['htaccess'] = aioseo()->htaccess->getContents();
		}

		return $data;
	}

	/**
	 * Returns user roles in the current WP install.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of user roles.
	 */
	public function getUserRoles() {
		global $wp_roles;
		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}
		$roleNames = $wp_roles->get_names();
		asort( $roleNames );

		return $roleNames;
	}

	/**
	 * Returns an array of plugins with the active status.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of plugins with active status.
	 */
	public function getPluginData() {
		$pluginUpgrader   = new PluginUpgraderSilentAjax();
		$installedPlugins = array_keys( get_plugins() );

		$plugins = [];
		foreach ( $pluginUpgrader->pluginSlugs as $key => $slug ) {
			$plugins[ $key ] = [
				'basename'   => $slug,
				'installed'  => in_array( $slug, $installedPlugins, true ),
				'activated'  => is_plugin_active( $slug ),
				'adminUrl'   => admin_url( $pluginUpgrader->pluginAdminUrls[ $key ] ),
				'canInstall' => aioseo()->addons->canInstall(),
				'wpLink'     => ! empty( $pluginUpgrader->wpPluginLinks[ $key ] ) ? $pluginUpgrader->wpPluginLinks[ $key ] : null
			];
		}

		return $plugins;
	}

	/**
	 * Retrieve a list of public post types with slugs/icons.
	 *
	 * @since 4.0.0
	 *
	 * @param  boolean $namesOnly       Whether only the names should be returned.
	 * @param  boolean $hasArchivesOnly Whether or not to only include post types which have archives.
	 * @param  boolean $rewriteType     Whether or not to rewrite the type slugs.
	 * @return array                    An array of public post types.
	 */
	public function getPublicPostTypes( $namesOnly = false, $hasArchivesOnly = false, $rewriteType = false ) {
		$postTypes   = [];
		$postObjects = get_post_types( [ 'public' => true ], 'objects' );
		$woocommerce = class_exists( 'woocommerce' );
		foreach ( $postObjects as $postObject ) {
			if ( empty( $postObject->label ) ) {
				continue;
			}

			// We don't want to include archives for the WooCommerce shop page.
			if (
				$hasArchivesOnly &&
				(
					! $postObject->has_archive ||
					( 'product' === $postObject->name && $woocommerce )
				)
			) {
				continue;
			}

			if ( $namesOnly ) {
				$postTypes[] = $postObject->name;
				continue;
			}

			if ( 'attachment' === $postObject->name ) {
				$postObject->label = __( 'Attachments', 'all-in-one-seo-pack' );
			}

			if ( 'product' === $postObject->name && $woocommerce ) {
				$postObject->menu_icon = 'dashicons-products';
			}

			$name = $postObject->name;
			if ( 'type' === $postObject->name && $rewriteType ) {
				$name = '_aioseo_type';
			}

			$postTypes[] = [
				'name'         => $name,
				'label'        => ucwords( $postObject->label ),
				'singular'     => ucwords( $postObject->labels->singular_name ),
				'icon'         => $postObject->menu_icon,
				'hasExcerpt'   => post_type_supports( $postObject->name, 'excerpt' ),
				'hasArchive'   => $postObject->has_archive,
				'hierarchical' => $postObject->hierarchical
			];
		}

		return $postTypes;
	}

	/**
	 * Retrieve a list of public taxonomies with slugs/icons.
	 *
	 * @since 4.0.0
	 *
	 * @param  boolean $namesOnly   Whether only the names should be returned.
	 * @param  boolean $rewriteType Whether or not to rewrite the type slugs.
	 * @return array                An array of public taxonomies.
	 */
	public function getPublicTaxonomies( $namesOnly = false, $rewriteType = false ) {
		$taxonomies = [];
		if ( count( $taxonomies ) ) {
			return $taxonomies;
		}

		$taxObjects = get_taxonomies( [ 'public' => true ], 'objects' );
		foreach ( $taxObjects as $taxObject ) {
			if ( empty( $taxObject->label ) ) {
				continue;
			}

			if ( in_array( $taxObject->name, [
				'product_shipping_class',
				'post_format'
			], true ) ) {
				continue;
			}

			// We need to exclude product attributes from this list as well.
			if (
				'pa_' === substr( $taxObject->name, 0, 3 ) &&
				'manage_product_terms' === $taxObject->cap->manage_terms &&
				! apply_filters( 'aioseo_woocommerce_product_attributes', false )
			) {
				continue;
			}

			if ( $namesOnly ) {
				$taxonomies[] = $taxObject->name;
				continue;
			}

			$name = $taxObject->name;
			if ( 'type' === $taxObject->name && $rewriteType ) {
				$name = '_aioseo_type';
			}

			$taxonomies[] = [
				'name'     => $name,
				'label'    => ucwords( $taxObject->label ),
				'singular' => ucwords( $taxObject->labels->singular_name ),
				'icon'     => strpos( $taxObject->label, 'categor' ) !== false ? 'dashicons-category' : 'dashicons-tag'
			];
		}

		return $taxonomies;
	}

	/**
	 * Returns noindexed post types.
	 *
	 * @since 4.0.0
	 *
	 * @return array A list of noindexed post types.
	 */
	public function getNoindexedPostTypes() {
		return $this->getNoindexedObjects( 'postTypes' );
	}

	/**
	 * Checks whether a given post type is noindexed.
	 *
	 * @since 4.0.0
	 *
	 * @param  string  $postType The post type.
	 * @return bool              Whether the post type is noindexed.
	 */
	public function isPostTypeNoindexed( $postType ) {
		$noindexedPostTypes = $this->getNoindexedPostTypes();
		return in_array( $postType, $noindexedPostTypes, true );
	}

	/**
	 * Returns noindexed taxonomies.
	 *
	 * @since 4.0.0
	 *
	 * @return array A list of noindexed taxonomies.
	 */
	public function getNoindexedTaxonomies() {
		return $this->getNoindexedObjects( 'taxonomies' );
	}

	/**
	 * Checks whether a given post type is noindexed.
	 *
	 * @since 4.0.0
	 *
	 * @param  string  $taxonomy The taxonomy.
	 * @return bool              Whether the taxonomy is noindexed.
	 */
	public function isTaxonomyNoindexed( $taxonomy ) {
		$noindexedTaxonomies = $this->getNoindexedTaxonomies();
		return in_array( $taxonomy, $noindexedTaxonomies, true );
	}

	/**
	 * Returns noindexed object types of a given parent type.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $type The parent object type ("postTypes" or "taxonomies").
	 * @return array        A list of noindexed objects types.
	 */
	private function getNoindexedObjects( $type ) {
		$noindexed = [];
		foreach ( aioseo()->options->searchAppearance->dynamic->$type->all() as $name => $object ) {
			if (
				! $object['show'] ||
				( $object['advanced']['robotsMeta'] && ! $object['advanced']['robotsMeta']['default'] && $object['advanced']['robotsMeta']['noindex'] )
			) {
				$noindexed[] = $name;
			}
		}
		return $noindexed;
	}

	/**
	 * Retrieve a list of users that match passed in roles.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of user data.
	 */
	public function getSiteUsers( $roles ) {
		static $users = [];

		if ( ! empty( $users ) ) {
			return $users;
		}

		$rolesWhere = [];
		foreach ( $roles as $role ) {
			$rolesWhere[] = '(um.meta_key = \'' . aioseo()->db->db->prefix . 'capabilities\' AND um.meta_value LIKE \'%\"' . $role . '\"%\')';
		}
		$dbUsers = aioseo()->db->start( 'users as u' )
			->select( 'u.ID, u.display_name, u.user_nicename, u.user_email' )
			->join( 'usermeta as um', 'u.ID = um.user_id' )
			->whereRaw( '(' . implode( ' OR ', $rolesWhere ) . ')' )
			->orderBy( 'u.user_nicename' )
			->run()
			->result();

		foreach ( $dbUsers as $dbUser ) {
			$users[] = [
				'id'          => intval( $dbUser->ID ),
				'displayName' => $dbUser->display_name,
				'niceName'    => $dbUser->user_nicename,
				'email'       => $dbUser->user_email,
				'gravatar'    => get_avatar_url( $dbUser->user_email )
			];
		}

		return $users;
	}

	/**
	 * Strips punctuation from a given string.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $string The string.
	 * @return string         The string without punctuation.
	 */
	public function stripPunctuation( $string ) {
		$string = preg_replace( '#\p{P}#u', '', $string );
		// Trim both internal and external whitespace.
		return preg_replace( '/\s\s+/u', ' ', trim( $string ) );
	}

	/*
	 * Sanitize Domain
	 *
	 * @since 4.0.0
	 *
	 * @param  string       $domain The domain to sanitize.
	 * @return mixed|string         The sanitized domain.
	 */
	public function sanitizeDomain( $domain ) {
		$domain = trim( $domain );
		$domain = strtolower( $domain );
		if ( 0 === strpos( $domain, 'http://' ) ) {
			$domain = substr( $domain, 7 );
		} elseif ( 0 === strpos( $domain, 'https://' ) ) {
			$domain = substr( $domain, 8 );
		}
		$domain = untrailingslashit( $domain );

		return $domain;
	}

	/**
	 * Get's the site domain.
	 *
	 * @since 4.0.0
	 *
	 * @return string The site's domain.
	 */
	public function getSiteDomain() {
		return wp_parse_url( home_url(), PHP_URL_HOST );
	}

	/**
	 * Get's the site URL.
	 * NOTE: For multisites inside a sub-directory, this returns the URL for the main site.
	 * This is intentional.
	 *
	 * @since 4.0.0
	 *
	 * @return string The site's domain.
	 */
	public function getSiteUrl() {
		return wp_parse_url( home_url(), PHP_URL_SCHEME ) . '://' . wp_parse_url( home_url(), PHP_URL_HOST );
	}

	/**
	 * Returns the current URL.
	 *
	 * @since 4.0.0
	 *
	 * @return string The URL.
	 */
	public function getUrl() {
		global $wp;
		return trailingslashit( home_url( $wp->request ) );
	}

	/**
	 * Gets the canonical URL for the current page/post.
	 *
	 * @since 4.0.0
	 *
	 * @return string $url The canonical URL.
	 */
	public function canonicalUrl() {
		static $canonicalUrl = '';
		if ( $canonicalUrl ) {
			return $canonicalUrl;
		}

		$metaData = [];
		$post     = $this->getPost();
		if ( $post ) {
			$metaData = aioseo()->meta->metaData->getMetaData( $post );
		}

		if ( is_category() || is_tag() || is_tax() ) {
			$metaData = aioseo()->meta->metaData->getMetaData( get_queried_object() );
		}

		if ( $metaData && ! empty( $metaData->canonical_url ) ) {
			return $metaData->canonical_url;
		}

		$url = $this->getUrl();
		if ( aioseo()->options->searchAppearance->advanced->noPaginationForCanonical && 1 < $this->getPageNumber() ) {
			$url = preg_replace( '#(\d+\/|(?<=\/)page\/\d+\/)$#', '', $url );
		}

		$url = $this->maybeRemoveTrailingSlash( $url );

		// Get rid of /amp at the end of the URL.
		if ( ! apply_filters( 'aioseo_disable_canonical_url_amp', false ) ) {
			$url = preg_replace( '/\/amp$/', '', $url );
			$url = preg_replace( '/\/amp\/$/', '/', $url );
		}

		$searchTerm = get_query_var( 's' );
		if ( is_search() && ! empty( $searchTerm ) ) {
			$url = add_query_arg( 's', $searchTerm, $url );
		}

		return apply_filters( 'aioseo_canonical_url', $url );
	}

	/**
	 * Remove trailing slashes if not set in the permalink structure.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $url The original URL.
	 * @return string      The adjusted URL.
	 */
	public function maybeRemoveTrailingSlash( $url ) {
		$permalinks = get_option( 'permalink_structure' );
		if ( $permalinks && ( ! is_home() || ! is_front_page() ) ) {
			$trailing = substr( $permalinks, -1 );
			if ( '/' !== $trailing ) {
				$url = untrailingslashit( $url );
			}
		}

		// Don't slash urls with query args.
		if ( false !== strpos( $url, '?' ) ) {
			$url = untrailingslashit( $url );
		}

		return $url;
	}

	/**
	 * Returns the page number of the current page.
	 *
	 * @since 4.0.0
	 *
	 * @return int The page number.
	 */
	public function getPageNumber() {
		$page  = get_query_var( 'page' );
		$paged = get_query_var( 'paged' );
		return ! empty( $page )
			? $page
			: (
				! empty( $paged )
					? $paged
					: 1
			);
	}

	/**
	 * Request the remote URL via wp_remote_post and return a json decoded response.
	 *
	 * @since 4.0.0
	 *
	 * @param array  $body    The content to retrieve from the remote URL.
	 * @param array  $headers The headers to send to the remote URL.
	 *
	 * @return string|bool Json decoded response on success, false on failure.
	 */
	public function sendRequest( $url, $body = [], $headers = [] ) {
		$body = wp_json_encode( $body );

		// Build the headers of the request.
		$headers = wp_parse_args(
			$headers,
			[
				'Content-Type' => 'application/json'
			]
		);

		// Setup variable for wp_remote_post.
		$post = [
			'headers'   => $headers,
			'body'      => $body,
			'sslverify' => defined( 'AIOSEO_DEV_VERSION' ) ? false : true
		];

		// Perform the query and retrieve the response.
		$response     = wp_remote_post( $url, $post );
		$responseBody = wp_remote_retrieve_body( $response );

		// Bail out early if there are any errors.
		if ( is_wp_error( $responseBody ) ) {
			return false;
		}

		// Return the json decoded content.
		return json_decode( $responseBody );
	}

	/**
	* Get the class name for the Score button.
	* Depending on the score the button should have different color.
	*
	* @since 4.0.0
	*
	* @param int $score The content to retrieve from the remote URL.
	*
	* @return string The class name for Score button.
	*/
	public function getScoreClass( $score ) {
		$scoreClass = 50 < $score ? 'score-orange' : 'score-red';
		if ( 0 === $score ) {
			$scoreClass = 'score-none';
		}
		if ( $score >= 80 ) {
			$scoreClass = 'score-green';
		}
		return $scoreClass;
	}

	/**
	 * Returns the ID of the site logo if it exists.
	 *
	 * @since 4.0.0
	 *
	 * @return int
	 */
	public function getSiteLogoId() {
		if ( ! get_theme_support( 'custom-logo' ) ) {
			return false;
		}
		return get_theme_mod( 'custom_logo' );
	}

	/**
	 * Returns the URL of the site logo if it exists.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	public function getSiteLogoUrl() {
		$id = $this->getSiteLogoId();
		if ( ! $id ) {
			return false;
		}

		$image = wp_get_attachment_image_src( $id, 'full' );
		if ( empty( $image ) ) {
			return false;
		}
		return $image[0];
	}

	/**
	 * Compares two (multidimensional) arrays to see if they're different.
	 *
	 * @since 4.0.0
	 *
	 * @param  array   $array1 The first array.
	 * @param  array   $array2 The second array.
	 * @return boolean         Whether the arrays are different.
	 */
	public function arraysDifferent( $array1, $array2 ) {
		foreach ( $array1 as $key => $value ) {
			// Check for non-existing values.
			if ( ! isset( $array2[ $key ] ) ) {
				return true;
			}
			if ( is_array( $value ) ) {
				if ( $this->arraysDifferent( $value, $array2[ $key ] ) ) {
					return true;
				};
			} else {
				if ( $value !== $array2[ $key ] ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Returns the filesystem object if we have access to it.
	 *
	 * @since 4.0.0
	 *
	 * @param  array         $args The connection args.
	 * @return WP_Filesystem       The filesystem object.
	 */
	public function wpfs( $args = [] ) {
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		WP_Filesystem( $args );

		global $wp_filesystem;
		if ( is_object( $wp_filesystem ) ) {
			return $wp_filesystem;
		}
		return false;
	}

	/**
	 * Internationalize.
	 *
	 * @since 4.0.0
	 *
	 * @param $in
	 * @return mixed|void
	 */
	public function internationalize( $in ) {
		if ( function_exists( 'langswitch_filter_langs_with_message' ) ) {
			$in = langswitch_filter_langs_with_message( $in );
		}

		if ( function_exists( 'polyglot_filter' ) ) {
			$in = polyglot_filter( $in );
		}

		if ( function_exists( 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
			$in = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $in );
		} elseif ( function_exists( 'ppqtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
			$in = ppqtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $in );
		} elseif ( function_exists( 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
			$in = qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage( $in );
		}

		return apply_filters( 'localization', $in );
	}

	/**
	 * Get the ID of the home page.
	 *
	 * @since 4.0.0
	 *
	 * @return integer The home page ID.
	 */
	public function getHomePageId() {
		$id        = null;
		$firstPage = 2 > aioseo()->helpers->getPageNumber();
		if ( 'page' === get_option( 'show_on_front' ) ) {
			$pageOnFront = get_option( 'page_on_front' );
			if ( is_page() && ! empty( $pageOnFront ) && $firstPage ) {
				$id = $pageOnFront;
			}
		} elseif (
			is_post_type_archive() &&
			is_post_type_archive( 'product' ) &&
			function_exists( 'wc_get_page_id' )
		) {
			// WooCommerce.
			$wcShopPostID = wc_get_page_id( 'shop' );
			if ( wc_get_page_id( 'shop' ) === get_option( 'page_on_front' ) ) {
				$id = $wcShopPostID;
			}
		}

		return intval( $id );
	}

	/**
	 * Truncates a given string.
	 *
	 * @since 4.0.0
	 *
	 * @param  string  $string             The string.
	 * @param  int     $maxCharacters      The max. amount of characters.
	 * @param  boolean $shouldHaveEllipsis Whether the string should have a trailing ellipsis (defaults to true).
	 * @return string  $string             The string.
	 */
	public function truncate( $string, $maxCharacters, $shouldHaveEllipsis = true ) {
		$length       = strlen( $string );
		$excessLength = $length - $maxCharacters;
		if ( 0 < $excessLength ) {
			// If the string is longer than 65535 characters, we first need to shorten it due to the character limit of the regex pattern quantifier.
			if ( 65535 < $length ) {
				$string = substr( $string, 0, 65534 );
			}
			$string = preg_replace( "#[^\pZ\pP]*.{{$excessLength}}$#", '', $string );
			if ( $shouldHaveEllipsis ) {
				$string = $string . ' ...';
			}
		}
		return $string;
	}

	/**
	 * Removes image dimensions from the slug of a URL.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $url The image URL.
	 * @return string      The formatted image URL.
	 */
	public function removeImageDimensions( $url ) {
		$uploadDirUrl = aioseo()->helpers->escapeRegex( $this->getWpContentUrl() );
		if ( ! preg_match( "/$uploadDirUrl.*/", $url ) ) {
			return $url;
		}
		return preg_replace( '#(-[0-9]*x[0-9]*)#', '', $url );
	}

	/**
	 * Returns the URLs of the pages that WooCommerce noindexes on its own.
	 *
	 * @since 4.0.0
	 *
	 * @return array The URLs of the noindexed pages.
	 */
	public function getNoindexedWooCommercePages() {
		if ( ! $this->isWooCommerceActive() ) {
			return [];
		}
		return [
			wc_get_cart_url(),
			wc_get_checkout_url(),
			wc_get_page_permalink( 'myaccount' )
		];
	}

	/**
	 * Checks if the server is running on Apache.
	 *
	 * @since 4.0.0
	 *
	 * @return boolean Whether or not it is on apache.
	 */
	public function isApache() {
		if ( ! isset( $_SERVER['SERVER_SOFTWARE'] ) ) {
			return false;
		}

		return stripos( sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ), 'apache' ) !== false;
	}

	/**
	 * Checks if the server is running on nginx.
	 *
	 * @since 4.0.0
	 *
	 * @return boolean Whether or not it is on apache.
	 */
	public function isNginx() {
		if ( ! isset( $_SERVER['SERVER_SOFTWARE'] ) ) {
			return false;
		}

		return stripos( sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ), 'nginx' ) !== false;
	}

	/**
	 * Get Network ID
	 *
	 * @since 4.0.0
	 *
	 * @return int The integer of the blog/site id.
	 */
	public function getNetworkId() {
		if ( is_multisite() ) {
			return get_network()->site_id;
		}
		return get_current_blog_id();
	}

	/**
	 * Validate IP addresses.
	 *
	 * @since 4.0.0
	 *
	 * @param  string  $ip The IP address to validate.
	 * @return boolean     If the IP address is valid or not.
	 */
	public function validateIp( $ip ) {
		if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
			// Valid IPV4.
			return true;
		}

		if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
			// Valid IPV6.
			return true;
		}

		// Doesn't seem to be a valid IP.
		return false;
	}

	/**
	 * Convert bytes to readable format.
	 *
	 * @since 4.0.0
	 *
	 * @param  integer $bytes The size of the file.
	 * @return string         The size as a string.
	 */
	public function convertFileSize( $bytes ) {
		if ( empty( $bytes ) ) {
			return [
				'original' => 0,
				'readable' => '0 B'
			];
		}
		$i = floor( log( $bytes ) / log( 1024 ) );
		$sizes = [ 'B', 'KB', 'MB', 'GB', 'TB' ];
		return [
			'original' => $bytes,
			'readable' => sprintf( '%.02F', $bytes / pow( 1024, $i ) ) * 1 . ' ' . $sizes[ $i ]
		];
	}

	/**
	 * Get timezone offset.
	 * We use the code from `wp_timezone_string()`
	 * which became available in WP 5.3+
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	public function getTimeZoneOffset() {
		// The code below is basically a copy-paste from that function.
		$timezoneString = get_option( 'timezone_string' );

		if ( $timezoneString ) {
			return $timezoneString;
		}

		$offset   = (float) get_option( 'gmt_offset' );
		$hours    = (int) $offset;
		$minutes  = ( $offset - $hours );
		$sign     = ( $offset < 0 ) ? '-' : '+';
		$absHour  = abs( $hours );
		$absMins  = abs( $minutes * 60 );
		$tzOffset = sprintf( '%s%02d:%02d', $sign, $absHour, $absMins );

		return $tzOffset;
	}

	/**
	 * Returns the current post object.
	 *
	 * @since 4.0.0
	 *
	 * @param  int     $postId The post ID.
	 * @return WP_Post         The post object.
	 */
	public function getPost( $postId = false ) {
		static $showOnFront  = null;
		static $pageOnFront  = null;
		static $pageForPosts = null;

		if ( aioseo()->helpers->isWooCommerceShopPage() ) {
			return get_post( wc_get_page_id( 'shop' ) );
		}

		if ( is_front_page() || is_home() ) {
			$showOnFront = $showOnFront ? $showOnFront : 'page' === get_option( 'show_on_front' );
			if ( $showOnFront ) {
				if ( is_front_page() ) {
					$pageOnFront = $pageOnFront ? $pageOnFront : (int) get_option( 'page_on_front' );
					return get_post( $pageOnFront );
				} elseif ( is_home() ) {
					$pageForPosts = $pageForPosts ? $pageForPosts : (int) get_option( 'page_for_posts' );
					return get_post( $pageForPosts );
				}
			}

			return get_post();
		}

		if (
			$this->isScreenBase( 'post' ) ||
			$postId ||
			is_singular()
		) {
			return get_post( $postId );
		}
	}

	/**
	 * Get Queried Object.
	 *
	 * @since 4.0.0
	 *
	 * @return null|\WP_Post Current WP object queried.
	 */
	public function getQueriedObject() {
		global $wp_query, $post;

		if ( is_object( $post ) ) {
			return $post;
		} else {
			if ( ! $wp_query ) {
				return null;
			}
			return $wp_query->get_queried_object();
		}
	}

	/**
	 * Returns the page content.
	 *
	 * @since 4.0.0
	 *
	 * @param  WP_Post|int $post The post.
	 * @return string            The content.
	 */
	public function getContent( $post = null ) {
		$post = ( $post && is_object( $post ) ) ? $post : $post = $this->getPost( $post );

		static $content = [];
		if ( isset( $content[ $post->ID ] ) ) {
			return $content[ $post->ID ];
		}

		if ( empty( $post->post_content ) ) {
			return $post->post_content;
		}

		$postContent = $post->post_content;
		if (
			! in_array( 'runShortcodesInDescription', aioseo()->internalOptions->deprecatedOptions, true ) ||
			aioseo()->options->deprecated->searchAppearance->advanced->runShortcodesInDescription
		) {
			$postContent = $this->doShortcodes( $postContent );
		}

		$postContent          = wp_trim_words( $postContent, 55, apply_filters( 'excerpt_more', ' ' . '[&hellip;]' ) );
		$postContent          = str_replace( ']]>', ']]&gt;', $postContent );
		$postContent          = preg_replace( '#(<figure.*\/figure>|<img.*\/>)#', '', $postContent );
		$content[ $post->ID ] = trim( wp_strip_all_tags( strip_shortcodes( $postContent ) ) );
		return $content[ $post->ID ];
	}

	/**
	 * Returns custom fields as a string.
	 *
	 * @since 4.0.6
	 *
	 * @param  WP_Post|int $post The post.
	 * @param  array       $keys The post meta_keys to check for values.
	 * @return string            The custom field content.
	 */
	public function getCustomFieldsContent( $post = null, $keys = [] ) {
		$post = ( $post && is_object( $post ) ) ? $post : $this->getPost( $post );

		$customFieldContent = '';

		$acfFields     = $this->getAcfContent( $post );
		$acfFieldsKeys = [];

		if ( ! empty( $acfFields ) ) {
			foreach ( $acfFields as $acfField => $acfValue ) {
				if ( in_array( $acfField, $keys, true ) ) {
					$customFieldContent .= "{$acfValue} ";
					$acfFieldsKeys[]     = $acfField;
				}
			}
		}

		foreach ( $keys as $key ) {
			if ( in_array( $key, $acfFieldsKeys, true ) ) {
				continue;
			}

			$value = get_post_meta( $post->ID, $key, true );

			if ( $value ) {
				$customFieldContent .= "{$value} ";
			}
		}

		return $customFieldContent;
	}

	/**
	 * Returns acf fields as an array of meta keys and values.
	 *
	 * @since 4.0.6
	 *
	 * @param  WP_Post|int $post         The post.
	 * @param  array       $allowedTypes A whitelist of ACF field types.
	 * @return array                     An array of meta keys and values.
	 */
	public function getAcfContent( $post = null, $types = [] ) {
		$post = ( $post && is_object( $post ) ) ? $post : $this->getPost( $post );

		if ( ! class_exists( 'ACF' ) || ! function_exists( 'get_field_objects' ) ) {
			return [];
		}

		if ( defined( 'ACF_VERSION' ) && version_compare( ACF_VERSION, '5.7.0', '<' ) ) {
			return [];
		}

		// Set defaults.
		$allowedTypes = [
			'text',
			'textarea',
			'email',
			'url',
			'wysiwyg',
			'image',
			'gallery',
			// 'link',
			// 'taxonomy',
		];

		$types     = wp_parse_args( $types, $allowedTypes );
		$acfFields = [];

		$fieldObjects = get_field_objects( $post->ID );
		if ( ! empty( $fieldObjects ) ) {
			foreach ( $fieldObjects as $field ) {
				if ( empty( $field['value'] ) ) {
					continue;
				}

				if ( ! in_array( $field['type'], $types, true ) ) {
					continue;
				}

				if ( 'url' === $field['type'] ) {
					// Url field
					$value = "<a href='{$field['value']}'>{$field['value']}</a>";
				} elseif ( 'image' === $field['type'] ) {
					// Image field
					$value = "<img src='{$field['value']['url']}'>";
				} elseif ( 'gallery' === $field['type'] ) {
					// Image field
					$value = "<img src='{$field['value'][0]['url']}'>";
				} else {
					// Other fields
					$value = $field['value'];
				}

				if ( $value ) {
					$acfFields[ $field['name'] ] = $value;
				}
			}
		}

		return $acfFields;
	}

	/**
	 * Returns the posts custom fields.
	 *
	 * @since 4.0.6
	 *
	 * @param  WP_Post|int $post The post.
	 * @return string            The custom field content.
	 */
	public function getAnalysisContent( $post = null ) {
		$post            = ( $post && is_object( $post ) ) ? $post : $this->getPost( $post );
		$customFieldKeys = aioseo()->options->searchAppearance->dynamic->postTypes->{$post->post_type}->customFields;

		if ( empty( $customFieldKeys ) ) {
			return get_post_field( 'post_content', $post->ID );
		}

		$customFieldKeys    = explode( ' ', sanitize_text_field( $customFieldKeys ) );
		$customFieldContent = $this->getCustomFieldsContent( $post->ID, $customFieldKeys );
		$analysisContent    = $post->post_content . apply_filters( 'aioseo_analysis_content', $customFieldContent );

		return sanitize_post_field( 'post_content', $analysisContent, $post->ID, 'display' );
	}

	/**
	 * Returns the string after it is encoded with htmlspecialchars().
	 *
	 * @since 4.0.0
	 *
	 * @param  string $string The string to encode.
	 * @return string         The encoded string.
	 */
	public function encodeOutputHtml( $string ) {
		return htmlspecialchars( $string, ENT_COMPAT | ENT_HTML401, get_option( 'blog_charset' ), false );
	}

	/**
	 * Returns the string after all HTML entities have been decoded.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $string The string to decode.
	 * @return string         The decoded string.
	 */
	public function decodeHtmlEntities( $string ) {
		return html_entity_decode( (string) $string, ENT_QUOTES );
	}

	/**
	 * Returns if the page is a special type one (WooCommerce pages, Privacy page).
	 *
	 * @since 4.0.0
	 *
	 * @param  int     $postId The post ID.
	 * @return boolean         If the page is special or not.
	 */
	public function isSpecialPage( $postId = false ) {
		if (
			(int) get_option( 'page_for_posts' ) === (int) $postId ||
			(int) get_option( 'wp_page_for_privacy_policy' ) === (int) $postId ||
			$this->isBuddyPressPage( $postId ) ||
			$this->isWooCommercePage( $postId )
		) {
			return true;
		}

		return false;
	}

	/**
	 * Returns if the page is a WooCommerce page (Cart, Checkout, ...).
	 *
	 * @since 4.0.0
	 *
	 * @param  int     $postId The post ID.
	 * @return boolean         If the page is a WooCommerce page or not.
	 */
	public function isWooCommercePage( $postId = false ) {
		if ( ! $this->isWooCommerceActive() ) {
			return false;
		}

		static $cartPageId;
		if ( ! $cartPageId ) {
			$cartPageId = (int) get_option( 'woocommerce_cart_page_id' );
		}
		static $checkoutPageId;
		if ( ! $checkoutPageId ) {
			$checkoutPageId = (int) get_option( 'woocommerce_checkout_page_id' );
		}
		static $myAccountPageId;
		if ( ! $myAccountPageId ) {
			$myAccountPageId = (int) get_option( 'woocommerce_myaccount_page_id' );
		}
		static $termsPageId;
		if ( ! $termsPageId ) {
			$termsPageId = (int) get_option( 'woocommerce_terms_page_id' );
		}
		if (
			$cartPageId === (int) $postId ||
			$checkoutPageId === (int) $postId ||
			$myAccountPageId === (int) $postId ||
			$termsPageId === (int) $postId
		) {
			return true;
		}

		return false;
	}

	/**
	 * Returns if the page is a BuddyPress page (Activity, Members, Groups).
	 *
	 * @since 4.0.0
	 *
	 * @param  int     $postId The post ID.
	 * @return boolean         If the page is a BuddyPress page or not.
	 */
	public function isBuddyPressPage( $postId = false ) {
		$bpPages = get_option( 'bp-pages' );

		if ( empty( $bpPages ) ) {
			return false;
		}

		foreach ( $bpPages as $page ) {
			if ( (int) $page === (int) $postId ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get's the estimated reading time for a string.
	 *
	 * @since 4.0.0
	 *
	 * @param  string  $string The string to count.
	 * @return integer         The estimated reading time as an integer.
	 */
	public function getReadingTime( $string ) {
		$wpm  = 250;
		$word = str_word_count( wp_strip_all_tags( $string ) );
		return round( $word / $wpm );
	}

	/**
	 * Sanitizes a given option value before we store it in the DB.
	 *
	 * Used by the migration and importer classes.
	 *
	 * @since 4.0.0
	 *
	 * @param  mixed $value The value.
	 * @return mixed $value The sanitized value.
	 */
	public function sanitizeOption( $value ) {
		switch ( gettype( $value ) ) {
			case 'boolean':
				return (bool) $value;
			case 'string':
				$value = aioseo()->helpers->decodeHtmlEntities( $value );
				return aioseo()->helpers->encodeOutputHtml( wp_strip_all_tags( wp_check_invalid_utf8( trim( $value ) ) ) );
			case 'integer':
				return intval( $value );
			case 'double':
				return floatval( $value );
			case 'array':
				$sanitized = [];
				foreach ( (array) $value as $child ) {
					$sanitized[] = aioseo()->helpers->sanitizeOption( $child );
				}
				return $sanitized;
			default:
				return false;
		}
	}

	/**
	 * Checks if WPML is active.
	 *
	 * @since 4.0.0
	 *
	 * @return bool True if it is, false if not.
	 */
	public function isWpmlActive() {
		return class_exists( 'SitePress' );
	}

	/**
	 * Check if the post passed in is a valid post, not a revision or autosave.
	 *
	 * @since 4.0.5
	 *
	 * @param  WP_Post $post The Post object to check.
	 * @return bool          True if valid, false if not.
	 */
	public function isValidPost( $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}

		if ( ! is_object( $post ) ) {
			$post = get_post( $post );
		}

		// In order to prevent recursion, we are skipping scheduled-action posts.
		if (
			empty( $post ) ||
			'scheduled-action' === $post->post_type ||
			'revision' === $post->post_type ||
			'publish' !== $post->post_status
		) {
			return false;
		}

		return true;
	}

	/**
	 * Returns the URL for the WP content folder.
	 *
	 * @since 4.0.5
	 *
	 * @return string The URL.
	 */
	public function getWpContentUrl() {
		$info = wp_get_upload_dir();
		return isset( $info['baseurl'] ) ? $info['baseurl'] : '';
	}

	/**
	 * Escapes special regex characters.
	 *
	 * @since 4.0.5
	 *
	 * @param  string $string The string.
	 * @return string         The escaped string.
	 */
	public function escapeRegex( $string ) {
		return preg_quote( $string, '/' );
	}

	/**
	 * Escapes special regex characters inside the replacement string.
	 *
	 * @since 4.0.7
	 *
	 * @param  string $string The string.
	 * @return string         The escaped string.
	 */
	public function escapeRegexReplacement( $string ) {
		return str_replace( '$', '\$', $string );
	}

	/**
	 * Returns the content with shortcodes replaced.
	 *
	 * @since 4.0.5
	 *
	 * @param  string $content The post content.
	 * @return string $content The post content with shortcodes replaced.
	 */
	public function doShortcodes( $content ) {
		if ( is_admin() ) {
			return $content;
		}

		// These are shortcodes that cause conflicts if we process them.
		$conflictingShortcodes = [
			'WooCommerce Login'          => '[woocommerce_my_account]',
			'WooCommerce Checkout'       => '[woocommerce_checkout]',
			'WooCommerce Order Tracking' => '[woocommerce_order_tracking]',
			'WooCommerce Cart'           => '[woocommerce_cart]',
			'WooCommerce Registration'   => '[wwp_registration_form]',
		];

		$conflictingShortcodes = apply_filters( 'aioseo_conflicting_shortcodes', $conflictingShortcodes );

		global $shortcode_tags;
		$foundConflictingShortcodes = [];
		foreach ( $conflictingShortcodes as $shortcode ) {
			// Second check is needed for shortcodes inside Classic Editor blocks.
			if ( stripos( $content, $shortcode, 0 ) || 0 === stripos( $content, $shortcode, 0 ) ) {
				$shortcodeTag = str_replace( [ '[', ']' ], '', $shortcode );
				if ( array_key_exists( $shortcodeTag, $shortcode_tags ) ) {
					$foundConflictingShortcodes[ $shortcodeTag ] = $shortcode_tags[ $shortcodeTag ];
				}
			}
		}

		// Remove all conflicting shortcodes before parsing the content.
		foreach ( $foundConflictingShortcodes as $shortcodeTag => $shortcodeCallback ) {
			remove_shortcode( $shortcodeTag );
		}

		$content = do_shortcode( $content );

		// Add back shortcodes as remove_shortcode() disables them site-wide.
		foreach ( $foundConflictingShortcodes as $shortcodeTag => $shortcodeCallback ) {
			add_shortcode( $shortcodeTag, $shortcodeCallback );
		}

		return $content;
	}

	/**
	 * Checks whether we're on the given screen.
	 *
	 * @since 4.0.7
	 *
	 * @param  string  $screenName The screen name.
	 * @return boolean             Whether we're on the given screen.
	 */
	public function isScreenBase( $screenName ) {
		if ( ! is_admin() || ! function_exists( 'get_current_screen' ) ) {
			return false;
		}

		$screen = get_current_screen();
		if ( ! isset( $screen->base ) ) {
			return false;
		}
		return $screen->base === $screenName;
	}

	/**
	 * Extracts existing sitemap URLs from the robots.txt file.
	 *
	 * We need this in case users have existing sitemap directives added to their robots.txt file.
	 *
	 * @since 4.0.10
	 *
	 * @return array An array with robots.txt sitemap directives.
	 */
	private function extractSitemapUrlsFromRobotsTxt() {
		// First, we need to remove our filter, so that it doesn't run unintentionally.
		remove_filter( 'robots_txt', [ aioseo()->robotsTxt, 'buildRules' ], 10000 );
		$robotsTxt = apply_filters( 'robots_txt', '', true );
		add_filter( 'robots_txt', [ aioseo()->robotsTxt, 'buildRules' ], 10000, 2 );

		if ( ! $robotsTxt ) {
			return [];
		}

		$lines = explode( "\n", $robotsTxt );
		if ( ! is_array( $lines ) || ! count( $lines ) ) {
			return [];
		}

		return aioseo()->robotsTxt->extractSitemapUrls( explode( "\n", $robotsTxt ) );
	}
}