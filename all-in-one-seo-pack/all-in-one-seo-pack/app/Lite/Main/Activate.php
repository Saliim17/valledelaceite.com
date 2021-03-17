<?php
namespace AIOSEO\Plugin\Lite\Main;

use AIOSEO\Plugin\Common\Main as CommonMain;

/**
 * Activate class with methods that are called.
 *
 * @since 4.0.0
 */
class Activate extends CommonMain\Activate {
	/**
	 * Runs on activate.
	 *
	 * @param  bool $networkWide Whether or not this is a network wide activation.
	 * @return void
	 */
	public function activate( $networkWide ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		aioseo()->access->addCapabilities();

		// Make sure our tables exist.
		aioseo()->updates->addInitialCustomTablesForV4();

		// Set the activation timestamps.
		$time = time();
		aioseo()->internalOptions->internal->activated = $time;

		if ( ! aioseo()->internalOptions->internal->firstActivated ) {
			aioseo()->internalOptions->internal->firstActivated = $time;
		}
	}
}