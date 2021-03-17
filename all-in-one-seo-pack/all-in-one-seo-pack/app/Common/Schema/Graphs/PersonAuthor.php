<?php
namespace AIOSEO\Plugin\Common\Schema\Graphs;

/**
 * Person Author graph class.
 *
 * This a secondary Person graph for post authors and BuddyPress profile pages.
 *
 * @since 4.0.0
 */
class PersonAuthor extends Person {

	/**
	 * Returns the graph data.
	 *
	 * @since 4.0.0
	 *
	 * @return array $data The graph data.
	 */
	public function get() {
		$post = aioseo()->helpers->getPost();
		if ( ! $post ) {
			return [];
		}

		$userId = $post->post_author;
		if ( function_exists( 'bp_is_user' ) && bp_is_user() ) {
			$userId = intval( wp_get_current_user()->ID );
		}

		if ( ! $userId ) {
			return [];
		}

		$name = trim( sprintf( '%1$s %2$s', get_the_author_meta( 'first_name', $userId ), get_the_author_meta( 'last_name', $userId ) ) );
		if ( ! $name ) {
			$name = get_the_author_meta( 'display_name', $userId );
		}

		$authorUrl = get_author_posts_url( $post->post_author );

		$data = [
			'@type' => 'Person',
			'@id'   => $authorUrl . '#author',
			'url'   => $authorUrl,
			'name'  => $name
		];

		$avatar = $this->avatar( $userId, 'authorImage' );
		if ( $avatar ) {
			$data['image'] = $avatar;
		}

		$socialUrls = $this->socialUrls( $userId );
		if ( $socialUrls ) {
			$data['sameAs'] = $socialUrls;
		}

		if ( is_author() ) {
			$data['mainEntityOfPage'] = [
				'#id' => aioseo()->schema->context['url'] . '#profilepage'
			];
		}
		return $data;
	}
}