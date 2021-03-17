<?php

namespace AIOSEO\Plugin\Common\Social;

/**
 * Outputs our social meta.
 *
 * @since 4.0.0
 */
class Output {
	/**
	 * Returns the social meta for the current page.
	 *
	 * @since 4.0.0
	 *
	 * @return array The social meta.
	 */
	public function getMeta() {
		if (
			! is_front_page() &&
			! is_home() &&
			! is_singular() &&
			! aioseo()->helpers->isWooCommerceShopPage()
		) {
			return;
		}

		return apply_filters( 'aioseo_social_meta_tags', $this->getMetaHelper() );
	}

	/**
	 * Returns the social meta data.
	 *
	 * Acts as a helper for getMeta() so that we can easily override it in Pro.
	 *
	 * @since 4.0.0
	 *
	 * @return array The social meta.
	 */
	protected function getMetaHelper() {
		if ( ! aioseo()->options->social->facebook->general->enable && ! aioseo()->options->social->twitter->general->enable ) {
			return [];
		}

		$meta = [];
		if ( aioseo()->options->social->facebook->general->enable ) {
			$meta += $this->getFacebookMeta( $meta );
		}

		if ( aioseo()->options->social->twitter->general->enable ) {
			$meta += $this->getTwitterMeta( $meta );
		}
		return $meta;
	}

	/**
	 * Returns the Open Graph meta.
	 *
	 * @since 4.0.0
	 *
	 * @return array The Open Graph meta.
	 */
	public function getFacebookMeta() {
		if ( ! aioseo()->options->social->facebook->general->enable ) {
			return [];
		}

		$meta = [
			'og:site_name'   => aioseo()->helpers->encodeOutputHtml( aioseo()->social->facebook->getSiteName() ),
			'og:type'        => aioseo()->social->facebook->getObjectType(),
			'og:title'       => aioseo()->helpers->encodeOutputHtml( aioseo()->social->facebook->getTitle() ),
			'og:description' => aioseo()->helpers->encodeOutputHtml( aioseo()->social->facebook->getDescription() ),
			'og:url'         => set_url_scheme( esc_url( aioseo()->helpers->canonicalUrl() ) ),
			'fb:app_id'      => aioseo()->options->social->facebook->advanced->appId,
			'fb:admins'      => implode( ',', array_map( 'trim', explode( ',', aioseo()->options->social->facebook->advanced->adminId ) ) ),
		];

		$image = aioseo()->social->facebook->getImage();
		if ( $image ) {
			$image = is_array( $image ) ? $image[0] : $image;
			$image = set_url_scheme( esc_url( $image ) );

			$meta += [
				'og:image'            => $image,
				'og:image:secure_url' => is_ssl() ? $image : '',
				'og:image:width'      => aioseo()->social->facebook->getImageWidth(),
				'og:image:height'     => aioseo()->social->facebook->getImageHeight(),
			];
		}

		$video = aioseo()->social->facebook->getVideo();
		if ( $video ) {
			$video = set_url_scheme( esc_url( $video ) );

			$meta += [
				'og:video'            => $video,
				'og:video:secure_url' => is_ssl() ? $video : '',
				'og:video:width'      => aioseo()->social->facebook->getVideoWidth(),
				'og:video:height'     => aioseo()->social->facebook->getVideoHeight(),
			];
		}

		if ( ! empty( $meta['og:type'] ) && 'article' === $meta['og:type'] ) {
			$meta += [
				'article:section'        => aioseo()->social->facebook->getSection(),
				'article:tag'            => aioseo()->social->facebook->getArticleTags(),
				'article:published_time' => aioseo()->social->facebook->getPublishedTime(),
				'article:modified_time'  => aioseo()->social->facebook->getModifiedTime(),
				'article:publisher'      => aioseo()->social->facebook->getPublisher(),
				'article:author'         => aioseo()->social->facebook->getAuthor()
			];
		}

		return array_filter( $meta );
	}

	/**
	 * Returns the Twitter meta.
	 *
	 * @since 4.0.0
	 *
	 * @return array The Twitter meta.
	 */
	public function getTwitterMeta() {
		if ( ! aioseo()->options->social->twitter->general->enable ) {
			return [];
		}

		$meta = [
			'twitter:card'        => aioseo()->social->twitter->getCardType(),
			'twitter:site'        => aioseo()->social->twitter->prepareUsername( aioseo()->social->twitter->getTwitterUrl() ),
			'twitter:domain'      => aioseo()->helpers->getSiteDomain(),
			'twitter:title'       => aioseo()->helpers->encodeOutputHtml( aioseo()->social->twitter->getTitle() ),
			'twitter:description' => aioseo()->helpers->encodeOutputHtml( aioseo()->social->twitter->getDescription() ),
			'twitter:creator'     => aioseo()->social->twitter->getCreator()
		];

		$image = aioseo()->social->twitter->getImage();
		if ( $image ) {
			if ( is_array( $image ) ) {
				$meta['twitter:image'] = $image[0];
			} else {
				$meta['twitter:image'] = $image;
			}
		}

		if ( is_singular() ) {
			$additionalData = apply_filters( 'aioseo_social_twitter_additional_data', aioseo()->social->twitter->getAdditionalData() );
			if ( $additionalData ) {
				$i = 1;
				foreach ( $additionalData as $data ) {
					$meta[ "twitter:label$i" ] = $data['label'];
					$meta[ "twitter:data$i" ]  = $data['value'];
					$i++;
				}
			}
		}

		return array_filter( $meta );
	}
}