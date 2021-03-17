<?php
namespace AIOSEO\Plugin\Common\Migration;

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Handles the migration from V3 to V4.
 */
class Migration {

	/**
	 * The old V3 options.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	public $oldOptions = [];

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->meta    = new Meta();
		$this->helpers = new Helpers();

		add_action( 'aioseo_migrate_post_meta', [ $this->meta, 'migratePostMeta' ] );

		if ( wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}

		add_action( 'init', [ $this, 'init' ], 2000 );
	}

	/**
	 * Initializes the class.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function init() {
		// Since the version numbers may vary, we only want to compare the first 3 numbers.
		$lastActiveVersion = aioseo()->internalOptions->internal->lastActiveVersion;
		$lastActiveVersion = $lastActiveVersion ? explode( '-', $lastActiveVersion ) : null;

		if ( version_compare( $lastActiveVersion[0], '4.0.0', '<' ) ) {
			aioseo()->internalOptions->internal->migratedVersion = $lastActiveVersion[0];
			add_action( 'wp_loaded', [ $this, 'doMigration' ] );
		}

		// Run our migration again for V4 users between v4.0.0 and v4.0.4.
		if (
			version_compare( $lastActiveVersion[0], '4.0.0', '>=' ) &&
			version_compare( $lastActiveVersion[0], '4.0.4', '<' ) &&
			get_option( 'aioseop_options' )
		) {
			add_action( 'wp_loaded', [ $this, 'redoMetaMigration' ] );
		}

		// Stop migration for new v4 users where it was incorrectly triggered.
		if ( version_compare( $lastActiveVersion[0], '4.0.4', '=' ) && ! get_option( 'aioseop_options' ) ) {
			delete_transient( 'aioseo_v3_migration_in_progress_posts' );
			delete_transient( 'aioseo_v3_migration_in_progress_terms' );

			try {
				if ( as_next_scheduled_action( 'aioseo_migrate_post_meta' ) ) {
					as_unschedule_action( 'aioseo_migrate_post_meta', [], 'aioseo' );
				}
				if ( as_next_scheduled_action( 'aioseo_migrate_term_meta' ) ) {
					as_unschedule_action( 'aioseo_migrate_term_meta', [], 'aioseo' );
				}
			} catch ( \Exception $e ) {
				// Do nothing.
			}
		}
	}

	/**
	 * Starts the migration.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $oldOptions The old options. We pass it in directly via the Importer/Exporter.
	 * @return void
	 */
	public function doMigration( $oldOptions = [] ) {
		// If our tables do not exist, create them now.
		if ( ! aioseo()->db->tableExists( 'aioseo_posts' ) ) {
			aioseo()->updates->addInitialCustomTablesForV4();
		}

		$this->oldOptions = ( new OldOptions( $oldOptions ) )->oldOptions;

		if (
			! $this->oldOptions ||
			! is_array( $this->oldOptions ) ||
			! count( $this->oldOptions )
		) {
			return;
		}

		set_transient( 'aioseo_v3_migration_in_progress_posts', time(), WEEK_IN_SECONDS );

		$this->migrateSettings();
		$this->meta->migrateMeta();
	}

	/**
	 * Reruns the post meta migration.
	 *
	 * This is meant for users on v4.0.0, v4.0.1 or v4.0.2 where the migration might have failed.
	 *
	 * @since 4.0.3
	 *
	 * @return void
	 */
	public function redoMetaMigration() {
		set_transient( 'aioseo_v3_migration_in_progress_posts', time(), WEEK_IN_SECONDS );
		$this->meta->migrateMeta();
	}

	/**
	 * Migrates the plugin settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	protected function migrateSettings() {
		new GeneralSettings();

		if ( ! isset( $this->oldOptions['modules']['aiosp_feature_manager_options'] ) ) {
			new Sitemap();
			return;
		}

		$this->migrateFeatureManager();

		if ( isset( $this->oldOptions['modules']['aiosp_feature_manager_options']['aiosp_feature_manager_enable_opengraph'] ) ) {
			new SocialMeta();
		}

		if ( isset( $this->oldOptions['modules']['aiosp_feature_manager_options']['aiosp_feature_manager_enable_sitemap'] ) ) {
			new Sitemap();
		}

		if ( isset( $this->oldOptions['modules']['aiosp_feature_manager_options']['aiosp_feature_manager_enable_robots'] ) ) {
			new RobotsTxt();
		}

		if ( ! empty( $this->oldOptions['modules']['aiosp_feature_manager_options']['aiosp_feature_manager_enable_bad_robots'] ) ) {
			new BadRobots();
		}

		if ( aioseo()->helpers->isWpmlActive() ) {
			new Wpml();
		}
	}

	/**
	 * Migrates the Feature Manager settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	protected function migrateFeatureManager() {
		if ( empty( $this->oldOptions['modules']['aiosp_feature_manager_options'] ) ) {
			return;
		}

		if ( empty( $this->oldOptions['modules']['aiosp_feature_manager_options']['aiosp_feature_manager_enable_opengraph'] ) ) {
			aioseo()->options->social->facebook->general->enable = false;
			aioseo()->options->social->twitter->general->enable  = false;
		}

		if ( empty( $this->oldOptions['modules']['aiosp_feature_manager_options']['aiosp_feature_manager_enable_sitemap'] ) ) {
			aioseo()->options->sitemap->general->enable = false;
			aioseo()->options->sitemap->rss->enable     = false;
		}

		if ( ! empty( $this->oldOptions['modules']['aiosp_feature_manager_options']['aiosp_feature_manager_enable_robots'] ) ) {
			aioseo()->options->tools->robots->enable = true;
		}
	}
}