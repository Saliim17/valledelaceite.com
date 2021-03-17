<?php
namespace AIOSEO\Plugin\Common\Admin;

/**
 * Class that holds our setup wizard.
 *
 * @since 4.0.0
 */
class SetupWizard {
	/**
	 * Class Constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'addDashboardPage' ] );
		add_action( 'admin_init', [ $this, 'maybeLoadOnboardingWizard' ] );
		add_action( 'admin_init', [ $this, 'redirect' ], 9999 );
	}

	/**
	 * Onboarding Wizard redirect.
	 *
	 * This function checks if a new install or update has just occurred. If so,
	 * then we redirect the user to the appropriate page.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function redirect() {
		// Check if we should consider redirection.
		if ( ! get_transient( 'aioseo_activation_redirect' ) ) {
			return;
		}

		// If we are redirecting, clear the transient so it only happens once.
		delete_transient( 'aioseo_activation_redirect' );

		// Check option to disable welcome redirect.
		if ( get_option( 'aioseo_activation_redirect', false ) ) {
			return;
		}

		// Only do this for single site installs.
		if ( isset( $_GET['activate-multi'] ) || is_network_admin() ) {
			return;
		}

		wp_safe_redirect( admin_url( 'index.php?page=aioseo-setup-wizard' ) );
		exit;
	}

	/**
	 * Adds a dashboard page for our setup wizard.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function addDashboardPage() {
		add_dashboard_page( '', '', 'aioseo_setup_wizard', 'aioseo-setup-wizard', '' );
		remove_submenu_page( 'index.php', 'aioseo-setup-wizard' );
	}

	/**
	 * Checks to see if we should load the setup wizard.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function maybeLoadOnboardingWizard() {
		// Don't load the interface if doing an ajax call.
		if ( wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}

		// Check for wizard-specific parameter
		// Allow plugins to disable the setup wizard
		// Check if current user is allowed to save settings.
		if (
			! isset( $_GET['page'] ) ||
			'aioseo-setup-wizard' !== wp_unslash( $_GET['page'] ) || // phpcs:ignore HM.Security.ValidatedSanitizedInput.InputNotSanitized
			! current_user_can( 'aioseo_setup_wizard' )
		) {
			return;
		}

		set_current_screen();

		// Remove an action in the Gutenberg plugin ( not core Gutenberg ) which throws an error.
		remove_action( 'admin_print_styles', 'gutenberg_block_editor_admin_print_styles' );

		$this->loadOnboardingWizard();
	}

	/**
	 * Load the Onboarding Wizard template.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function loadOnboardingWizard() {
		$this->enqueueScripts();
		$this->setupWizardHeader();
		$this->setupWizardContent();
		$this->setupWizardFooter();
		exit;
	}

	/**
	 * Enqueue's scripts for the setup wizard.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function enqueueScripts() {
		// We don't want any plugin adding notices to our screens. Let's clear them out here.
		remove_all_actions( 'admin_notices' );
		remove_all_actions( 'all_admin_notices' );

		// Scripts.
		aioseo()->helpers->enqueueScript(
			'aioseo-vendors',
			'js/chunk-vendors.js'
		);
		aioseo()->helpers->enqueueScript(
			'aioseo-common',
			'js/chunk-common.js'
		);
		aioseo()->helpers->enqueueScript(
			'aioseo-setup-wizard-script',
			'js/setup-wizard.js'
		);

		// Styles.
		$rtl = is_rtl() ? '.rtl' : '';
		aioseo()->helpers->enqueueStyle(
			'aioseo-vendors',
			"css/chunk-vendors$rtl.css"
		);
		aioseo()->helpers->enqueueStyle(
			'aioseo-common',
			"css/chunk-common$rtl.css"
		);
		// aioseo()->helpers->enqueueStyle(
		//  'aioseo-setup-wizard-style',
		//  "css/setup-wizard$rtl.css"
		// );
		// aioseo()->helpers->enqueueStyle(
		//  'aioseo-setup-wizard-vendors-style',
		//  "css/chunk-setup-wizard-vendors$rtl.css"
		// );

		wp_localize_script(
			'aioseo-setup-wizard-script',
			'aioseo',
			aioseo()->helpers->getVueData( 'setup-wizard' )
		);

		wp_enqueue_style( 'common' );
		wp_enqueue_media();
	}

	/**
	 * Outputs the simplified header used for the Onboarding Wizard.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function setupWizardHeader() {
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta name="viewport" content="width=device-width"/>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
			<title>
			<?php
				// Translators: 1 - The plugin name ("All in One SEO").
				echo sprintf( esc_html__( '%1$s &rsaquo; Onboarding Wizard', 'all-in-one-seo-pack' ), esc_html( AIOSEO_PLUGIN_SHORT_NAME ) );
			?>
			</title>
			<?php do_action( 'admin_print_scripts' ); ?>
			<?php do_action( 'admin_print_styles' ); ?>
			<?php do_action( 'admin_head' ); ?>
		</head>
		<body class="aioseo-setup-wizard">
		<?php
	}

	/**
	 * Outputs the content of the current step.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function setupWizardContent() {
		echo '<div id="aioseo-app"></div>';
	}

	/**
	 * Outputs the simplified footer used for the Onboarding Wizard.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function setupWizardFooter() {
		?>
		<?php
		wp_print_scripts( 'aioseo-vendors' );
		wp_print_scripts( 'aioseo-common' );
		wp_print_scripts( 'aioseo-setup-wizard-script' );
		do_action( 'admin_footer', '' );
		do_action( 'admin_print_footer_scripts' );
		// do_action( 'customize_controls_print_footer_scripts' );
		?>
		</body>
		</html>
		<?php
	}
}