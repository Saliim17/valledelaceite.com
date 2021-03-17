<?php
namespace AIOSEO\Plugin\Common\Utils;

use AIOSEO\Plugin\Common\Utils;

/**
 * Contains helper methods specific to the addons.
 *
 * @since 4.0.0
 */
class Addons {
	/**
	 * Returns our addons.
	 *
	 * @since 4.0.0
	 *
	 * @param  boolean $flushCache Whether or not to flush the cache.
	 * @return array               An array of addon data.
	 */
	public function getAddons( $flushCache = false ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		$addons = get_transient( 'aioseo_addons' );
		if ( false === $addons || $flushCache ) {
			$addons = aioseo()->helpers->sendRequest( $this->getLicensingUrl() . 'addons/', $this->getAddonPayload() );
		}

		$transientTime = 4 * HOUR_IN_SECONDS;
		if ( ! $addons || ! empty( $addons->error ) ) {
			$addons        = $this->getDefaultAddons();
			$transientTime = 10 * MINUTE_IN_SECONDS;
		}

		set_transient( 'aioseo_addons', $addons, $transientTime );

		// The API request will tell us if we can activate a plugin, but let's check if its already active.
		$installedPlugins = array_keys( get_plugins() );
		foreach ( $addons as $key => $addon ) {
			$addons[ $key ]->basename   = $this->getAddonBasename( $addon->sku );
			$addons[ $key ]->installed  = in_array( $this->getAddonBasename( $addon->sku ), $installedPlugins, true );
			$addons[ $key ]->isActive   = is_plugin_active( $addons[ $key ]->basename );
			$addons[ $key ]->canInstall = $this->canInstall();
		}

		return $addons;
	}

	/**
	 * Get the data for a specific addon.
	 *
	 * We need this function to refresh the data of a given addon because installation links expire after one hour.
	 *
	 * @since 4.0.0
	 *
	 * @param  string  $sku        The addon sku.
	 * @param  boolean $flushCache Whether or not to flush the cache.
	 * @return object              The addon.
	 */
	public function getAddon( $sku, $flushCache = false ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		$addon = get_transient( 'aioseo_addon_' . $sku );
		if ( false === $addon || $flushCache ) {
			$addon = aioseo()->helpers->sendRequest( $this->getLicensingUrl() . 'addons/', $this->getAddonPayload( $sku ) );
		}

		$transientTime = 10 * MINUTE_IN_SECONDS;
		if ( ! $addon || ! empty( $addon->error ) ) {
			$addon = $this->getDefaultAddon( $sku );
		}

		set_transient( 'aioseo_addon_' . $sku, $addon, $transientTime );

		// The API request will tell us if we can activate a plugin, but let's check if its already active.
		$installedPlugins  = array_keys( get_plugins() );
		$addon->basename   = $this->getAddonBasename( $addon->sku );
		$addon->installed  = in_array( $this->getAddonBasename( $addon->sku ), $installedPlugins, true );
		$addon->isActive   = is_plugin_active( $addon->basename );
		$addon->canInstall = $this->canInstall();

		return $addon;
	}

	/**
	 * Gets the payload to send in the request.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $sku The sku to use in the request.
	 * @return array       A payload array.
	 */
	private function getAddonPayload( $sku = 'all-in-one-seo-pack-pro' ) {
		return [
			'license'     => aioseo()->options->has( 'general' ) && aioseo()->options->general->has( 'licenseKey' )
				? aioseo()->options->general->licenseKey
				: '',
			'domain'      => aioseo()->helpers->getSiteDomain(),
			'sku'         => $sku,
			'version'     => AIOSEO_VERSION,
			'php_version' => PHP_VERSION,
			'wp_version'  => get_bloginfo( 'version' )
		];
	}

	/**
	 * Checks if the specified addon is activated.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $sku The sku to check.
	 * @return string      The addon basename.
	 */
	public function getAddonBasename( $sku ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		$plugins = get_plugins();

		$keys = array_keys( $plugins );
		foreach ( $keys as $key ) {
			if ( preg_match( '|^' . $sku . '|', $key ) ) {
				return $key;
			}
		}

		return $sku;
	}

	/**
	 * Returns an array of levels connected to an addon.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $addonName The addon name.
	 * @return array             The array of levels.
	 */
	public function getAddonLevels( $addonName ) {
		$addons = $this->getAddons();
		foreach ( $addons as $addon ) {
			if ( $addonName !== $addon->sku ) {
				continue;
			}

			if ( ! isset( $addon->levels ) ) {
				return [];
			}

			return $addon->levels;
		}
		return [];
	}

	/**
	 * Get the URL to check licenses.
	 *
	 * @since 4.0.0
	 *
	 * @return string The URL.
	 */
	private function getLicensingUrl() {
		if ( defined( 'AIOSEO_LICENSING_URL' ) ) {
			return AIOSEO_LICENSING_URL;
		}
		return 'https://licensing.aioseo.com/v1/';
	}

	/**
	 * Installs and activates a given addon or plugin.
	 *
	 * @since 4.0.0
	 *
	 * @return boolean Whether or not the installation was succesful.
	 */
	public function installAddon( $name ) {
		if ( ! $this->canInstall() ) {
			return false;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/template.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-screen.php';
		require_once ABSPATH . 'wp-admin/includes/screen.php';

		// Set the current screen to avoid undefined notices.
		set_current_screen( 'toplevel_page_aioseo' );

		// Prepare variables.
		$url = esc_url_raw(
			add_query_arg(
				[
					'page' => 'aioseo-settings',
				],
				admin_url( 'admin.php' )
			)
		);

		// Do not allow WordPress to search/download translations, as this will break JS output.
		remove_action( 'upgrader_process_complete', [ 'Language_Pack_Upgrader', 'async_upgrade' ], 20 );

		// Create the plugin upgrader with our custom skin.
		$installer = new Utils\PluginUpgraderSilentAjax( new Utils\PluginUpgraderSkin() );

		// Activate the plugin silently.
		$pluginUrl = ! empty( $installer->pluginSlugs[ $name ] ) ? $installer->pluginSlugs[ $name ] : $name;
		$activated = activate_plugin( $pluginUrl );

		if ( ! is_wp_error( $activated ) ) {
			return $name;
		}

		// Using output buffering to prevent the FTP form from being displayed in the screen.
		ob_start();
		$creds = request_filesystem_credentials( $url, '', false, false, null );
		ob_end_clean();

		// Check for file system permissions.
		if ( false === $creds || ! aioseo()->helpers->wpfs( $creds ) ) {
			return false;
		}

		// Error check.
		if ( ! method_exists( $installer, 'install' ) ) {
			return false;
		}

		$installLink = ! empty( $installer->pluginLinks[ $name ] ) ? $installer->pluginLinks[ $name ] : null;

		// Check if this is an addon and if we have a download link.
		if ( empty( $installLink ) ) {
			$addon = aioseo()->addons->getAddon( $name, true );
			if ( empty( $addon->downloadUrl ) ) {
				return false;
			}

			$installLink = $addon->downloadUrl;
		}

		$installer->install( $installLink );

		// Flush the cache and return the newly installed plugin basename.
		wp_cache_flush();

		$pluginBasename = $installer->plugin_info();
		if ( ! $pluginBasename ) {
			return false;
		}

		// Activate the plugin silently.
		$activated = activate_plugin( $pluginBasename );

		if ( is_wp_error( $activated ) ) {
			return false;
		}

		return $pluginBasename;
	}

	/**
	 * Determine if addons/plugins can be installed.
	 *
	 * @since 4.0.0
	 *
	 * @return bool True if yes, false if not.
	 */
	public function canInstall() {
		if ( ! current_user_can( 'install_plugins' ) ) {
			return false;
		}

		// Determine whether file modifications are allowed.
		if ( ! wp_is_file_mod_allowed( 'aioseo_can_install' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieves a default addon with whatever information is needed if the API cannot be reached.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $sku The sku of the addon.
	 * @return array       An array of addon data.
	 */
	public function getDefaultAddon( $sku ) {
		$addons = $this->getDefaultAddons();
		$addon  = [];
		foreach ( $addons as $a ) {
			if ( $a->sku === $sku ) {
				$addon = $a;
			}
		}

		return $addon;
	}

	/**
	 * Retrieves a default list of addons if the API cannot be reached.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of addons.
	 */
	private function getDefaultAddons() {
		return json_decode( wp_json_encode( [
			[
				'sku'                => 'aioseo-image-seo',
				'name'               => 'Image SEO',
				'version'            => '1.0.0',
				'image'              => null,
				'icon'               => 'svg-image-seo',
				'levels'             => [
					'business',
					'agency',
					'pro',
					'elite',
				],
				'currentLevels'      => [
					'business',
					'agency',
				],
				'requiresUpgrade'    => false,
				'description'        => '<p>Globally control the Title attribute and Alt text for images in your content. These attributes are essential for both accessibility and SEO.</p>',
				'descriptionVersion' => 0,
				'downloadUrl'        => '',
				'productUrl'         => 'https://aioseo.com/image-seo',
				'learnMoreUrl'       => 'https://aioseo.com/image-seo',
				'manageUrl'          => 'https://route#aioseo-search-appearance:media',
				'basename'           => 'aioseo-image-seo/aioseo-image-seo.php',
				'installed'          => false,
				'isActive'           => false,
				'canInstall'         => false
			],
			[
				'sku'                => 'aioseo-video-sitemap',
				'name'               => 'Video Sitemap',
				'version'            => '1.0.0',
				'image'              => null,
				'icon'               => 'svg-sitemaps-pro',
				'levels'             => [
					'individual',
					'business',
					'agency',
					'pro',
					'elite',
				],
				'currentLevels'      => [
					'individual',
					'business',
					'agency',
				],
				'requiresUpgrade'    => false,
				'description'        => '<p>The Video Sitemap works in much the same way as the XML Sitemap module, it generates an XML Sitemap specifically for video content on your site. Search engines use this information to display rich snippet information in search results.</p>', // phpcs:ignore Generic.Files.LineLength.MaxExceeded
				'descriptionVersion' => 0,
				'downloadUrl'        => '',
				'productUrl'         => 'https://aioseo.com/video-sitemap',
				'learnMoreUrl'       => 'https://aioseo.com/video-sitemap',
				'manageUrl'          => 'https://route#aioseo-sitemaps:video-sitemap',
				'basename'           => 'aioseo-video-sitemap/aioseo-video-sitemap.php',
				'installed'          => false,
				'isActive'           => false,
				'canInstall'         => false
			],
			[
				'sku'                => 'aioseo-local-business',
				'name'               => 'Local Business SEO',
				'version'            => '1.0.0',
				'image'              => null,
				'icon'               => 'svg-local-business',
				'levels'             => [
					'business',
					'agency',
					'pro',
					'elite',
				],
				'currentLevels'      => [
					'business',
					'agency',
				],
				'requiresUpgrade'    => false,
				'description'        => '<p>Local Business schema markup enables you to tell Google about your business, including your business name, address and phone number, opening hours and price range. This information may be displayed as a Knowledge Graph card or business carousel.</p>', // phpcs:ignore Generic.Files.LineLength.MaxExceeded
				'descriptionVersion' => 0,
				'downloadUrl'        => '',
				'productUrl'         => 'https://aioseo.com/local-business',
				'learnMoreUrl'       => 'https://aioseo.com/local-business',
				'manageUrl'          => 'https://route#aioseo-local-seo:locations',
				'basename'           => 'aioseo-local-business/aioseo-local-business.php',
				'installed'          => false,
				'isActive'           => false,
				'canInstall'         => false
			],
			[
				'sku'                => 'aioseo-news-sitemap',
				'name'               => 'News Sitemap',
				'version'            => '1.0.0',
				'image'              => null,
				'icon'               => 'svg-sitemaps-pro',
				'levels'             => [
					'business',
					'agency',
					'pro',
					'elite',
				],
				'currentLevels'      => [
					'business',
					'agency',
				],
				'requiresUpgrade'    => false,
				'description'        => '<p>Our Google News Sitemap lets you control which content you submit to Google News and only contains articles that were published in the last 48 hours. In order to submit a News Sitemap to Google, you must have added your site to Google’s Publisher Center and had it approved.</p>', // phpcs:ignore Generic.Files.LineLength.MaxExceeded
				'descriptionVersion' => 0,
				'downloadUrl'        => '',
				'productUrl'         => 'https://aioseo.com/news-sitemap',
				'learnMoreUrl'       => 'https://aioseo.com/news-sitemap',
				'manageUrl'          => 'https://route#aioseo-sitemaps:news-sitemap',
				'basename'           => 'aioseo-news-sitemap/aioseo-news-sitemap.php',
				'installed'          => false,
				'isActive'           => false,
				'canInstall'         => false
			]
		] ) );
	}
}