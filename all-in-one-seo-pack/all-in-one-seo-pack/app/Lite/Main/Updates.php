<?php
namespace AIOSEO\Plugin\Lite\Main;

use AIOSEO\Plugin\Common\Main as CommonMain;

/**
 * Common class with methods that are called.
 *
 * @since 4.0.0
 */
class Updates extends CommonMain\Updates {
	/**
	 * Run our version updates here.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $oldVersion The old version number to compare against.
	 * @return void
	 */
	protected function doVersionUpdates( $oldVersion ) {
		parent::doVersionUpdates( $oldVersion );

		if ( version_compare( $oldVersion, '2.3.3', '<' ) ) {
			$this->badBots201603();
		}

		if ( version_compare( $oldVersion, '2.3.4.1', '<' ) ) {
			$this->badBotsRemoveYandex201604();
		}

		if ( version_compare( $oldVersion, '2.3.9', '<' ) ) {
			$this->badBotsRemoveSeznambot201608();
		}

		if ( version_compare( $oldVersion, '2.9', '<' ) ) {
			$this->badBotsRemoveSemrush201810();
		}
	}
}