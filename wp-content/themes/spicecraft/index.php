<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
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
	<?php if ( is_home() && ! is_front_page() ) : ?>
		<header class="page-header" style="margin-bottom: var(--sc-space-8); text-align: center;">
			<h1 class="page-title"><?php single_post_title(); ?></h1>
			<p class="sc-lead" style="color: var(--sc-color-text-muted);"><?php esc_html_e( 'Latest updates, recipes, and spice processing insights.', 'spicecraft' ); ?></p>
		</header>
	<?php endif; ?>

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
