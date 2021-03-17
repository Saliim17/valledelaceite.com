<?php
namespace AIOSEO\Plugin\Common\Schema\Graphs;

/**
 * The base graph class.
 *
 * @since 4.0.0
 */
abstract class Graph {

	/**
	 * Returns the graph data.
	 *
	 * @since 4.0.0
	 */
	abstract public function get();

	/**
	 * Builds the graph data for a given image with a given schema ID.
	 *
	 * @since 4.0.0
	 *
	 * @param int    $imageId The image ID.
	 * @param string $graphId The graph ID.
	 * @return array $data    The image graph data.
	 */
	protected function image( $imageId, $graphId ) {
		$imageId = is_string( $imageId ) ? attachment_url_to_postid( $imageId ) : $imageId;

		$data = [
			'@type' => 'ImageObject',
			'@id'   => trailingslashit( home_url() ) . '#' . $graphId,
			'url'   => wp_get_attachment_image_url( $imageId, 'full' ),
		];

		$metaData = wp_get_attachment_metadata( $imageId );
		if ( $metaData ) {
			$data['width']  = $metaData['width'];
			$data['height'] = $metaData['height'];
		}

		$caption = wp_get_attachment_caption( $imageId );
		if ( false !== $caption || ! empty( $caption ) ) {
			$data['caption'] = $caption;
		}
		return array_filter( $data );
	}

	/**
	 * Returns the graph data for the avatar of a given user.
	 *
	 * @since 4.0.0
	 *
	 * @param  int    $userId  The user ID.
	 * @param  string $graphId The graph ID.
	 * @return array           The graph data.
	 */
	protected function avatar( $userId, $graphId ) {
		if ( ! get_option( 'show_avatars' ) ) {
			return [];
		}

		$avatar = get_avatar_data( $userId );
		if ( ! $avatar['found_avatar'] ) {
			return [];
		}

		$caption = trim( sprintf( '%1$s %2$s', get_the_author_meta( 'first_name', $userId ), get_the_author_meta( 'last_name', $userId ) ) );
		if ( ! $caption ) {
			$caption = get_the_author_meta( 'display_name', $userId );
		}

		return array_filter( [
			'@type'   => 'ImageObject',
			'@id'     => aioseo()->schema->context['url'] . "#$graphId",
			'url'     => $avatar['url'],
			'width'   => $avatar['width'],
			'height'  => $avatar['height'],
			'caption' => $caption
		] );
	}

	/**
	 * Returns the social media URLs for the author.
	 *
	 * @since 4.0.0
	 *
	 * @param  int   $authorId   The author ID.
	 * @return array $socialUrls The social media URLs.
	 */
	protected function socialUrls( $authorId = false ) {
		$socialUrls = [];
		if ( aioseo()->options->social->profiles->sameUsername->enable ) {
			$username = aioseo()->options->social->profiles->sameUsername->username;
			$urls = [
				'facebookPageUrl' => "https://facebook.com/$username",
				'twitterUrl'      => "https://twitter.com/$username",
				'instagramUrl'    => "https://instagram.com/$username",
				'pinterestUrl'    => "https://pinterest.com/$username",
				'youtubeUrl'      => "https://youtube.com/$username",
				'linkedinUrl'     => "https://linkedin.com/in/$username",
				'tumblrUrl'       => "https://$username.tumblr.com",
				'yelpPageUrl'     => "https://yelp.com/biz/$username",
				'soundCloudUrl'   => "https://soundcloud.com/$username",
				'wikipediaUrl'    => "https://wikipedia.com/wiki/$username",
				'myspaceUrl'      => "https://myspace.com/$username"
			];

			$included = aioseo()->options->social->profiles->sameUsername->included;
			foreach ( $urls as $name => $value ) {
				if ( in_array( $name, $included, true ) ) {
					$socialUrls[ $name ] = $value;
				} else {
					$notIncluded = aioseo()->options->social->profiles->urls->$name;
					if ( ! empty( $notIncluded ) ) {
						$socialUrls[ $name ] = $notIncluded;
					}
				}
			}
		} else {
			$socialUrls = [
				'facebookPageUrl' => aioseo()->options->social->profiles->urls->facebookPageUrl,
				'twitterUrl'      => aioseo()->options->social->profiles->urls->twitterUrl,
				'instagramUrl'    => aioseo()->options->social->profiles->urls->instagramUrl,
				'pinterestUrl'    => aioseo()->options->social->profiles->urls->pinterestUrl,
				'youtubeUrl'      => aioseo()->options->social->profiles->urls->youtubeUrl,
				'linkedinUrl'     => aioseo()->options->social->profiles->urls->linkedinUrl,
				'tumblrUrl'       => aioseo()->options->social->profiles->urls->tumblrUrl,
				'yelpPageUrl'     => aioseo()->options->social->profiles->urls->yelpPageUrl,
				'soundCloudUrl'   => aioseo()->options->social->profiles->urls->soundCloudUrl,
				'wikipediaUrl'    => aioseo()->options->social->profiles->urls->wikipediaUrl,
				'myspaceUrl'      => aioseo()->options->social->profiles->urls->myspaceUrl
			];
		}

		if ( ! $authorId ) {
			return array_values( array_filter( $socialUrls ) );
		}

		if ( aioseo()->options->social->facebook->general->showAuthor ) {
			$meta = get_the_author_meta( 'aioseo_facebook', $authorId );
			if ( $meta ) {
				$socialUrls['facebookPageUrl'] = $meta;
			}
		} else {
			$socialUrls['facebookPageUrl'] = '';
		}

		if ( aioseo()->options->social->twitter->general->showAuthor ) {
			$meta = get_the_author_meta( 'aioseo_twitter', $authorId );
			if ( $meta ) {
				$socialUrls['twitterUrl'] = $meta;
			}
		} else {
			$socialUrls['twitterUrl'] = '';
		}
		return array_values( array_filter( $socialUrls ) );
	}
}