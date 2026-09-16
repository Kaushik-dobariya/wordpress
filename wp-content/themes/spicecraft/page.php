<?php
/**
 * The template for displaying all pages
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="sc-container sc-container--narrow">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'sc-card' ); ?> style="padding: var(--sc-space-8); margin-bottom: var(--sc-space-8);">
			<header class="entry-header" style="margin-bottom: var(--sc-space-6); text-align: center;">
				<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<div class="post-thumbnail" style="margin-bottom: var(--sc-space-6); border-radius: var(--sc-radius-md); overflow: hidden;">
					<?php the_post_thumbnail( 'full' ); ?>
				</div>
			<?php endif; ?>

			<div class="entry-content">
				<?php
				the_content();

				wp_link_pages(
					array(
						'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'spicecraft' ),
						'after'  => '</div>',
					)
				);
				?>
			</div>
		</article>

		<?php
		// If comments are open or we have at least one comment, load up the comment template.
		if ( comments_open() || get_comments_number() ) :
			comments_template();
		endif;

	endwhile;
	?>
</div>

<?php
get_footer();
