<?php
/**
 * Template part for displaying an article card in blog grids and related posts
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post_id      = get_the_ID();
$categories   = get_the_category( $post_id );
$primary_cat  = ! empty( $categories ) ? $categories[0] : null;
$reading_time = function_exists( 'spicecraft_get_reading_time' ) ? spicecraft_get_reading_time( $post_id ) : 3;
$is_featured  = (bool) get_post_meta( $post_id, '_sc_post_is_featured', true );
$show_author  = function_exists( 'spicecraft_get_blog_option' ) ? (bool) spicecraft_get_blog_option( 'show_author', true ) : true;
$show_reading = function_exists( 'spicecraft_get_blog_option' ) ? (bool) spicecraft_get_blog_option( 'show_reading_time', true ) : true;
$show_date    = function_exists( 'spicecraft_get_blog_option' ) ? (bool) spicecraft_get_blog_option( 'show_date', true ) : true;
$card_class   = 'sc-article-card' . ( $is_featured ? ' sc-article-card--featured' : '' );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( $card_class ); ?>>
	<!-- Media Header -->
	<div class="sc-article-card__media">
		<a href="<?php the_permalink(); ?>" class="sc-article-card__media-link" tabindex="-1" aria-hidden="true">
			<?php if ( has_post_thumbnail( $post_id ) ) : ?>
				<?php
				the_post_thumbnail(
					'medium_large',
					array(
						'class'   => 'sc-article-card__img',
						'alt'     => the_title_attribute( array( 'echo' => false ) ),
						'loading' => 'lazy',
					)
				);
				?>
			<?php else : ?>
				<div class="sc-article-card__placeholder">
					<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
						<path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
					</svg>
					<span class="sc-article-card__placeholder-text"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
				</div>
			<?php endif; ?>
		</a>

		<!-- Category & Featured Badges -->
		<div class="sc-article-card__badges">
			<?php if ( $primary_cat ) : ?>
				<a href="<?php echo esc_url( get_category_link( $primary_cat->term_id ) ); ?>" class="sc-badge sc-badge--category">
					<?php echo esc_html( $primary_cat->name ); ?>
				</a>
			<?php endif; ?>

			<?php if ( $is_featured ) : ?>
				<span class="sc-badge sc-badge--featured" title="<?php esc_attr_e( 'Featured Insight', 'spicecraft' ); ?>">
					<svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" stroke="none" aria-hidden="true">
						<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
					</svg>
					<?php esc_html_e( 'Featured', 'spicecraft' ); ?>
				</span>
			<?php endif; ?>
		</div>
	</div>

	<!-- Content Body -->
	<div class="sc-article-card__body">
		<!-- Metadata Row -->
		<div class="sc-article-card__meta">
			<?php if ( $show_date ) : ?>
				<time class="sc-article-card__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
					<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
						<line x1="16" y1="2" x2="16" y2="6"/>
						<line x1="8" y1="2" x2="8" y2="6"/>
						<line x1="3" y1="10" x2="21" y2="10"/>
					</svg>
					<?php echo esc_html( get_the_date( 'M j, Y' ) ); ?>
				</time>
			<?php endif; ?>

			<?php if ( $show_reading && $reading_time > 0 ) : ?>
				<span class="sc-article-card__reading-time" title="<?php esc_attr_e( 'Estimated reading duration', 'spicecraft' ); ?>">
					<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<circle cx="12" cy="12" r="10"/>
						<polyline points="12 6 12 12 16 14"/>
					</svg>
					<?php
					/* translators: %d: number of minutes */
					printf( esc_html__( '%d min read', 'spicecraft' ), absint( $reading_time ) );
					?>
				</span>
			<?php endif; ?>
		</div>

		<!-- Title -->
		<h3 class="sc-article-card__title">
			<a href="<?php the_permalink(); ?>">
				<?php the_title(); ?>
			</a>
		</h3>

		<!-- Excerpt -->
		<p class="sc-article-card__excerpt">
			<?php echo esc_html( wp_trim_words( get_the_excerpt(), 18, '...' ) ); ?>
		</p>

		<!-- Footer Row: Author & Link -->
		<div class="sc-article-card__footer">
			<?php if ( $show_author ) : ?>
				<div class="sc-article-card__author">
					<span class="sc-article-card__author-avatar" aria-hidden="true">
						<?php echo get_avatar( get_the_author_meta( 'ID' ), 24 ); ?>
					</span>
					<span class="sc-article-card__author-name">
						<?php echo esc_html( get_the_author() ); ?>
					</span>
				</div>
			<?php endif; ?>

			<a href="<?php the_permalink(); ?>" class="sc-article-card__link" aria-label="<?php echo esc_attr( sprintf( __( 'Read article: %s', 'spicecraft' ), get_the_title() ) ); ?>">
				<span><?php esc_html_e( 'Read Article', 'spicecraft' ); ?></span>
				<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<line x1="5" y1="12" x2="19" y2="12"></line>
					<polyline points="12 5 19 12 12 19"></polyline>
				</svg>
			</a>
		</div>
	</div>
</article>
