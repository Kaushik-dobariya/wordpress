<?php
/**
 * SpiceCraft Core - Testimonial Helper Functions
 *
 * Provides query helpers, star rating rendering, template partial helpers,
 * and canonical URL resolution for client endorsements and testimonials.
 *
 * @package SpiceCraft_Core
 * @since 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieve published testimonials with optional filtering and custom sorting.
 *
 * @param array $args Query arguments.
 * @return WP_Post[] Array of post objects.
 */
function spicecraft_get_testimonials( $args = array() ) {
	$defaults = array(
		'post_type'      => 'sc_testimonial',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'featured_only'  => false,
		'orderby'        => 'order', // order, date, rating
		'order'          => 'ASC',
	);

	$parsed = wp_parse_args( $args, $defaults );

	$query_args = array(
		'post_type'      => 'sc_testimonial',
		'post_status'    => 'publish',
		'posts_per_page' => intval( $parsed['posts_per_page'] ),
		'no_found_rows'  => true,
	);

	// Featured filter
	if ( ! empty( $parsed['featured_only'] ) ) {
		$query_args['meta_query'][] = array(
			'key'     => '_sc_testimonial_featured',
			'value'   => '1',
			'compare' => '=',
		);
	}

	// Ordering
	if ( 'order' === $parsed['orderby'] ) {
		$query_args['meta_key'] = '_sc_testimonial_order';
		$query_args['orderby']  = array(
			'meta_value_num' => 'ASC',
			'date'           => 'DESC',
		);
	} elseif ( 'rating' === $parsed['orderby'] ) {
		$query_args['meta_key'] = '_sc_testimonial_rating';
		$query_args['orderby']  = array(
			'meta_value_num' => 'DESC',
			'date'           => 'DESC',
		);
	} else {
		$query_args['orderby'] = 'date';
		$query_args['order']   = $parsed['order'];
	}

	// Specific IDs
	if ( ! empty( $parsed['post__in'] ) ) {
		$query_args['post__in'] = array_map( 'absint', (array) $parsed['post__in'] );
	}

	$posts = get_posts( $query_args );
	return is_array( $posts ) ? $posts : array();
}

/**
 * Retrieve featured testimonials.
 *
 * @param int $limit Max count.
 * @return WP_Post[]
 */
function spicecraft_get_featured_testimonials( $limit = 3 ) {
	return spicecraft_get_testimonials(
		array(
			'featured_only'  => true,
			'posts_per_page' => $limit,
		)
	);
}

/**
 * Render accessible star rating markup.
 *
 * @param int  $rating Star count (1 to 5).
 * @param bool $echo   Whether to echo or return HTML.
 * @return string
 */
function spicecraft_render_testimonial_stars( $rating, $echo = true ) {
	$rating = absint( $rating );
	if ( $rating < 1 || $rating > 5 ) {
		return '';
	}

	ob_start();
	?>
	<div class="sc-testimonial-stars" aria-label="<?php echo esc_attr( sprintf( _n( '%d star rating', '%d stars rating', $rating, 'spicecraft' ), $rating ) ); ?>">
		<span class="screen-reader-text"><?php echo esc_html( sprintf( __( '%d out of 5 stars', 'spicecraft' ), $rating ) ); ?></span>
		<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
			<?php if ( $i <= $rating ) : ?>
				<svg class="sc-star-icon sc-star-icon--filled" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
					<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
				</svg>
			<?php else : ?>
				<svg class="sc-star-icon sc-star-icon--empty" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
				</svg>
			<?php endif; ?>
		<?php endfor; ?>
	</div>
	<?php
	$html = ob_get_clean();

	if ( $echo ) {
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	return $html;
}

/**
 * Retrieve canonical public URL for Testimonials.
 *
 * @return string
 */
function spicecraft_get_testimonials_url() {
	$archive_link = get_post_type_archive_link( 'sc_testimonial' );
	if ( $archive_link ) {
		return $archive_link;
	}

	$page = get_page_by_path( 'testimonials' );
	if ( $page && 'publish' === $page->post_status ) {
		return get_permalink( $page->ID );
	}

	return home_url( '/testimonials/' );
}
