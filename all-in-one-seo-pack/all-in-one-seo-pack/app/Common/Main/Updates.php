<?php
namespace AIOSEO\Plugin\Common\Main;

/**
 * Updater class.
 *
 * @since 4.0.0
 */
class Updates {

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		if ( wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}

		add_action( 'init', [ $this, 'init' ], 1001 );
		add_action( 'init', [ $this, 'runUpdates' ], 1002 );
		add_action( 'init', [ $this, 'updateLatestVersion' ], 3000 );
	}

	/**
	 * Sets the latest active version if it is not set yet.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function init() {
		if ( '0.0' !== aioseo()->internalOptions->internal->lastActiveVersion ) {
			return;
		}

		// It's possible the user may not have capabilities. Let's add them now.
		aioseo()->access->addCapabilities();

		$oldOptions = get_option( 'aioseop_options' );
		if ( empty( $oldOptions ) && ( ! is_network_admin() || ! isset( $_GET['activate-multi'] ) ) ) {
			// Sets 30 second transient for welcome screen redirect on activation.
			set_transient( 'aioseo_activation_redirect', true, 30 );
		}

		if ( ! empty( $oldOptions['last_active_version'] ) ) {
			aioseo()->internalOptions->internal->lastActiveVersion = $oldOptions['last_active_version'];
		}

		$this->addInitialCustomTablesForV4();
		add_action( 'wp_loaded', [ $this, 'setDefaultSocialImages' ], 1001 );
	}

	/**
	 * Runs our migrations.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function runUpdates() {
		$lastActiveVersion = aioseo()->internalOptions->internal->lastActiveVersion;
		if ( version_compare( $lastActiveVersion, '4.0.5', '<' ) ) {
			$this->addImageScanDateColumn();
		}

		if ( version_compare( $lastActiveVersion, '4.0.6', '<' ) ) {
			$this->disableTwitterUseOgDefault();
			$this->updateMaxImagePreviewDefault();
		}

		if ( ! aioseo()->pro && version_compare( $lastActiveVersion, '4.0.6', '=' ) && 'posts' !== get_option( 'show_on_front' ) ) {
			aioseo()->migration->helpers->redoMigration();
		}
	}

	/**
	 * Updates the latest version after all migrations and updates have run.
	 *
	 * @since 4.0.3
	 *
	 * @return void
	 */
	public function updateLatestVersion() {
		aioseo()->internalOptions->internal->lastActiveVersion = aioseo()->version;
	}

	/**
	 * Adds our custom tables for V4.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function addInitialCustomTablesForV4() {
		$db             = aioseo()->db->db;
		$charsetCollate = '';

		if ( ! empty( $db->charset ) ) {
			$charsetCollate .= "DEFAULT CHARACTER SET {$db->charset}";
		}
		if ( ! empty( $db->collate ) ) {
			$charsetCollate .= " COLLATE {$db->collate}";
		}

		// Check for notifications table.
		if ( ! aioseo()->db->tableExists( 'aioseo_notifications' ) ) {
			$tableName = $db->prefix . 'aioseo_notifications';

			aioseo()->db->execute(
				"CREATE TABLE {$tableName} (
					id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					slug varchar(13) NOT NULL,
					title text NOT NULL,
					content longtext NOT NULL,
					type varchar(64) NOT NULL,
					level text NOT NULL,
					notification_id bigint(20) unsigned DEFAULT NULL,
					notification_name varchar(255) DEFAULT NULL,
					start datetime DEFAULT NULL,
					end datetime DEFAULT NULL,
					button1_label varchar(255) DEFAULT NULL,
					button1_action varchar(255) DEFAULT NULL,
					button2_label varchar(255) DEFAULT NULL,
					button2_action varchar(255) DEFAULT NULL,
					dismissed tinyint(1) NOT NULL DEFAULT 0,
					created datetime NOT NULL,
					updated datetime NOT NULL,
					PRIMARY KEY (id),
					UNIQUE KEY ndx_aioseo_notifications_slug (slug),
					KEY ndx_aioseo_notifications_dates (start, end),
					KEY ndx_aioseo_notifications_type (type),
					KEY ndx_aioseo_notifications_dismissed (dismissed)
				) {$charsetCollate};"
			);
		}

		if ( ! aioseo()->db->tableExists( 'aioseo_posts' ) ) {
			$tableName = $db->prefix . 'aioseo_posts';

			aioseo()->db->execute(
				"CREATE TABLE {$tableName} (
					id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					post_id bigint(20) unsigned NOT NULL,
					title text DEFAULT NULL,
					description text DEFAULT NULL,
					keywords mediumtext DEFAULT NULL,
					keyphrases longtext DEFAULT NULL,
					page_analysis longtext DEFAULT NULL,
					canonical_url text DEFAULT NULL,
					og_title text DEFAULT NULL,
					og_description text DEFAULT NULL,
					og_object_type varchar(64) DEFAULT 'default',
					og_image_type varchar(64) DEFAULT 'default',
					og_image_custom_url text DEFAULT NULL,
					og_image_custom_fields text DEFAULT NULL,
					og_custom_image_width int(11) DEFAULT NULL,
					og_custom_image_height int(11) DEFAULT NULL,
					og_video varchar(255) DEFAULT NULL,
					og_custom_url text DEFAULT NULL,
					og_article_section text DEFAULT NULL,
					og_article_tags text DEFAULT NULL,
					twitter_use_og tinyint(1) DEFAULT 1,
					twitter_card varchar(64) DEFAULT 'default',
					twitter_image_type varchar(64) DEFAULT 'default',
					twitter_image_custom_url text DEFAULT NULL,
					twitter_image_custom_fields text DEFAULT NULL,
					twitter_title text DEFAULT NULL,
					twitter_description text DEFAULT NULL,
					seo_score int(11) DEFAULT 0 NOT NULL,
					schema_type varchar(20) DEFAULT NULL,
					schema_type_options longtext DEFAULT NULL,
					pillar_content tinyint(1) DEFAULT NULL,
					robots_default tinyint(1) DEFAULT 1 NOT NULL,
					robots_noindex tinyint(1) DEFAULT 0 NOT NULL,
					robots_noarchive tinyint(1) DEFAULT 0 NOT NULL,
					robots_nosnippet tinyint(1) DEFAULT 0 NOT NULL,
					robots_nofollow tinyint(1) DEFAULT 0 NOT NULL,
					robots_noimageindex tinyint(1) DEFAULT 0 NOT NULL,
					robots_noodp tinyint(1) DEFAULT 0 NOT NULL,
					robots_notranslate tinyint(1) DEFAULT 0 NOT NULL,
					robots_max_snippet int(11) DEFAULT NULL,
					robots_max_videopreview int(11) DEFAULT NULL,
					robots_max_imagepreview varchar(20) DEFAULT 'none',
					tabs mediumtext DEFAULT NULL,
					images longtext DEFAULT NULL,
					priority tinytext DEFAULT NULL,
					frequency tinytext DEFAULT NULL,
					videos longtext DEFAULT NULL,
					video_thumbnail text DEFAULT NULL,
					video_scan_date datetime DEFAULT NULL,
					location text DEFAULT NULL,
					local_seo longtext DEFAULT NULL,
					created datetime NOT NULL,
					updated datetime NOT NULL,
					PRIMARY KEY (id),
					KEY ndx_aioseo_posts_post_id (post_id)
				) {$charsetCollate};"
			);
		}
	}

	/**
	 * Sets the default social images.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function setDefaultSocialImages() {
		$siteLogo = aioseo()->helpers->getSiteLogoUrl();
		if ( $siteLogo && ! aioseo()->internalOptions->internal->migratedVersion ) {
			if ( ! aioseo()->options->social->facebook->general->defaultImagePosts ) {
				aioseo()->options->social->facebook->general->defaultImagePosts = $siteLogo;
			}
			if ( ! aioseo()->options->social->twitter->general->defaultImagePosts ) {
				aioseo()->options->social->twitter->general->defaultImagePosts = $siteLogo;
			}
		}
	}

	/**
	 * Adds the image scan date column to our posts table.
	 *
	 * @since 4.0.5
	 *
	 * @return void
	 */
	public function addImageScanDateColumn() {
		if ( ! aioseo()->db->columnExists( 'aioseo_posts', 'image_scan_date' ) ) {
			$tableName = aioseo()->db->db->prefix . 'aioseo_posts';
			aioseo()->db->execute(
				"ALTER TABLE {$tableName}
				ADD image_scan_date datetime DEFAULT NULL AFTER images"
			);
		}
	}

	/**
	 * Modifes the default value of the twitter_use_og column.
	 *
	 * @since 4.0.6
	 *
	 * @return void
	 */
	public function disableTwitterUseOgDefault() {
		if ( aioseo()->db->tableExists( 'aioseo_posts' ) ) {
			$tableName = aioseo()->db->db->prefix . 'aioseo_posts';
			aioseo()->db->execute(
				"ALTER TABLE {$tableName}
				MODIFY twitter_use_og tinyint(1) DEFAULT 0"
			);
		}
	}

	/**
	 * Modifes the default value of the robots_max_imagepreview column.
	 *
	 * @since 4.0.6
	 *
	 * @return void
	 */
	public function updateMaxImagePreviewDefault() {
		if ( aioseo()->db->tableExists( 'aioseo_posts' ) ) {
			$tableName = aioseo()->db->db->prefix . 'aioseo_posts';
			aioseo()->db->execute(
				"ALTER TABLE {$tableName}
				MODIFY robots_max_imagepreview varchar(20) DEFAULT 'large'"
			);
		}
	}
}