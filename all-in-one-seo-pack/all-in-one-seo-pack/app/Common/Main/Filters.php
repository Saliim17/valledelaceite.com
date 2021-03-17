<?php
namespace AIOSEO\Plugin\Common\Main;

/**
 * Abstract class that Pro and Lite both extend.
 *
 * @since 4.0.0
 */
abstract class Filters {
	/**
	 * The plugin we are checking.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	private $plugin;

	/**
	 * Construct method.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		add_filter( 'plugin_row_meta', [ $this, 'pluginRowMeta' ], 10, 2 );
		add_filter( 'plugin_action_links_' . AIOSEO_PLUGIN_BASENAME, [ $this, 'pluginActionLinks' ], 10, 2 );

		// Genesis theme compatibility.
		add_filter( 'genesis_detect_seo_plugins', [ $this, 'genesisTheme' ] );

		// WeGlot compatibility.
		if ( preg_match( '#(/default\.xsl)$#i', $_SERVER['REQUEST_URI'] ) ) {
			add_filter( 'weglot_active_translation_before_treat_page', '__return_false' );
		}

		// GoDaddy CDN compatibility.
		add_filter( 'wpaas_cdn_file_ext', [ $this, 'goDaddySitemapXml' ] );
	}

	/**
	 * Disable SEO inside the Genesis theme if it's running.
	 *
	 * @since 4.0.3
	 *
	 * @param  array $array An array of checks.
	 * @return array        An array with our function added.
	 */
	public function genesisTheme( $array ) {
		if ( empty( $array ) || ! isset( $array['functions'] ) ) {
			return $array;
		}

		$array['functions'][] = 'aioseo';

		return $array;
	}

	/**
	 * Remove XML from the GoDaddy CDN so our urls remain intact.
	 *
	 * @since 4.0.5
	 *
	 * @param  array $extensions The original extensions list.
	 * @return array             The extensions list without xml.
	 */
	public function goDaddySitemapXml( $extensions ) {
		$key = array_search( 'xml', $extensions, true );
		unset( $extensions[ $key ] );
		return $extensions;
	}

	/**
	 * Action links for the plugins page.
	 *
	 * @since 4.0.0
	 *
	 * @return array The array of actions.
	 */
	abstract public function pluginRowMeta( $actions, $pluginFile );

	/**
	 * Action links for the plugins page.
	 *
	 * @since 4.0.0
	 *
	 * @return array The array of actions.
	 */
	abstract public function pluginActionLinks( $actions, $pluginFile );

	/**
	 * Parse the action links.
	 *
	 * @since 4.0.0
	 *
	 * @param  array  $actions
	 * @param  string $pluginFile
	 * @param
	 * @return array
	 */
	protected function parseActionLinks( $actions, $pluginFile, $actionLinks = [], $position = 'after' ) {
		if ( empty( $this->plugin ) ) {
			$this->plugin = AIOSEO_PLUGIN_BASENAME;
		}

		if ( $this->plugin === $pluginFile && ! empty( $actionLinks ) ) {
			foreach ( $actionLinks as $key => $value ) {
				$link = [
					$key => '<a href="' . $value['url'] . '">' . $value['label'] . '</a>'
				];

				$actions = 'after' === $position ? array_merge( $actions, $link ) : array_merge( $link, $actions );
			}
		}
		return $actions;
	}
}