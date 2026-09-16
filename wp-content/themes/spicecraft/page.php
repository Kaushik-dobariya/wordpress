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

<div class="sc-container sc-container--narrow" style="padding-top: var(--sc-space-6);">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<?php if ( ! is_front_page() ) : ?>
			<nav class="sc-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'spicecraft' ); ?>" style="margin-bottom: var(--sc-space-4); font-size: 0.85rem; color: var(--sc-color-text-muted, #6b7280);">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" style="color: inherit; text-decoration: none;"><?php esc_html_e( 'Home', 'spicecraft' ); ?></a>
				<span class="sc-breadcrumb__separator" aria-hidden="true" style="margin: 0 var(--sc-space-2);">&rsaquo;</span>
				<span class="sc-breadcrumb__current" aria-current="page" style="color: var(--sc-color-text-heading, #111827); font-weight: 500;"><?php the_title(); ?></span>
			</nav>
		<?php endif; ?>

		<article id="post-<?php the_ID(); ?>" <?php post_class( 'sc-card' ); ?> style="padding: var(--sc-space-8); margin-bottom: var(--sc-space-8);">
			<header class="entry-header" style="margin-bottom: var(--sc-space-6);">
				<?php the_title( '<h1 class="entry-title" style="margin-top: 0; margin-bottom: var(--sc-space-2); font-size: clamp(1.75rem, 3vw, 2.25rem);">', '</h1>' ); ?>
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
