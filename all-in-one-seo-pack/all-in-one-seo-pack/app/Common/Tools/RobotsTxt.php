<?php
namespace AIOSEO\Plugin\Common\Tools;

use AIOSEO\Plugin\Common\Models;

class RobotsTxt {
	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		add_filter( 'robots_txt', [ $this, 'buildRules' ], 10000, 2 );

		// If our tables do not exist, create them now.
		if ( ! aioseo()->db->tableExists( 'aioseo_notifications' ) ) {
			aioseo()->updates->addInitialCustomTablesForV4();
		}
		$this->checkForPhysicalFiles();
	}

	/**
	 * Build out the robots.txt rules.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $original The original rules to parse.
	 * @return string           The parsed/appended/modified rules.
	 */
	public function buildRules( $original ) {
		// Other plugins might call this too early.
		if ( ! property_exists( aioseo(), 'sitemap' ) ) {
			return $original;
		}

		$original      = explode( "\n", $original );
		$sitemapUrls   = implode( "\r\n", array_merge( aioseo()->sitemap->helpers->getSitemapUrls(), $this->extractSitemapUrls( $original ) ) );
		$originalRules = $this->extractRules( $original );
		$networkRules  = [];
		if ( is_multisite() ) {
			switch_to_blog( aioseo()->helpers->getNetworkId() );
			$options = aioseo()->options->noConflict();
			$options->initNetwork();
			$networkRules = $options->tools->robots->enable ? $options->tools->robots->rules : [];
			restore_current_blog();
		}

		if ( ! aioseo()->options->tools->robots->enable ) {
			$networkAndOriginal = $this->mergeRules( $originalRules, $this->parseRules( $networkRules ) );
			$networkAndOriginal = $this->robotsArrayUnique( $networkAndOriginal );
			return $this->stringify( $networkAndOriginal ) . "\r\n" . $sitemapUrls;
		}

		$allRules = $this->mergeRules( $originalRules, $this->mergeRules( $this->parseRules( $networkRules ), $this->parseRules( aioseo()->options->tools->robots->rules ) ), true );
		$allRules = $this->robotsArrayUnique( $allRules );

		return $this->stringify( $allRules ) . "\r\n" . $sitemapUrls;
	}

	/**
	 * Merges two rulesets.
	 *
	 * @since 4.0.0
	 *
	 * @param  array          $rules1   An array of rules to merge with.
	 * @param  array          $rules2   An array of rules to merge.
	 * @param  boolean $allowOverride   Whether or not to allow overriding.
	 * @param  boolean $allowduplicates Whether or not to allow duplicates.
	 * @return array                    The validated rules.
	 */
	private function mergeRules( $rules1, $rules2, $allowOverride = false, $allowDuplicates = false ) {
		foreach ( $rules2 as $userAgent => $rules ) {
			if ( empty( $userAgent ) ) {
				continue;
			}

			if ( empty( $rules1[ $userAgent ] ) ) {
				$rules1[ $userAgent ] = $rules2[ $userAgent ];
				continue;
			}

			foreach ( $rules['allow'] as $index1 => $path ) {
				$index2 = array_search( $path, $rules1[ $userAgent ]['disallow'], true );
				if ( false !== $index2 && ! $allowDuplicates ) {
					if ( $allowOverride ) {
						unset( $rules1[ $userAgent ]['disallow'][ $index2 ] );
					} else {
						unset( $rules2[ $userAgent ]['allow'][ $index1 ] );
					}
				}

				$pattern = '^' . str_replace(
					[
						'.',
						'/',
						'*'
					],
					[
						'\.',
						'\/',
						'(.*)'
					],
					$path
				) . '$';

				foreach ( $rules1[ $userAgent ]['allow'] as $p ) {
					$matches = [];
					preg_match( "/{$pattern}/", $p, $matches );
				}

				if ( ! empty( $matches ) && ! $allowDuplicates ) {
					unset( $rules2[ $userAgent ]['allow'][ $index1 ] );
				}

				foreach ( $rules1[ $userAgent ]['disallow'] as $p ) {
					$matches = [];
					preg_match( "/{$pattern}/", $p, $matches );
				}

				if ( ! empty( $matches ) && ! $allowDuplicates ) {
					unset( $rules2[ $userAgent ]['allow'][ $index1 ] );
				}
			}

			foreach ( $rules['disallow'] as $index1 => $path ) {
				$index2 = array_search( $path, $rules1[ $userAgent ]['allow'], true );
				if ( false !== $index2 && ! $allowDuplicates ) {
					if ( $allowOverride ) {
						unset( $rules1[ $userAgent ]['allow'][ $index2 ] );
					} else {
						unset( $rules2[ $userAgent ]['disallow'][ $index1 ] );
					}
				}

				$pattern = '^' . str_replace(
					[
						'.',
						'/',
						'*'
					],
					[
						'\.',
						'\/',
						'(.*)'
					],
					$path
				) . '$';

				foreach ( $rules1[ $userAgent ]['disallow'] as $p ) {
					$matches = [];
					preg_match( "/{$pattern}/", $p, $matches );
				}

				if ( ! empty( $matches ) && ! $allowDuplicates ) {
					unset( $rules2[ $userAgent ]['disallow'][ $index1 ] );
				}

				foreach ( $rules1[ $userAgent ]['allow'] as $p ) {
					$matches = [];
					preg_match( "/{$pattern}/", $p, $matches );
				}

				if ( ! empty( $matches ) && ! $allowDuplicates ) {
					unset( $rules2[ $userAgent ]['disallow'][ $index1 ] );
				}
			}

			$allow = array_merge(
				array_values( $rules1[ $userAgent ]['allow'] ),
				array_values( $rules2[ $userAgent ]['allow'] )
			);
			$rules1[ $userAgent ]['allow'] = array_unique( $allow );

			$disallow = array_merge(
				array_values( $rules1[ $userAgent ]['disallow'] ),
				array_values( $rules2[ $userAgent ]['disallow'] )
			);
			$rules1[ $userAgent ]['disallow'] = array_unique( $disallow );

		}

		return $rules1;
	}

	/**
	 * Stringifies the parsed rules.
	 *
	 * @param  array  $allRules The rules array.
	 * @return string           The stringified rules.
	 */
	private function stringify( $allRules ) {
		$robots = [];
		foreach ( $allRules as $agent => $rules ) {
			if ( empty( $agent ) ) {
				continue;
			}

			$robots[] = sprintf( 'User-agent: %s', $agent );

			foreach ( $rules as $type => $path ) {
				foreach ( $path as $p ) {
					if ( empty( $p ) ) {
						continue;
					}

					$robots[] = sprintf( '%s: %s', ucfirst( $type ), $p );
				}
			}

			$robots[] = '';
		}
		return implode( "\r\n", $robots );
	}

	/**
	 * Parses the rules.
	 *
	 * @since 4.0.0
	 *
	 * @return array|mixed The rules.
	 */
	private function parseRules( $rules ) {
		$robots = [];
		foreach ( $rules as $rule ) {
			$r = json_decode( $rule );
			if ( empty( $robots[ $r->userAgent ] ) ) {
				$robots[ $r->userAgent ] = [
					'allow'    => [],
					'disallow' => []
				];

			}

			$robots[ $r->userAgent ][ $r->rule ][] = $r->directoryPath;
		}

		return $robots;
	}

	/**
	 * Extract rules from a string.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $lines The lines to extract from.
	 * @return array        An array of extracted rules.
	 */
	private function extractRules( $lines ) {
		$rules     = [];
		$userAgent = null;
		foreach ( $lines as $line ) {
			if ( empty( $line ) ) {
				continue;
			}

			$array = array_map( 'trim', explode( ':', $line ) );
			if ( $array && count( $array ) !== 2 ) {
				// Invalid line, let's keep going.
				continue;
			}

			$operand = $array[0];
			switch ( strtolower( $operand ) ) {
				case 'user-agent':
					$userAgent = $array[1];
					$rules[ $userAgent ] = ! empty( $rules[ $userAgent ] ) ? $rules[ $userAgent ] : [
						'allow'    => [],
						'disallow' => []
					];
					break;
				case 'allow':
				case 'disallow':
					$rules[ $userAgent ][ strtolower( $operand ) ][] = $this->sanitizePath( $array[1] );
					break;
				default:
					break;
			}
		}

		return $rules;
	}

	/**
	 * Extract sitemap URLs from a string.
	 *
	 * @since 4.0.10
	 *
	 * @param  array $lines The lines to extract from.
	 * @return array        An array of sitemap URLs.
	 */
	public function extractSitemapUrls( $lines ) {
		$sitemapUrls = [];
		foreach ( $lines as $line ) {
			if ( empty( $line ) ) {
				continue;
			}

			$array = array_map( 'trim', explode( 'sitemap:', strtolower( $line ) ) );
			if ( ! empty( $array[1] ) ) {
				$sitemapUrls[] = trim( $line );
			}
		}
		return $sitemapUrls;
	}

	/**
	 * Sanitize the path on import.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $path The path to sanitize.
	 * @return string       The sanitized path.
	 */
	private function sanitizePath( $path ) {
		// if path does not have a trailing wild card (*) or does not refer to a file (with extension), add trailing slash.
		if ( '*' !== substr( $path, -1 ) && false === strpos( $path, '.' ) ) {
			$path = trailingslashit( $path );
		}

		// if path does not have a leading slash, add it.
		if ( '/' !== substr( $path, 0, 1 ) ) {
			$path = '/' . $path;
		}

		// convert everything to lower case.
		$path = strtolower( $path );

		return $path;
	}

	/**
	 * Check if a physical robots.txt file exists, and if it does. Add a notice.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function checkForPhysicalFiles() {
		if ( ! $this->hasPhysicalRobotsTxt() ) {
			return;
		}

		$notification = Models\Notification::getNotificationByName( 'robots-physical-file' );
		if ( $notification->exists() ) {
			return;
		}

		Models\Notification::addNotification( [
			'slug'              => uniqid(),
			'notification_name' => 'robots-physical-file',
			'title'             => __( 'Physical Robots.txt File Detected', 'all-in-one-seo-pack' ),
			'content'           => sprintf(
				// Translators: 1 - The plugin name ("All in One SEO"), 2 - The plugin name ("All in One SEO").
				__( '%1$s has detected a physical robots.txt file in the root folder of your WordPress installation. We recommend removing this file as it could cause conflicts with WordPress\' dynamically generated one. %2$s can import this file and delete it, or you can simply delete it.', 'all-in-one-seo-pack' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
				AIOSEO_PLUGIN_SHORT_NAME,
				AIOSEO_PLUGIN_SHORT_NAME
			),
			'type'              => 'error',
			'level'             => [ 'all' ],
			'button1_label'     => __( 'Import and Delete', 'all-in-one-seo-pack' ),
			'button1_action'    => 'http://action#tools/import-robots-txt?redirect=aioseo-tools',
			'button2_label'     => __( 'Delete', 'all-in-one-seo-pack' ),
			'button2_action'    => 'http://action#tools/delete-robots-txt?redirect=aioseo-tools',
			'start'             => gmdate( 'Y-m-d H:i:s' )
		] );
	}

	/**
	 * Import physical robots.txt file.
	 *
	 * @since 4.0.0
	 *
	 * @return boolean Whether or not the file imported correctly.
	 */
	public function importPhysicalRobotsTxt( $network = false ) {
		$wpfs = aioseo()->helpers->wpfs();
		$file = trailingslashit( $wpfs->abspath() ) . 'robots.txt';
		if ( ! @$wpfs->is_readable( $file ) ) {
			return false;
		}

		$lines = @$wpfs->get_contents_array( $file );
		if ( ! $lines ) {
			return true;
		}

		$allRules = $this->extractRules( $lines );

		if ( $network ) {
			aioseo()->options->initNetwork();
		}

		$currentRules = $this->parseRules( aioseo()->options->tools->robots->rules );
		$allRules     = $this->mergeRules( $currentRules, $allRules, false, true );

		$robots = [];
		foreach ( $allRules as $userAgent => $rules ) {
			if ( empty( $userAgent ) ) {
				continue;
			}

			foreach ( $rules as $rule => $path ) {
				foreach ( $path as $p ) {
					if ( empty( $p ) ) {
						continue;
					}

					if ( '*' === $userAgent && 'allow' === $rule && '/wp-admin/admin-ajax.php' === $p ) {
						continue;
					}
					if ( '*' === $userAgent && 'disallow' === $rule && '/wp-admin/' === $p ) {
						continue;
					}

					$robots[] = wp_json_encode( [
						'userAgent'     => $userAgent,
						'rule'          => $rule,
						'directoryPath' => $p
					] );
				}
			}
		}

		aioseo()->options->tools->robots->rules = $robots;

		return true;
	}

	/**
	 * Checks if a physical robots.txt file exists.
	 *
	 * @since 4.0.0
	 *
	 * @return boolean True if it does, false if not.
	 */
	public function hasPhysicalRobotsTxt() {
		$wpfs       = aioseo()->helpers->wpfs();
		$accessType = get_filesystem_method();

		if ( 'direct' === $accessType ) {
			$file = trailingslashit( $wpfs->abspath() ) . 'robots.txt';

			return @$wpfs->exists( $file );
		}

		return false;
	}

	/**
	 * Delete robots.txt physical file.
	 *
	 * @since 4.0.0
	 *
	 * @return mixed The response from the delete method of WP_Filesystem.
	 */
	public function deletePhysicalRobotsTxt() {
		$wpfs = aioseo()->helpers->wpfs();
		$file = trailingslashit( $wpfs->abspath() ) . 'robots.txt';
		return @$wpfs->delete( $file );
	}

	/**
	 * Get the default Robots.txt rules (excluding our own).
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of robots.txt rules (excluding our own).
	 */
	public function getDefaultRules() {
		// First, we need to remove our filter, so that it doesn't run unintentionally.
		remove_filter( 'robots_txt', [ $this, 'buildRules' ], 10000 );

		ob_start();
		do_action( 'do_robots' );
		if ( is_admin() ) {
			// conflict with WooCommerce etc. cause the page to render as text/plain.
			header( 'Content-Type:text/html' );
		}
		$rules = ob_get_clean();

		// Add the filter back.
		add_filter( 'robots_txt', [ $this, 'buildRules' ], 10000, 2 );

		return $this->extractRules( explode( "\n", $rules ) );
	}

	/**
	 * Makes the rules unique.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $s An array to make unique.
	 * @return array    The unique array.
	 */
	private function robotsArrayUnique( &$s ) {
		foreach ( $s as $i => &$e ) {
			if ( is_array( $e ) && ! empty( $e ) ) {
				$e = $this->robotsArrayUnique( $e );
			}
		}
		if ( is_numeric( $i ) ) {
			return array_unique( $s, SORT_REGULAR );
		} else {
			return $s;
		}
	}

	/**
	 * A check to see if the rewrite rules are set.
	 * This isn't perfect, but it will help us know in most cases.
	 *
	 * @since 4.0.0
	 *
	 * @return boolean Whether the rewrite rules are set or not.
	 */
	public function rewriteRulesExist() {
		// We don't want to check if a migration is being restarted.
		if ( isset( $_GET['aioseo-v3-migration'] ) && 'i-want-to-migrate' === wp_unslash( $_GET['aioseo-v3-migration'] ) ) { // phpcs:ignore HM.Security.ValidatedSanitizedInput.InputNotSanitized
			return true;
		}

		// If we have a physical file, it's almost impossible to tell if the rewrite rules are set.
		// The only scenario is if we still get a 404.
		if ( $this->hasPhysicalRobotsTxt() ) {
			$response = wp_remote_get( aioseo()->helpers->getSiteUrl() . '/robots.txt' );
			if ( 300 <= wp_remote_retrieve_response_code( $response ) ) {
				return false;
			}

			// Since we got a 200, we are going to assume they exist. Once the file is deleted, we can tell for sure.
			return true;
		}

		$response = wp_remote_get( aioseo()->helpers->getSiteUrl() . '/robots.txt' );
		if ( 300 <= wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		return true;
	}
}