<?php
/**
 * Template part for displaying standard posts
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'sc-card sc-post-card' ); ?> style="padding: var(--sc-space-6); margin-bottom: var(--sc-space-6);">
	<?php if ( has_post_thumbnail() ) : ?>
		<div class="sc-post-thumbnail" style="margin: calc(-1 * var(--sc-space-6)) calc(-1 * var(--sc-space-6)) var(--sc-space-4); overflow: hidden;">
			<a href="<?php the_permalink(); ?>">
				<?php the_post_thumbnail( 'spicecraft-blog-thumb', array( 'style' => 'width: 100%; height: auto;' ) ); ?>
			</a>
		</div>
	<?php endif; ?>

	<header class="entry-header">
		<?php
		if ( is_singular() ) :
			the_title( '<h1 class="entry-title">', '</h1>' );
		else :
			the_title( '<h2 class="entry-title" style="font-size: 1.75rem;"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
		endif;
		?>

		<div class="entry-meta" style="font-size: 0.85rem; color: var(--sc-color-text-muted); margin-bottom: var(--sc-space-4);">
			<?php
			spicecraft_posted_on();
			spicecraft_posted_by();
			?>
		</div>
	</header>

	<div class="entry-content">
		<?php
		if ( is_singular() ) :
			the_content(
				sprintf(
					wp_kses(
						/* translators: %s: Name of current post. Only visible to screen readers */
						__( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'spicecraft' ),
						array(
							'span' => array(
								'class' => array(),
							),
						)
					),
					wp_kses_post( get_the_title() )
				)
			);

			wp_link_pages(
				array(
					'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'spicecraft' ),
					'after'  => '</div>',
				)
			);
		else :
			the_excerpt();
			?>
			<a href="<?php the_permalink(); ?>" class="sc-btn sc-btn--outline" style="margin-top: var(--sc-space-2);">
				<?php esc_html_e( 'Read Article', 'spicecraft' ); ?> &rarr;
			</a>
			<?php
		endif;
		?>
	</div>

	<?php if ( is_singular() ) : ?>
		<footer class="entry-footer" style="margin-top: var(--sc-space-6); padding-top: var(--sc-space-4); border-top: 1px solid var(--sc-color-border-subtle); font-size: 0.85rem;">
			<?php spicecraft_entry_footer(); ?>
		</footer>
	<?php endif; ?>
</article>
