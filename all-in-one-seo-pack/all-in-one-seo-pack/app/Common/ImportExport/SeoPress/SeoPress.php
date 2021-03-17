<?php
namespace AIOSEO\Plugin\Common\ImportExport\SeoPress;

class SeoPress {

	/**
	 * Starts the import.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function doImport() {
		// @TODO: [V4+] Write this once SEOPress is going in.
	}

	/**
	 * Imports the settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function migrateSettings() {
		$this->helpers = new Helpers();

		new SearchAppearance();
	}
}