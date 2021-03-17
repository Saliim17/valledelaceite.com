<?php
namespace AIOSEO\Plugin\Common\ImportExport;

/**
 * Contains helper methods for the import from other plugins.
 *
 * @since 4.0.0
 */
abstract class Helpers {

	/**
	 * Maps a list of old settings from V3 to their counterparts in V4.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $mappings      The old settings, mapped to their new settings.
	 * @param  array $group         The old settings group.
	 * @param  bool  $convertMacros Whether to convert the old V3 macros to V4 smart tags.
	 * @return void
	 */
	public function mapOldToNew( $mappings, $group, $convertMacros = false ) {
		if (
			! is_array( $mappings ) ||
			! is_array( $group ) ||
			! count( $mappings ) ||
			! count( $group )
		) {
			return;
		}

		foreach ( $mappings as $name => $values ) {
			if ( ! isset( $group[ $name ] ) ) {
				continue;
			}

			$error      = false;
			$options    = aioseo()->options->noConflict();
			$lastOption = '';
			for ( $i = 0; $i < count( $values['newOption'] ); $i++ ) {
				$lastOption = $values['newOption'][ $i ];
				if ( ! $options->has( $lastOption, false ) ) {
					$error = true;
					break;
				};
				if ( count( $values['newOption'] ) - 1 !== $i ) {
					$options = $options->$lastOption;
				}
			}

			if ( $error ) {
				continue;
			}

			switch ( $values['type'] ) {
				case 'boolean':
					if ( ! empty( $group[ $name ] ) ) {
						$options->$lastOption = true;
						break;
					}
					$options->$lastOption = false;
					break;
				case 'integer':
				case 'float':
					$value = aioseo()->helpers->sanitizeOption( $group[ $name ] );
					if ( $value ) {
						$options->$lastOption = $value;
					}
					break;
				default:
					$value = $group[ $name ];
					if ( $convertMacros ) {
						$value = $this->macrosToSmartTags( $value );
					}
					$options->$lastOption = aioseo()->helpers->sanitizeOption( $value );
					break;
			}
		}

		aioseo()->options->refresh();
	}
}