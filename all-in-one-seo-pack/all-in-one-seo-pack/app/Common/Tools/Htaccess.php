<?php
namespace AIOSEO\Plugin\Common\Tools;

class Htaccess {
	/**
	 * The path to the .htaccess file.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	private $path = '';

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->path = ABSPATH . '.htaccess';
	}

	/**
	 * Get the contents of the .htaccess file.
	 *
	 * @since 4.0.0
	 *
	 * @return string The contents of the file.
	 */
	public function getContents() {
		$wpfs = aioseo()->helpers->wpfs();
		if ( ! @$wpfs->exists( $this->path ) ) {
			return false;
		}

		return @$wpfs->get_contents( $this->path );
	}

	/**
	 * Saves the contents of the .htaccess file.
	 *
	 * @since 4.0.0
	 *
	 * @param  string  $contents The contents to write.
	 * @return boolean           True if the file was updated.
	 */
	public function saveContents( $contents ) {
		$wpfs       = aioseo()->helpers->wpfs();
		$fileExists = @$wpfs->exists( $this->path );
		if ( ! $fileExists || @$wpfs->is_writable( $this->path ) ) {
			$success = @$wpfs->put_contents( $this->path, $contents );
			if ( false === $success ) {
				return false;
			}

			return true;
		}

		return false;
	}
}