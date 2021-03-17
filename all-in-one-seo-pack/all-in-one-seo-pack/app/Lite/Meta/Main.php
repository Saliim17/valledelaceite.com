<?php
namespace AIOSEO\Plugin\Lite\Meta;

use \AIOSEO\Plugin\Common\Meta as CommonMeta;

/**
 * Handles our ouput in the document head.
 *
 * @since 4.0.0
 */
class Main extends CommonMeta\Main {
	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		parent::__construct();
		$this->included = new CommonMeta\Included();
	}
}