<?php
/**
 * The template for displaying archive pages
 *
 * @package SpiceCraft
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="sc-container">
	<header class="page-header" style="margin-bottom: var(--sc-space-8); text-align: center;">
		<?php
		the_archive_title( '<h1 class="page-title">', '</h1>' );
		the_archive_description( '<div class="archive-description" style="color: var(--sc-color-text-muted); max-width: 700px; margin: var(--sc-space-2) auto 0;">', '</div>' );
		?>
	</header>

	<div class="sc-content-area">
		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/content/content', get_post_format() );
			endwhile;

			the_posts_pagination(
				array(
					'prev_text' => '&larr; ' . esc_html__( 'Previous', 'spicecraft' ),
					'next_text' => esc_html__( 'Next', 'spicecraft' ) . ' &rarr;',
				)
			);
		else :
			get_template_part( 'template-parts/content/content', 'none' );
		endif;
		?>
	</div>
</div>

<?php
get_footer();
